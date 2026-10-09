<?php
declare(strict_types=1);

namespace CB\Likes\Governance;

use CB\Likes\Settings;
use CoreBlueprint\Core\Governance\EventRegistry;
use CoreBlueprint\Core\Log\AuditLog;

defined( 'ABSPATH' ) || exit;

/**
 * Governance bridge to the Core Blueprint Audit Log.
 *
 * Likes owns the meaning of its settings change. Core Blueprint owns the
 * append-only audit store, actor metadata and presentation. Normal member
 * reactions are deliberately excluded from the governance log.
 */
final class Audit {
	private const EVENT_SETTINGS_CHANGED = 'likes.settings_changed';
	private const MAX_CHANGED_PATHS      = 100;

	public static function init(): void {
		add_action( 'updated_option', [ __CLASS__, 'option_updated' ], 10, 3 );
		add_action( 'added_option', [ __CLASS__, 'option_added' ], 10, 2 );
		add_action( 'init', [ __CLASS__, 'register_event' ], 10 );
	}

	public static function option_updated( string $option, mixed $old_value, mixed $value ): void {
		if ( Settings::OPTION !== $option ) {
			return;
		}

		self::log_settings_change( $old_value, $value );
	}

	public static function option_added( string $option, mixed $value ): void {
		if ( Settings::OPTION !== $option ) {
			return;
		}

		self::log_settings_change( [], $value );
	}

	/** Register the settings-change event using Base's public EventRegistry. */
	public static function register_event(): void {
		if ( ! class_exists( EventRegistry::class ) ) {
			return;
		}
		EventRegistry::register( [
			'id'    => self::EVENT_SETTINGS_CHANGED,
			'label' => __( 'Likes: settings changed', 'core-blueprint-likes' ),
		] );
	}

	private static function log_settings_change( mixed $old_value, mixed $new_value ): void {
		if ( ! function_exists( 'cb_likes_base_ready' ) || ! cb_likes_base_ready() || ! class_exists( AuditLog::class ) ) {
			return;
		}

		$old = self::snapshot( $old_value );
		$new = self::snapshot( $new_value );

		$changed = self::changed_paths( $old, $new );
		if ( [] === $changed ) {
			return;
		}

		$total   = count( $changed );
		$context = [
			'module'        => 'likes',
			'changed'       => array_slice( $changed, 0, self::MAX_CHANGED_PATHS ),
			'changed_count' => $total,
		];

		if ( $total > self::MAX_CHANGED_PATHS ) {
			$context['changed_truncated'] = true;
		}

		AuditLog::log( self::EVENT_SETTINGS_CHANGED, 'notice', $context );
	}

	/**
	 * Normalize settings before comparing them so storage-shape differences
	 * do not create false governance events. Values are used for comparison
	 * only and are never written to the audit context.
	 *
	 * @return array<string,mixed>
	 */
	private static function snapshot( mixed $value ): array {
		$normalized = Settings::sanitize( is_array( $value ) ? $value : [] );

		$post_types = isset( $normalized['post_types'] ) && is_array( $normalized['post_types'] )
			? array_values( array_map( 'strval', $normalized['post_types'] ) )
			: [];
		sort( $post_types, SORT_STRING );
		$normalized['post_types'] = $post_types;

		return $normalized;
	}

	/**
	 * Return setting paths only; never include old/new values in audit data.
	 *
	 * @param array<string,mixed> $old Previous normalized settings.
	 * @param array<string,mixed> $new New normalized settings.
	 * @return string[]
	 */
	private static function changed_paths( array $old, array $new ): array {
		$changed = [];

		if ( ( $old['post_types'] ?? [] ) !== ( $new['post_types'] ?? [] ) ) {
			$changed[] = 'post_types';
		}
		if ( (bool) ( $old['users_enabled'] ?? false ) !== (bool) ( $new['users_enabled'] ?? false ) ) {
			$changed[] = 'users_enabled';
		}

		self::compare_section(
			'global',
			is_array( $old['global'] ?? null ) ? $old['global'] : [],
			is_array( $new['global'] ?? null ) ? $new['global'] : [],
			$changed
		);

		$old_overrides = is_array( $old['post_type_overrides'] ?? null ) ? $old['post_type_overrides'] : [];
		$new_overrides = is_array( $new['post_type_overrides'] ?? null ) ? $new['post_type_overrides'] : [];
		$post_types     = array_values( array_unique( array_merge( array_keys( $old_overrides ), array_keys( $new_overrides ) ) ) );
		sort( $post_types, SORT_STRING );

		foreach ( $post_types as $post_type ) {
			self::compare_section(
			'post_type_overrides.' . sanitize_key( (string) $post_type ),
			is_array( $old_overrides[ $post_type ] ?? null ) ? $old_overrides[ $post_type ] : [],
			is_array( $new_overrides[ $post_type ] ?? null ) ? $new_overrides[ $post_type ] : [],
			$changed
			);
		}

		return array_values( array_unique( $changed ) );
	}

	/**
	 * @param array<string,mixed> $old
	 * @param array<string,mixed> $new
	 * @param string[]            $changed
	 */
	private static function compare_section( string $prefix, array $old, array $new, array &$changed ): void {
		$keys = array_values( array_unique( array_merge( array_keys( $old ), array_keys( $new ) ) ) );
		sort( $keys, SORT_STRING );

		foreach ( $keys as $key ) {
			$old_value = $old[ $key ] ?? null;
			$new_value = $new[ $key ] ?? null;
			if ( $old_value !== $new_value ) {
				$changed[] = $prefix . '.' . sanitize_key( (string) $key );
			}
		}
	}
}

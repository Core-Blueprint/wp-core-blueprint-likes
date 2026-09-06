<?php
declare(strict_types=1);

namespace CB\Likes\Builder;

use CB\Likes\Settings;
use CB\Likes\Targets as DomainTargets;

defined( 'ABSPATH' ) || exit;

/** Human-readable target options for optional builder adapters. */
final class Targets {
	/** @var array<string,string>|null */
	private static ?array $post_options = null;
	/** @var array<string,string>|null */
	private static ?array $user_options = null;

	/** @return array<string,string> */
	public static function options( bool $include_users = true ): array {
		$options = self::post_options();
		if ( $include_users && Settings::users_enabled() ) {
			$options += self::user_options();
		}
		return $options;
	}

	/** @return array{type:string,id:int}|null */
	public static function from_option( string $value, bool $include_users = true ): ?array {
		if ( ! preg_match( '/^(post|user):(\d+)$/', $value, $matches ) ) {
			return null;
		}
		$type = DomainTargets::normalize_type( $matches[1] );
		$id   = absint( $matches[2] );
		if ( $id <= 0 || ( DomainTargets::USER === $type && ! $include_users ) ) {
			return null;
		}
		if ( ! DomainTargets::is_enabled( $type, $id ) ) {
			return null;
		}
		return [ 'type' => $type, 'id' => $id ];
	}

	public static function current_label(): string {
		return __( 'Current target', 'core-blueprint-likes' );
	}

	public static function specific_label(): string {
		return __( 'Specific target', 'core-blueprint-likes' );
	}

	public static function select_placeholder(): string {
		return __( 'Select content or user', 'core-blueprint-likes' );
	}

	/** @return array<string,string> */
	private static function post_options(): array {
		if ( null !== self::$post_options ) {
			return self::$post_options;
		}

		$post_types = Settings::enabled_post_types();
		if ( [] === $post_types ) {
			self::$post_options = [];
			return self::$post_options;
		}

		$limit = (int) apply_filters( 'cb_likes_builder_target_limit', 200, DomainTargets::POST );
		$limit = max( 20, min( 500, $limit ) );
		$posts = get_posts( [
			'post_type'        => $post_types,
			'post_status'      => 'publish',
			'posts_per_page'   => $limit,
			'orderby'          => [ 'modified' => 'DESC', 'ID' => 'DESC' ],
			'suppress_filters' => true,
		] );

		$options = [];
		foreach ( $posts as $post ) {
			if ( ! $post instanceof \WP_Post ) {
				continue;
			}
			$type = get_post_type_object( $post->post_type );
			$type_label = $type && isset( $type->labels->singular_name )
				? (string) $type->labels->singular_name
				: $post->post_type;
			$title = trim( get_the_title( $post ) );
			$options['post:' . (int) $post->ID] = sprintf(
				/* translators: 1: content type label, 2: content title. */
				__( '%1$s — %2$s', 'core-blueprint-likes' ),
				$type_label,
				'' !== $title ? $title : __( '(Untitled)', 'core-blueprint-likes' )
			);
		}
		self::$post_options = $options;
		return $options;
	}

	/** @return array<string,string> */
	private static function user_options(): array {
		if ( null !== self::$user_options ) {
			return self::$user_options;
		}

		$limit = (int) apply_filters( 'cb_likes_builder_target_limit', 200, DomainTargets::USER );
		$limit = max( 20, min( 500, $limit ) );
		$users = get_users( [
			'number'  => $limit,
			'orderby' => 'display_name',
			'order'   => 'ASC',
			'fields'  => 'all',
		] );

		$options = [];
		foreach ( $users as $user ) {
			if ( ! $user instanceof \WP_User ) {
				continue;
			}
			$options['user:' . (int) $user->ID] = sprintf(
				/* translators: %s: user display name. */
				__( 'User — %s', 'core-blueprint-likes' ),
				(string) $user->display_name
			);
		}
		self::$user_options = $options;
		return $options;
	}
}

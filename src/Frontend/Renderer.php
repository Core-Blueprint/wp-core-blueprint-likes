<?php
declare(strict_types=1);

namespace CB\Likes\Frontend;

use CB\Likes\Repository;
use CB\Likes\Settings;
use CB\Likes\Targets;

defined( 'ABSPATH' ) || exit;

final class Renderer {
	private static bool $localized = false;

	public static function init(): void {
		add_shortcode( 'cb_like_button', [ __CLASS__, 'button_shortcode' ] );
		add_shortcode( 'cb_like_count', [ __CLASS__, 'count_shortcode' ] );
		add_shortcode( 'cb_dislike_button', [ __CLASS__, 'dislike_button_shortcode' ] );
		add_shortcode( 'cb_dislike_count', [ __CLASS__, 'dislike_count_shortcode' ] );
	}

	/** @param array<string,mixed>|string $atts */
	public static function button_shortcode( array|string $atts = [] ): string {
		return self::like_button( is_array( $atts ) ? $atts : [] );
	}

	/** @param array<string,mixed>|string $atts */
	public static function dislike_button_shortcode( array|string $atts = [] ): string {
		return self::dislike_button( is_array( $atts ) ? $atts : [] );
	}

	/** @param array<string,mixed> $atts */
	public static function like_button( array $atts = [] ): string {
		return self::reaction_button(
			Repository::LIKE,
			self::button_atts( $atts, 'cb_like_button' )
		);
	}

	/** @param array<string,mixed> $atts */
	public static function dislike_button( array $atts = [] ): string {
		return self::reaction_button(
			Repository::DISLIKE,
			self::button_atts( $atts, 'cb_dislike_button' )
		);
	}

	/** @param array<string,mixed>|string $atts */
	public static function count_shortcode( array|string $atts = [] ): string {
		return self::reaction_count_shortcode( Repository::LIKE, $atts );
	}

	/** @param array<string,mixed>|string $atts */
	public static function dislike_count_shortcode( array|string $atts = [] ): string {
		return self::reaction_count_shortcode( Repository::DISLIKE, $atts );
	}

	/** @param array<string,mixed> $atts */
	private static function reaction_button( string $reaction, array $atts ): string {
		$target = self::target_from_atts( $atts );
		if ( ! $target || ! Targets::is_enabled( $target['type'], $target['id'] ) ) {
			return '';
		}
		$config = Settings::presentation_for_target( $target['type'], $target['id'] );
		if ( Repository::DISLIKE === $reaction && ! $config['dislike_enabled'] ) {
			return '';
		}

		$user_id = get_current_user_id();
		if ( $user_id <= 0 && $config['logged_in_only'] ) {
			return '';
		}
		$current_reaction = $user_id > 0 ? Repository::reaction_for_user( $user_id, $target['type'], $target['id'] ) : null;
		$active = $reaction === $current_reaction;
		$count = Repository::DISLIKE === $reaction
			? Repository::dislike_count( $target['type'], $target['id'] )
			: Repository::count( $target['type'], $target['id'] );
		self::enqueue_assets();

		$is_dislike = Repository::DISLIKE === $reaction;
		$classes = $is_dislike
			? 'cb-reaction-button cb-dislike-button cb-reaction-button--dislike'
			: 'cb-reaction-button cb-like-button cb-reaction-button--like';
		if ( $active ) {
			$classes .= $is_dislike ? ' is-disliked is-active' : ' is-liked is-active';
		}
		if ( '' !== trim( (string) $atts['class'] ) ) {
			$classes .= ' ' . implode( ' ', array_map( 'sanitize_html_class', preg_split( '/\s+/', trim( (string) $atts['class'] ) ) ?: [] ) );
		}

		$setting_label = $is_dislike ? $config['dislike_label'] : $config['like_label'];
		$setting_active_label = $is_dislike ? $config['disliked_label'] : $config['liked_label'];
		$label = '' !== trim( (string) $atts['label'] ) ? (string) $atts['label'] : $setting_label;
		$legacy_active = ! $is_dislike ? trim( (string) $atts['liked_label'] ) : '';
		$active_label = '' !== trim( (string) $atts['active_label'] )
			? (string) $atts['active_label']
			: ( '' !== $legacy_active ? (string) $atts['liked_label'] : $setting_active_label );
		$visible_label = $active ? $active_label : $label;
		$icon = $is_dislike ? $config['dislike_icon'] : $config['like_icon'];
		$show_count = filter_var( $atts['show_count'], FILTER_VALIDATE_BOOLEAN );
		$disabled = $user_id > 0 && ! Targets::user_can_like( $user_id, $target['type'], $target['id'] );
		$aria_inactive = $is_dislike ? __( 'Dislike this item', 'core-blueprint-likes' ) : __( 'Like this item', 'core-blueprint-likes' );
		$aria_active = $is_dislike ? __( 'Remove dislike from this item', 'core-blueprint-likes' ) : __( 'Remove like from this item', 'core-blueprint-likes' );

		$icon_class = $is_dislike ? 'cb-reaction-button__icon cb-dislike-button__icon' : 'cb-reaction-button__icon cb-like-button__icon';
		$label_class = $is_dislike ? 'cb-reaction-button__label cb-dislike-button__label' : 'cb-reaction-button__label cb-like-button__label';
		$count_class = $is_dislike ? 'cb-reaction-button__count cb-dislike-button__count' : 'cb-reaction-button__count cb-like-button__count';

		return sprintf(
			'<button type="button" class="%1$s" data-cb-reaction-button="%2$s" data-target-type="%3$s" data-target-id="%4$d" data-active="%5$s" data-label="%6$s" data-active-label="%7$s" data-aria-inactive="%8$s" data-aria-active="%9$s" data-logged-out-message="%10$s" aria-pressed="%11$s" aria-label="%12$s"%13$s><span class="%14$s" aria-hidden="true">%15$s</span><span class="%16$s">%17$s</span>%18$s</button>',
			esc_attr( trim( $classes ) ),
			esc_attr( $reaction ),
			esc_attr( $target['type'] ),
			$target['id'],
			$active ? '1' : '0',
			esc_attr( $label ),
			esc_attr( $active_label ),
			esc_attr( $aria_inactive ),
			esc_attr( $aria_active ),
			esc_attr( $config['logged_out_message'] ),
			$active ? 'true' : 'false',
			esc_attr( $active ? $aria_active : $aria_inactive ),
			$disabled ? ' disabled' : '',
			esc_attr( $icon_class ),
			$icon, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- sanitized by Settings::sanitize_svg or a trusted built-in SVG.
			esc_attr( $label_class ),
			esc_html( $visible_label ),
			$show_count ? '<span class="' . esc_attr( $count_class ) . '" data-cb-reaction-count="' . esc_attr( $reaction ) . '">' . esc_html( (string) $count ) . '</span>' : ''
		);
	}

	/** @param array<string,mixed>|string $atts */
	private static function reaction_count_shortcode( string $reaction, array|string $atts ): string {
		$shortcode = Repository::DISLIKE === $reaction ? 'cb_dislike_count' : 'cb_like_count';
		$atts = shortcode_atts( [
			'target_type' => '',
			'target_id'   => 0,
			'class'       => '',
		], is_array( $atts ) ? $atts : [], $shortcode );

		$target = self::target_from_atts( $atts );
		if ( ! $target || ! Targets::is_enabled( $target['type'], $target['id'] ) ) {
			return '';
		}
		$config = Settings::presentation_for_target( $target['type'], $target['id'] );
		if ( Repository::DISLIKE === $reaction && ! $config['dislike_enabled'] ) {
			return '';
		}
		if ( ! is_user_logged_in() && $config['logged_in_only'] ) {
			return '';
		}

		self::enqueue_assets();
		$is_dislike = Repository::DISLIKE === $reaction;
		$classes = $is_dislike ? 'cb-reaction-count cb-dislike-count' : 'cb-reaction-count cb-like-count';
		if ( '' !== trim( (string) $atts['class'] ) ) {
			$classes .= ' ' . implode( ' ', array_map( 'sanitize_html_class', preg_split( '/\s+/', trim( (string) $atts['class'] ) ) ?: [] ) );
		}
		$count = $is_dislike
			? Repository::dislike_count( $target['type'], $target['id'] )
			: Repository::count( $target['type'], $target['id'] );

		return sprintf(
			'<span class="%1$s" data-cb-reaction-count="%2$s" data-target-type="%3$s" data-target-id="%4$d">%5$s</span>',
			esc_attr( trim( $classes ) ),
			esc_attr( $reaction ),
			esc_attr( $target['type'] ),
			$target['id'],
			esc_html( (string) $count )
		);
	}

	/**
	 * @param array<string,mixed> $atts
	 * @return array<string,mixed>
	 */
	private static function button_atts( array $atts, string $shortcode ): array {
		return shortcode_atts( [
			'target_type'  => '',
			'target_id'    => 0,
			'show_count'   => 'true',
			'label'        => '',
			'active_label' => '',
			'liked_label'  => '', // Retained public shortcode compatibility.
			'class'        => '',
		], $atts, $shortcode );
	}

	/**
	 * @param array<string,mixed> $atts
	 * @return array{type:string,id:int}|null
	 */
	private static function target_from_atts( array $atts ): ?array {
		$id = absint( $atts['target_id'] ?? 0 );
		$type = sanitize_key( (string) ( $atts['target_type'] ?? '' ) );
		if ( $id > 0 ) {
			return [ 'type' => Targets::normalize_type( $type ?: Targets::POST ), 'id' => $id ];
		}
		return Targets::current();
	}

	private static function enqueue_assets(): void {
		wp_enqueue_style( 'cb-likes', CB_LIKES_URL . 'assets/css/frontend.css', [], CB_LIKES_VERSION );
		wp_enqueue_script( 'cb-likes', CB_LIKES_URL . 'assets/js/frontend.js', [], CB_LIKES_VERSION, true );
		if ( self::$localized ) {
			return;
		}
		self::$localized = true;
		wp_localize_script( 'cb-likes', 'CBLikes', [
			'endpoint' => rest_url( 'core-blueprint-likes/v1/state' ),
			'nonce'     => is_user_logged_in() ? wp_create_nonce( 'wp_rest' ) : '',
			'loggedIn'  => is_user_logged_in(),
			'error'     => __( 'The reaction could not be saved.', 'core-blueprint-likes' ),
		] );
	}
}

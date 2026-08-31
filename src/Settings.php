<?php
declare(strict_types=1);

namespace CB\Likes;

defined( 'ABSPATH' ) || exit;

final class Settings {
	public const OPTION = 'cb_likes_settings';

	/**
	 * @return array{
	 *   post_types:string[],
	 *   users_enabled:bool,
	 *   global:array{like_label:string,liked_label:string,like_icon_source:string,like_icon_builtin:string,like_icon_media_id:int,like_icon:string,dislike_enabled:bool,dislike_label:string,disliked_label:string,dislike_icon_source:string,dislike_icon_builtin:string,dislike_icon_media_id:int,dislike_icon:string,logged_in_only:bool,logged_out_message:string},
	 *   post_type_overrides:array<string,array{like_label:string,liked_label:string,like_icon_source:string,like_icon_builtin:string,like_icon_media_id:int,like_icon:string,dislike_mode:string,dislike_label:string,disliked_label:string,dislike_icon_source:string,dislike_icon_builtin:string,dislike_icon_media_id:int,dislike_icon:string,visibility_mode:string,logged_out_message:string}>
	 * }
	 */
	public static function all(): array {
		$value = get_option( self::OPTION, [] );
		$value = is_array( $value ) ? $value : [];

		$post_types = isset( $value['post_types'] ) && is_array( $value['post_types'] )
			? array_values( array_unique( array_filter( array_map( 'sanitize_key', $value['post_types'] ) ) ) )
			: [ 'post' ];

		$global_raw    = isset( $value['global'] ) && is_array( $value['global'] ) ? $value['global'] : [];
		$overrides_raw = isset( $value['post_type_overrides'] ) && is_array( $value['post_type_overrides'] ) ? $value['post_type_overrides'] : [];
		$overrides     = [];
		foreach ( $overrides_raw as $post_type => $override ) {
			if ( ! is_array( $override ) ) {
				continue;
			}
			$slug = sanitize_key( (string) $post_type );
			if ( '' === $slug ) {
				continue;
			}
			$overrides[ $slug ] = self::normalize_override( $override );
		}

		return [
			'post_types'          => $post_types,
			'users_enabled'       => ! empty( $value['users_enabled'] ),
			'global'              => self::normalize_global( $global_raw ),
			'post_type_overrides' => $overrides,
		];
	}

	/** @return string[] */
	public static function enabled_post_types(): array {
		return self::all()['post_types'];
	}

	public static function users_enabled(): bool {
		return self::all()['users_enabled'];
	}

	/**
	 * Resolve labels, icons, dislike availability and logged-out visibility for a concrete target.
	 * Empty stored labels/icons/messages intentionally fall back to translated plugin defaults.
	 *
	 * @return array{like_label:string,liked_label:string,like_icon:string,dislike_enabled:bool,dislike_label:string,disliked_label:string,dislike_icon:string,logged_in_only:bool,logged_out_message:string}
	 */
	public static function presentation_for_target( string $target_type, int $target_id ): array {
		$settings = self::all();
		$global   = $settings['global'];
		$config   = [
			'like_label'         => '' !== $global['like_label'] ? $global['like_label'] : __( 'Like', 'core-blueprint-likes' ),
			'liked_label'        => '' !== $global['liked_label'] ? $global['liked_label'] : __( 'Liked', 'core-blueprint-likes' ),
			'like_icon'          => self::resolve_icon( $global['like_icon_source'], $global['like_icon_builtin'], $global['like_icon_media_id'], $global['like_icon'], self::default_like_icon() ),
			'dislike_enabled'    => false,
			'dislike_label'      => '' !== $global['dislike_label'] ? $global['dislike_label'] : __( 'Dislike', 'core-blueprint-likes' ),
			'disliked_label'     => '' !== $global['disliked_label'] ? $global['disliked_label'] : __( 'Disliked', 'core-blueprint-likes' ),
			'dislike_icon'       => self::resolve_icon( $global['dislike_icon_source'], $global['dislike_icon_builtin'], $global['dislike_icon_media_id'], $global['dislike_icon'], self::default_dislike_icon() ),
			'logged_in_only'     => $global['logged_in_only'],
			'logged_out_message' => '' !== $global['logged_out_message'] ? $global['logged_out_message'] : __( 'Please log in to react to this content.', 'core-blueprint-likes' ),
		];

		if ( Targets::POST !== Targets::normalize_type( $target_type ) ) {
			return $config;
		}

		$post = get_post( $target_id );
		if ( ! $post instanceof \WP_Post ) {
			return $config;
		}

		$override = $settings['post_type_overrides'][ $post->post_type ] ?? self::normalize_override( [] );
		foreach ( [ 'like_label', 'liked_label', 'dislike_label', 'disliked_label' ] as $key ) {
			if ( '' !== $override[ $key ] ) {
				$config[ $key ] = $override[ $key ];
			}
		}

		if ( 'inherit' !== $override['like_icon_source'] ) {
			$config['like_icon'] = self::resolve_icon( $override['like_icon_source'], $override['like_icon_builtin'], $override['like_icon_media_id'], $override['like_icon'], self::default_like_icon() );
		}
		if ( 'inherit' !== $override['dislike_icon_source'] ) {
			$config['dislike_icon'] = self::resolve_icon( $override['dislike_icon_source'], $override['dislike_icon_builtin'], $override['dislike_icon_media_id'], $override['dislike_icon'], self::default_dislike_icon() );
		}

		$config['dislike_enabled'] = match ( $override['dislike_mode'] ) {
			'enabled'  => true,
			'disabled' => false,
			default    => $global['dislike_enabled'],
		};

		$config['logged_in_only'] = match ( $override['visibility_mode'] ) {
			'logged_in_only' => true,
			'show'           => false,
			default          => $global['logged_in_only'],
		};

		if ( '' !== $override['logged_out_message'] ) {
			$config['logged_out_message'] = $override['logged_out_message'];
		}

		return $config;
	}

	public static function dislike_enabled_for_target( string $target_type, int $target_id ): bool {
		return self::presentation_for_target( $target_type, $target_id )['dislike_enabled'];
	}

	public static function logged_in_only_for_target( string $target_type, int $target_id ): bool {
		return self::presentation_for_target( $target_type, $target_id )['logged_in_only'];
	}

	public static function logged_out_message_for_target( string $target_type, int $target_id ): string {
		return self::presentation_for_target( $target_type, $target_id )['logged_out_message'];
	}

	/**
	 * @param mixed $value
	 * @return array{
	 *   post_types:string[],
	 *   users_enabled:bool,
	 *   global:array{like_label:string,liked_label:string,like_icon_source:string,like_icon_builtin:string,like_icon_media_id:int,like_icon:string,dislike_enabled:bool,dislike_label:string,disliked_label:string,dislike_icon_source:string,dislike_icon_builtin:string,dislike_icon_media_id:int,dislike_icon:string,logged_in_only:bool,logged_out_message:string},
	 *   post_type_overrides:array<string,array{like_label:string,liked_label:string,like_icon_source:string,like_icon_builtin:string,like_icon_media_id:int,like_icon:string,dislike_mode:string,dislike_label:string,disliked_label:string,dislike_icon_source:string,dislike_icon_builtin:string,dislike_icon_media_id:int,dislike_icon:string,visibility_mode:string,logged_out_message:string}>
	 * }
	 */
	public static function sanitize( mixed $value ): array {
		$value     = is_array( $value ) ? $value : [];
		$available = array_keys( self::available_post_types() );
		$selected  = isset( $value['post_types'] ) && is_array( $value['post_types'] ) ? array_map( 'sanitize_key', $value['post_types'] ) : [];
		$selected  = array_values( array_intersect( array_unique( $selected ), $available ) );

		$global        = isset( $value['global'] ) && is_array( $value['global'] ) ? $value['global'] : [];
		// Checkbox fields are omitted by browsers when unchecked; preserve that as an explicit false during saves.
		$global['logged_in_only'] = ! empty( $global['logged_in_only'] );
		$overrides_raw = isset( $value['post_type_overrides'] ) && is_array( $value['post_type_overrides'] ) ? $value['post_type_overrides'] : [];
		$overrides     = [];
		foreach ( $available as $post_type ) {
			$raw                     = isset( $overrides_raw[ $post_type ] ) && is_array( $overrides_raw[ $post_type ] ) ? $overrides_raw[ $post_type ] : [];
			$overrides[ $post_type ] = self::normalize_override( $raw, true );
		}

		return [
			'post_types'          => $selected,
			'users_enabled'       => ! empty( $value['users_enabled'] ),
			'global'              => self::normalize_global( $global, true ),
			'post_type_overrides' => $overrides,
		];
	}

	/** @return array<string,\WP_Post_Type> */
	public static function available_post_types(): array {
		$objects = get_post_types( [ 'public' => true ], 'objects' );
		unset( $objects['attachment'] );
		/** @var array<string,\WP_Post_Type> $objects */
		return $objects;
	}

	public static function default_like_icon(): string {
		return Icons::svg( 'heart' );
	}

	public static function default_dislike_icon(): string {
		return Icons::svg( 'thumbs-down' );
	}

	private static function resolve_icon( string $source, string $built_in, int $media_id, string $svg, string $fallback ): string {
		if ( 'built_in' === $source ) {
			return Icons::svg( $built_in, 'heart' );
		}
		if ( 'svg' === $source && '' !== $svg ) {
			return $svg;
		}

		if ( 'media' === $source && $media_id > 0 ) {
			$mime = (string) get_post_mime_type( $media_id );
			$url  = wp_get_attachment_url( $media_id );
			if ( str_starts_with( $mime, 'image/' ) && is_string( $url ) && '' !== $url ) {
				return sprintf( '<img src="%s" alt="" aria-hidden="true" loading="lazy" decoding="async">', esc_url( $url ) );
			}
		}

		return $fallback;
	}

	public static function sanitize_svg( string $svg ): string {
		$svg = trim( $svg );
		if ( '' === $svg ) {
			return '';
		}
		// Privacy-first: reject SVG paint servers or other CSS URL references.
		if ( preg_match( '/url\s*\(/i', $svg ) ) {
			return '';
		}

		$allowed = [
			'svg' => [
				'xmlns' => true, 'viewbox' => true, 'viewBox' => true, 'width' => true, 'height' => true,
				'fill' => true, 'stroke' => true, 'stroke-width' => true, 'stroke-linecap' => true, 'stroke-linejoin' => true,
				'aria-hidden' => true, 'focusable' => true, 'role' => true,
			],
			'g' => [ 'fill' => true, 'stroke' => true, 'stroke-width' => true, 'stroke-linecap' => true, 'stroke-linejoin' => true, 'transform' => true, 'opacity' => true ],
			'path' => [ 'd' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true, 'stroke-linecap' => true, 'stroke-linejoin' => true, 'transform' => true, 'opacity' => true ],
			'circle' => [ 'cx' => true, 'cy' => true, 'r' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true, 'opacity' => true ],
			'ellipse' => [ 'cx' => true, 'cy' => true, 'rx' => true, 'ry' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true, 'opacity' => true ],
			'rect' => [ 'x' => true, 'y' => true, 'width' => true, 'height' => true, 'rx' => true, 'ry' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true, 'opacity' => true ],
			'line' => [ 'x1' => true, 'x2' => true, 'y1' => true, 'y2' => true, 'stroke' => true, 'stroke-width' => true, 'stroke-linecap' => true, 'opacity' => true ],
			'polyline' => [ 'points' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true, 'stroke-linecap' => true, 'stroke-linejoin' => true, 'opacity' => true ],
			'polygon' => [ 'points' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true, 'stroke-linecap' => true, 'stroke-linejoin' => true, 'opacity' => true ],
		];

		$sanitized = trim( (string) wp_kses( $svg, $allowed ) );
		return str_contains( strtolower( $sanitized ), '<svg' ) ? $sanitized : '';
	}

	private static function normalize_builtin_icon( string $name, string $fallback ): string {
		$name = sanitize_key( $name );
		return Icons::exists( $name ) ? $name : $fallback;
	}

	/** @param array<string,mixed> $value
	 *  @return array{like_label:string,liked_label:string,like_icon:string,dislike_enabled:bool,dislike_label:string,disliked_label:string,dislike_icon:string,logged_in_only:bool,logged_out_message:string}
	 */
	private static function normalize_global( array $value, bool $sanitize = false ): array {
		$text = static function ( mixed $item ) use ( $sanitize ): string {
			$item = is_scalar( $item ) ? (string) $item : '';
			return $sanitize ? sanitize_text_field( $item ) : $item;
		};
		$icon = static function ( mixed $item ) use ( $sanitize ): string {
			$item = is_scalar( $item ) ? (string) $item : '';
			return $sanitize ? self::sanitize_svg( $item ) : $item;
		};
		$icon_source = sanitize_key( (string) ( $value['like_icon_source'] ?? ( ! empty( $value['like_icon'] ) ? 'svg' : 'built_in' ) ) );
		if ( ! in_array( $icon_source, [ 'built_in', 'media', 'svg' ], true ) ) {
			$icon_source = 'built_in';
		}
		$dislike_icon_source = sanitize_key( (string) ( $value['dislike_icon_source'] ?? ( ! empty( $value['dislike_icon'] ) ? 'svg' : 'built_in' ) ) );
		if ( ! in_array( $dislike_icon_source, [ 'built_in', 'media', 'svg' ], true ) ) {
			$dislike_icon_source = 'built_in';
		}
		return [
			'like_label'         => $text( $value['like_label'] ?? '' ),
			'liked_label'        => $text( $value['liked_label'] ?? '' ),
			'like_icon_source'   => $icon_source,
			'like_icon_builtin'  => self::normalize_builtin_icon( (string) ( $value['like_icon_builtin'] ?? 'heart' ), 'heart' ),
			'like_icon_media_id' => absint( $value['like_icon_media_id'] ?? 0 ),
			'like_icon'          => $icon( $value['like_icon'] ?? '' ),
			'dislike_enabled'    => ! empty( $value['dislike_enabled'] ),
			'dislike_label'      => $text( $value['dislike_label'] ?? '' ),
			'disliked_label'     => $text( $value['disliked_label'] ?? '' ),
			'dislike_icon_source'   => $dislike_icon_source,
			'dislike_icon_builtin'  => self::normalize_builtin_icon( (string) ( $value['dislike_icon_builtin'] ?? 'thumbs-down' ), 'thumbs-down' ),
			'dislike_icon_media_id' => absint( $value['dislike_icon_media_id'] ?? 0 ),
			'dislike_icon'       => $icon( $value['dislike_icon'] ?? '' ),
			'logged_in_only'     => array_key_exists( 'logged_in_only', $value ) ? ! empty( $value['logged_in_only'] ) : true,
			'logged_out_message' => $text( $value['logged_out_message'] ?? '' ),
		];
	}

	/** @param array<string,mixed> $value
	 *  @return array{like_label:string,liked_label:string,like_icon:string,dislike_mode:string,dislike_label:string,disliked_label:string,dislike_icon:string,visibility_mode:string,logged_out_message:string}
	 */
	private static function normalize_override( array $value, bool $sanitize = false ): array {
		$text = static function ( mixed $item ) use ( $sanitize ): string {
			$item = is_scalar( $item ) ? (string) $item : '';
			return $sanitize ? sanitize_text_field( $item ) : $item;
		};
		$icon = static function ( mixed $item ) use ( $sanitize ): string {
			$item = is_scalar( $item ) ? (string) $item : '';
			return $sanitize ? self::sanitize_svg( $item ) : $item;
		};
		$dislike_mode = sanitize_key( (string) ( $value['dislike_mode'] ?? 'inherit' ) );
		if ( ! in_array( $dislike_mode, [ 'inherit', 'enabled', 'disabled' ], true ) ) {
			$dislike_mode = 'inherit';
		}
		$visibility_mode = sanitize_key( (string) ( $value['visibility_mode'] ?? 'inherit' ) );
		if ( ! in_array( $visibility_mode, [ 'inherit', 'logged_in_only', 'show' ], true ) ) {
			$visibility_mode = 'inherit';
		}
		$like_icon_source = sanitize_key( (string) ( $value['like_icon_source'] ?? ( ! empty( $value['like_icon'] ) ? 'svg' : 'inherit' ) ) );
		if ( ! in_array( $like_icon_source, [ 'inherit', 'built_in', 'media', 'svg' ], true ) ) {
			$like_icon_source = 'inherit';
		}
		$dislike_icon_source = sanitize_key( (string) ( $value['dislike_icon_source'] ?? ( ! empty( $value['dislike_icon'] ) ? 'svg' : 'inherit' ) ) );
		if ( ! in_array( $dislike_icon_source, [ 'inherit', 'built_in', 'media', 'svg' ], true ) ) {
			$dislike_icon_source = 'inherit';
		}
		return [
			'like_label'         => $text( $value['like_label'] ?? '' ),
			'liked_label'        => $text( $value['liked_label'] ?? '' ),
			'like_icon_source'   => $like_icon_source,
			'like_icon_builtin'  => self::normalize_builtin_icon( (string) ( $value['like_icon_builtin'] ?? 'heart' ), 'heart' ),
			'like_icon_media_id' => absint( $value['like_icon_media_id'] ?? 0 ),
			'like_icon'          => $icon( $value['like_icon'] ?? '' ),
			'dislike_mode'       => $dislike_mode,
			'dislike_label'      => $text( $value['dislike_label'] ?? '' ),
			'disliked_label'     => $text( $value['disliked_label'] ?? '' ),
			'dislike_icon_source'   => $dislike_icon_source,
			'dislike_icon_builtin'  => self::normalize_builtin_icon( (string) ( $value['dislike_icon_builtin'] ?? 'thumbs-down' ), 'thumbs-down' ),
			'dislike_icon_media_id' => absint( $value['dislike_icon_media_id'] ?? 0 ),
			'dislike_icon'       => $icon( $value['dislike_icon'] ?? '' ),
			'visibility_mode'    => $visibility_mode,
			'logged_out_message' => $text( $value['logged_out_message'] ?? '' ),
		];
	}
}

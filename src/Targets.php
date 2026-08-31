<?php
declare(strict_types=1);

namespace CB\Likes;

defined( 'ABSPATH' ) || exit;

final class Targets {
	public const POST = 'post';
	public const USER = 'user';

	public static function normalize_type( string $target_type ): string {
		return self::USER === sanitize_key( $target_type ) ? self::USER : self::POST;
	}

	public static function is_enabled( string $target_type, int $target_id ): bool {
		$type = self::normalize_type( $target_type );
		if ( $target_id <= 0 ) {
			return false;
		}
		if ( self::USER === $type ) {
			return Settings::users_enabled() && (bool) get_userdata( $target_id );
		}
		$post = get_post( $target_id );
		return $post instanceof \WP_Post && in_array( $post->post_type, Settings::enabled_post_types(), true );
	}

	public static function user_can_view( int $user_id, string $target_type, int $target_id ): bool {
		$type = self::normalize_type( $target_type );
		$allowed = self::is_enabled( $type, $target_id );
		if ( $allowed && self::POST === $type ) {
			$post = get_post( $target_id );
			$allowed = $post instanceof \WP_Post && ( 'publish' === $post->post_status || ( $user_id > 0 && user_can( $user_id, 'read_post', $target_id ) ) );
		}

		/**
		 * Lets sibling plugins enforce target visibility without becoming a
		 * dependency of Core Blueprint Likes. Restricted content providers should
		 * deny targets the user can no longer view.
		 */
		return (bool) apply_filters( 'cb_likes_user_can_view_target', $allowed, $user_id, $type, $target_id );
	}

	public static function user_can_like( int $user_id, string $target_type, int $target_id ): bool {
		$type = self::normalize_type( $target_type );
		$allowed = $user_id > 0 && self::user_can_view( $user_id, $type, $target_id );
		if ( $allowed && self::USER === $type && $user_id === $target_id ) {
			$allowed = false;
		}

		/**
		 * Allows sibling plugins to add stricter participation rules after target
		 * visibility has already been confirmed.
		 */
		return (bool) apply_filters( 'cb_likes_user_can_like_target', $allowed, $user_id, $type, $target_id );
	}

	/** @param mixed $context
	 *  @return array{type:string,id:int}|null
	 */
	public static function current( mixed $context = null ): ?array {
		$id = 0;
		if ( $context instanceof \WP_Post ) {
			$id = (int) $context->ID;
		} elseif ( is_numeric( $context ) ) {
			$id = absint( $context );
		} else {
			$id = (int) get_the_ID();
		}
		$target = $id > 0 ? [ 'type' => self::POST, 'id' => $id ] : null;
		/**
		 * Filters the current like target. Profiles can later expose a user target
		 * without Core Blueprint Likes depending on Profiles.
		 *
		 * @param array{type:string,id:int}|null $target
		 * @param mixed $context
		 */
		$target = apply_filters( 'cb_likes_current_target', $target, $context );
		if ( ! is_array( $target ) || empty( $target['id'] ) || empty( $target['type'] ) ) {
			return null;
		}
		return [ 'type' => self::normalize_type( (string) $target['type'] ), 'id' => absint( $target['id'] ) ];
	}
}

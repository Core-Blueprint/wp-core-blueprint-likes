<?php
declare(strict_types=1);

namespace CB\Likes;

defined( 'ABSPATH' ) || exit;

final class Service {
	/** @return array{reaction:?string,liked:bool,disliked:bool,count:int,like_count:int,dislike_count:int}|\WP_Error */
	public static function set_reaction( int $user_id, string $target_type, int $target_id, ?string $reaction ): array|\WP_Error {
		$type = Targets::normalize_type( $target_type );
		$reaction = null === $reaction || '' === $reaction ? null : sanitize_key( $reaction );
		if ( null !== $reaction && ! in_array( $reaction, [ Repository::LIKE, Repository::DISLIKE ], true ) ) {
			return new \WP_Error( 'cb_likes_invalid_reaction', __( 'This reaction is not supported.', 'core-blueprint-likes' ), [ 'status' => 400 ] );
		}
		if ( $user_id <= 0 || ! get_userdata( $user_id ) ) {
			return new \WP_Error( 'cb_likes_auth_required', __( 'You must be logged in to react to items.', 'core-blueprint-likes' ), [ 'status' => 401 ] );
		}
		if ( ! Targets::user_can_like( $user_id, $type, $target_id ) ) {
			return new \WP_Error( 'cb_likes_target_not_allowed', __( 'You cannot react to this item.', 'core-blueprint-likes' ), [ 'status' => 403 ] );
		}
		if ( Repository::DISLIKE === $reaction && ! Settings::dislike_enabled_for_target( $type, $target_id ) ) {
			return new \WP_Error( 'cb_likes_dislike_disabled', __( 'Dislikes are not enabled for this item.', 'core-blueprint-likes' ), [ 'status' => 403 ] );
		}

		$before = Repository::reaction_for_user( $user_id, $type, $target_id );
		if ( $before === $reaction ) {
			return self::state( $user_id, $type, $target_id );
		}

		$ok = null === $reaction
			? Repository::clear_reaction( $user_id, $type, $target_id )
			: Repository::set_reaction( $user_id, $type, $target_id, $reaction );
		if ( ! $ok ) {
			return new \WP_Error( 'cb_likes_write_failed', __( 'The reaction could not be saved.', 'core-blueprint-likes' ), [ 'status' => 500 ] );
		}

		$after = Repository::reaction_for_user( $user_id, $type, $target_id );
		$state = self::state( $user_id, $type, $target_id );
		self::fire_change_actions( $user_id, $type, $target_id, $before, $after, $state );
		return $state;
	}

	/** @return array{reaction:?string,liked:bool,disliked:bool,count:int,like_count:int,dislike_count:int}|\WP_Error */
	public static function set_liked( int $user_id, string $target_type, int $target_id, bool $liked ): array|\WP_Error {
		$current = Repository::reaction_for_user( $user_id, $target_type, $target_id );
		if ( $liked ) {
			return self::set_reaction( $user_id, $target_type, $target_id, Repository::LIKE );
		}
		return Repository::LIKE === $current
			? self::set_reaction( $user_id, $target_type, $target_id, null )
			: self::state( $user_id, Targets::normalize_type( $target_type ), $target_id );
	}

	/** @return array{reaction:?string,liked:bool,disliked:bool,count:int,like_count:int,dislike_count:int}|\WP_Error */
	public static function set_disliked( int $user_id, string $target_type, int $target_id, bool $disliked ): array|\WP_Error {
		$current = Repository::reaction_for_user( $user_id, $target_type, $target_id );
		if ( $disliked ) {
			return self::set_reaction( $user_id, $target_type, $target_id, Repository::DISLIKE );
		}
		return Repository::DISLIKE === $current
			? self::set_reaction( $user_id, $target_type, $target_id, null )
			: self::state( $user_id, Targets::normalize_type( $target_type ), $target_id );
	}

	/** @return array{reaction:?string,liked:bool,disliked:bool,count:int,like_count:int,dislike_count:int} */
	private static function state( int $user_id, string $target_type, int $target_id ): array {
		$reaction = Repository::reaction_for_user( $user_id, $target_type, $target_id );
		$like_count = Repository::count( $target_type, $target_id );
		$dislike_count = Repository::dislike_count( $target_type, $target_id );
		return [
			'reaction'      => $reaction,
			'liked'         => Repository::LIKE === $reaction,
			'disliked'      => Repository::DISLIKE === $reaction,
			'count'         => $like_count,
			'like_count'    => $like_count,
			'dislike_count' => $dislike_count,
		];
	}

	/** @param array{reaction:?string,liked:bool,disliked:bool,count:int,like_count:int,dislike_count:int} $state */
	private static function fire_change_actions( int $user_id, string $target_type, int $target_id, ?string $before, ?string $after, array $state ): void {
		if ( Repository::LIKE === $before && Repository::LIKE !== $after ) {
			do_action( 'cb_likes_unliked', $user_id, $target_type, $target_id, $state['like_count'] );
		}
		if ( Repository::DISLIKE === $before && Repository::DISLIKE !== $after ) {
			do_action( 'cb_likes_undisliked', $user_id, $target_type, $target_id, $state['dislike_count'] );
		}
		if ( Repository::LIKE === $after && Repository::LIKE !== $before ) {
			do_action( 'cb_likes_liked', $user_id, $target_type, $target_id, $state['like_count'] );
		}
		if ( Repository::DISLIKE === $after && Repository::DISLIKE !== $before ) {
			do_action( 'cb_likes_disliked', $user_id, $target_type, $target_id, $state['dislike_count'] );
		}
		do_action( 'cb_likes_reaction_changed', $user_id, $target_type, $target_id, $before, $after, $state );
	}
}

<?php
declare(strict_types=1);

namespace CB\Likes\Builder;

use CB\Likes\Repository;
use CB\Likes\Settings;
use CB\Likes\Targets;

defined( 'ABSPATH' ) || exit;

/** Builder-neutral collection queries for Likes adapters. */
final class Queries {
	/** @return array<int,\WP_Post> */
	public static function liked_posts( int $user_id, int $limit = 100 ): array {
		return self::hydrate_visible_posts( Repository::liked_post_ids( $user_id, $limit ) );
	}

	/** @return array<int,\WP_Post> */
	public static function most_liked_posts( int $limit = 100 ): array {
		return self::hydrate_visible_posts(
			Repository::most_liked_post_ids( Settings::enabled_post_types(), $limit )
		);
	}

	/** @param int[] $ids
	 *  @return array<int,\WP_Post>
	 */
	private static function hydrate_visible_posts( array $ids ): array {
		$allowed = array_flip( Settings::enabled_post_types() );
		$user_id = get_current_user_id();
		$posts   = [];

		foreach ( $ids as $id ) {
			$post = get_post( $id );
			if ( ! $post instanceof \WP_Post || ! isset( $allowed[ $post->post_type ] ) ) {
				continue;
			}
			if ( ! Targets::user_can_view( $user_id, Targets::POST, (int) $post->ID ) ) {
				continue;
			}
			$posts[] = $post;
		}

		return $posts;
	}
}

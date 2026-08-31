<?php
declare(strict_types=1);

namespace CB\Likes;

defined( 'ABSPATH' ) || exit;

final class Repository {
	public const LIKE = 'like';
	public const DISLIKE = 'dislike';

	public static function reaction_for_user( int $user_id, string $target_type, int $target_id ): ?string {
		if ( $user_id <= 0 || $target_id <= 0 ) {
			return null;
		}
		global $wpdb;
		$table = Install::table();
		$reaction = $wpdb->get_var( $wpdb->prepare(
			"SELECT reaction FROM {$table} WHERE user_id = %d AND target_type = %s AND target_id = %d LIMIT 1",
			$user_id,
			Targets::normalize_type( $target_type ),
			$target_id
		) );
		return in_array( $reaction, [ self::LIKE, self::DISLIKE ], true ) ? (string) $reaction : null;
	}

	public static function user_has_liked( int $user_id, string $target_type, int $target_id ): bool {
		return self::LIKE === self::reaction_for_user( $user_id, $target_type, $target_id );
	}

	public static function user_has_disliked( int $user_id, string $target_type, int $target_id ): bool {
		return self::DISLIKE === self::reaction_for_user( $user_id, $target_type, $target_id );
	}

	public static function set_reaction( int $user_id, string $target_type, int $target_id, string $reaction ): bool {
		$reaction = self::normalize_reaction( $reaction );
		if ( null === $reaction ) {
			return false;
		}
		global $wpdb;
		$table = Install::table();
		$now = current_time( 'mysql', true );
		$sql = "INSERT INTO {$table} (user_id,target_type,target_id,reaction,created_at,updated_at)
			VALUES (%d,%s,%d,%s,%s,%s)
			ON DUPLICATE KEY UPDATE reaction = %s, updated_at = %s";
		$result = $wpdb->query( $wpdb->prepare(
			$sql,
			$user_id,
			Targets::normalize_type( $target_type ),
			$target_id,
			$reaction,
			$now,
			$now,
			$reaction,
			$now
		) );
		return false !== $result;
	}

	public static function clear_reaction( int $user_id, string $target_type, int $target_id ): bool {
		global $wpdb;
		$result = $wpdb->delete(
			Install::table(),
			[ 'user_id' => $user_id, 'target_type' => Targets::normalize_type( $target_type ), 'target_id' => $target_id ],
			[ '%d', '%s', '%d' ]
		);
		return false !== $result;
	}

	/** Backwards-compatible like writer. */
	public static function add( int $user_id, string $target_type, int $target_id ): bool {
		return self::set_reaction( $user_id, $target_type, $target_id, self::LIKE );
	}

	/** Backwards-compatible like remover. */
	public static function remove( int $user_id, string $target_type, int $target_id ): bool {
		return self::clear_reaction( $user_id, $target_type, $target_id );
	}

	public static function count( string $target_type, int $target_id ): int {
		return self::count_reaction( $target_type, $target_id, self::LIKE );
	}

	public static function dislike_count( string $target_type, int $target_id ): int {
		return self::count_reaction( $target_type, $target_id, self::DISLIKE );
	}

	public static function count_reaction( string $target_type, int $target_id, string $reaction ): int {
		$reaction = self::normalize_reaction( $reaction );
		if ( $target_id <= 0 || null === $reaction ) {
			return 0;
		}
		global $wpdb;
		$table = Install::table();
		return (int) $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM {$table} WHERE target_type = %s AND target_id = %d AND reaction = %s",
			Targets::normalize_type( $target_type ),
			$target_id,
			$reaction
		) );
	}

	/** @return int[] */
	public static function liked_post_ids( int $user_id, int $limit = 100 ): array {
		if ( $user_id <= 0 ) {
			return [];
		}
		global $wpdb;
		$table = Install::table();
		$rows = $wpdb->get_col( $wpdb->prepare(
			"SELECT target_id FROM {$table} WHERE user_id = %d AND target_type = 'post' AND reaction = 'like' ORDER BY COALESCE(updated_at,created_at) DESC, id DESC LIMIT %d",
			$user_id,
			max( 1, min( 500, $limit ) )
		) );
		return array_values( array_filter( array_map( 'intval', $rows ) ) );
	}

	/** @param string[] $post_types
	 *  @return int[]
	 */
	public static function most_liked_post_ids( array $post_types, int $limit = 100 ): array {
		$post_types = array_values( array_filter( array_map( 'sanitize_key', $post_types ) ) );
		if ( [] === $post_types ) {
			return [];
		}
		global $wpdb;
		$table = Install::table();
		$placeholders = implode( ',', array_fill( 0, count( $post_types ), '%s' ) );
		$params = array_merge( $post_types, [ max( 1, min( 500, $limit ) ) ] );
		$sql = "SELECT l.target_id FROM {$table} l INNER JOIN {$wpdb->posts} p ON p.ID = l.target_id WHERE l.target_type = 'post' AND l.reaction = 'like' AND p.post_status = 'publish' AND p.post_type IN ({$placeholders}) GROUP BY l.target_id ORDER BY COUNT(*) DESC, MAX(COALESCE(l.updated_at,l.created_at)) DESC LIMIT %d";
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- placeholders are generated from a trusted array length.
		$rows = $wpdb->get_col( $wpdb->prepare( $sql, ...$params ) );
		return array_values( array_filter( array_map( 'intval', $rows ) ) );
	}

	public static function total_count( ?string $reaction = null ): int {
		global $wpdb;
		$table = Install::table();
		if ( null === $reaction ) {
			return (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" );
		}
		$reaction = self::normalize_reaction( $reaction );
		if ( null === $reaction ) {
			return 0;
		}
		return (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE reaction = %s", $reaction ) );
	}

	public static function unique_user_count(): int {
		global $wpdb;
		$table = Install::table();
		return (int) $wpdb->get_var( "SELECT COUNT(DISTINCT user_id) FROM {$table}" );
	}

	public static function delete_target( string $target_type, int $target_id ): void {
		global $wpdb;
		$wpdb->delete( Install::table(), [ 'target_type' => Targets::normalize_type( $target_type ), 'target_id' => $target_id ], [ '%s', '%d' ] );
	}

	public static function delete_by_user( int $user_id ): int {
		global $wpdb;
		$result = $wpdb->delete( Install::table(), [ 'user_id' => $user_id ], [ '%d' ] );
		return false === $result ? 0 : (int) $result;
	}

	/** @return array<int,array<string,mixed>> */
	public static function rows_for_user( int $user_id, int $offset = 0, int $limit = 100 ): array {
		global $wpdb;
		$table = Install::table();
		$rows = $wpdb->get_results( $wpdb->prepare(
			"SELECT id,target_type,target_id,reaction,created_at,updated_at FROM {$table} WHERE user_id = %d ORDER BY id ASC LIMIT %d OFFSET %d",
			$user_id,
			$limit,
			$offset
		), ARRAY_A );
		return is_array( $rows ) ? $rows : [];
	}

	private static function normalize_reaction( string $reaction ): ?string {
		$reaction = sanitize_key( $reaction );
		return in_array( $reaction, [ self::LIKE, self::DISLIKE ], true ) ? $reaction : null;
	}
}

<?php
declare(strict_types=1);

namespace CB\Likes\Privacy;

use CB\Likes\Repository;
defined( 'ABSPATH' ) || exit;

final class Integration {
	public static function init(): void {
		if ( ! self::runtime_ready() ) {
			return;
		}
		add_filter( 'wp_privacy_personal_data_exporters', [ __CLASS__, 'exporters' ] );
		add_filter( 'wp_privacy_personal_data_erasers', [ __CLASS__, 'erasers' ] );
	}

	/** @param array<string,mixed> $exporters
	 *  @return array<string,mixed>
	 */
	public static function exporters( array $exporters ): array {
		if ( ! self::runtime_ready() ) {
			return $exporters;
		}
		$exporters['core-blueprint-likes'] = [
			'exporter_friendly_name' => __( 'Core Blueprint Likes', 'core-blueprint-likes' ),
			'callback' => [ __CLASS__, 'export' ],
		];
		return $exporters;
	}

	/** @param array<string,mixed> $erasers
	 *  @return array<string,mixed>
	 */
	public static function erasers( array $erasers ): array {
		if ( ! self::runtime_ready() ) {
			return $erasers;
		}
		$erasers['core-blueprint-likes'] = [
			'eraser_friendly_name' => __( 'Core Blueprint Likes', 'core-blueprint-likes' ),
			'callback' => [ __CLASS__, 'erase' ],
		];
		return $erasers;
	}

	/** @return array{data:array<int,mixed>,done:bool} */
	public static function export( string $email, int $page = 1 ): array {
		if ( ! self::runtime_ready() ) {
			return [ 'data' => [], 'done' => true ];
		}
		$user = get_user_by( 'email', $email );
		if ( ! $user ) {
			return [ 'data' => [], 'done' => true ];
		}
		$limit = 100;
		$rows = Repository::rows_for_user( (int) $user->ID, max( 0, $page - 1 ) * $limit, $limit );
		$data = [];
		foreach ( $rows as $row ) {
			$data[] = [
				'group_id' => 'core-blueprint-likes',
				'group_label' => __( 'Reactions', 'core-blueprint-likes' ),
				'item_id' => 'cb-reaction-' . (int) $row['id'],
				'data' => [
					[ 'name' => __( 'Target type', 'core-blueprint-likes' ), 'value' => (string) $row['target_type'] ],
					[ 'name' => __( 'Target ID', 'core-blueprint-likes' ), 'value' => (string) $row['target_id'] ],
					[ 'name' => __( 'Reaction', 'core-blueprint-likes' ), 'value' => (string) $row['reaction'] ],
					[ 'name' => __( 'Created at (UTC)', 'core-blueprint-likes' ), 'value' => (string) $row['created_at'] ],
					[ 'name' => __( 'Updated at (UTC)', 'core-blueprint-likes' ), 'value' => (string) ( $row['updated_at'] ?? '' ) ],
				],
			];
		}
		return [ 'data' => $data, 'done' => count( $rows ) < $limit ];
	}

	/** @return array{items_removed:bool,items_retained:bool,messages:string[],done:bool} */
	public static function erase( string $email, int $page = 1 ): array {
		unset( $page );
		if ( ! self::runtime_ready() ) {
			return [
				'items_removed'  => false,
				'items_retained' => true,
				'messages'       => [ self::dependency_message() ],
				'done'           => true,
			];
		}
		$user = get_user_by( 'email', $email );
		$removed = $user ? Repository::delete_by_user( (int) $user->ID ) > 0 : false;
		return [ 'items_removed' => $removed, 'items_retained' => false, 'messages' => [], 'done' => true ];
	}

	private static function runtime_ready(): bool {
		return function_exists( 'cb_likes_runtime_ready' ) && \cb_likes_runtime_ready();
	}

	private static function dependency_message(): string {
		return function_exists( 'cb_likes_dependency_message' )
			? \cb_likes_dependency_message()
			: 'Core Blueprint Likes runtime is unavailable.';
	}
}

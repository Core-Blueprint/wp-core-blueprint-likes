<?php
declare(strict_types=1);

namespace CB\Likes;

defined( 'ABSPATH' ) || exit;

final class Capabilities {
	public const MANAGE = 'cb_likes_manage';

	/** @param array<string,mixed> $catalog
	 *  @return array<string,mixed>
	 */
	public static function register_catalog( array $catalog ): array {
		$catalog[ self::MANAGE ] = [
			'label' => __( 'Manage Likes', 'core-blueprint-likes' ),
			'group' => __( 'Core Blueprint Likes', 'core-blueprint-likes' ),
		];
		return $catalog;
	}
}

<?php
declare(strict_types=1);

namespace CB\Likes\Admin;

defined( 'ABSPATH' ) || exit;

/** Likes-owned provider readiness for the shared Base Integration Grid. */
final class IntegrationReadiness {
	/** @return array<int,array<string,mixed>> */
	public static function items(): array {
		$active = defined( 'BRICKS_VERSION' ) || class_exists( '\\Bricks\\Elements' );
		return [
			[
				'name' => __( 'Bricks Builder', 'core-blueprint-likes' ),
				'description' => $active
					? __( 'Likes is available in Bricks through dynamic data, conditions, Query Loops and dedicated Like and Dislike elements. Bricks remains optional.', 'core-blueprint-likes' )
					: __( 'Bricks integration is optional. Likes works without a page builder and enables its Bricks adapter automatically when Bricks is active.', 'core-blueprint-likes' ),
				'status' => $active ? 'ready' : 'optional',
				'status_label' => $active
					? __( 'Ready', 'core-blueprint-likes' )
					: __( 'Not active', 'core-blueprint-likes' ),
			],
		];
	}
}

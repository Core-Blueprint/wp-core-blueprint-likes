<?php
declare(strict_types=1);

namespace CB\Likes\Integration;

use CB\Likes\Admin\CoreBlueprintPage;
use CB\Likes\Capabilities;
use CB\Core\Dashboard\CardRegistry;

defined( 'ABSPATH' ) || exit;

final class CoreBlueprint {
	public static function init(): void {
		add_action( 'cb_core_register_pages', [ __CLASS__, 'register_page' ] );
		add_filter( 'plugin_action_links_' . CB_LIKES_BASENAME, [ __CLASS__, 'plugin_links' ] );
		add_action( 'cb_core_dashboard_register_cards', [ __CLASS__, 'register_dashboard_shortcuts' ] );
	}


	public static function register_dashboard_shortcuts(): void {
		if ( ! class_exists( CardRegistry::class ) ) {
			return;
		}

		CardRegistry::register_shortcut( 'core-blueprint-likes', [
			'id'         => 'settings',
			'label'      => __( 'Settings', 'core-blueprint-likes' ),
			'url'        => admin_url( 'admin.php?page=core-blueprint-likes' ),
			'capability' => Capabilities::MANAGE,
			'order'      => 10,
		] );
	}

	public static function register_page(): void {
		if ( ! class_exists( '\\CB\\Core\\Admin\\PageRegistry' ) || ! class_exists( '\\CB\\Core\\Admin\\PageBase' ) ) {
			return;
		}
		\CB\Core\Admin\PageRegistry::register( new CoreBlueprintPage() );
	}

	/** @param string[] $links
	 *  @return string[]
	 */
	public static function plugin_links( array $links ): array {
		$url = defined( 'CB_CORE_VERSION' )
			? admin_url( 'admin.php?page=core-blueprint-likes' )
			: admin_url( 'options-general.php?page=core-blueprint-likes' );
		array_unshift( $links, '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Settings', 'core-blueprint-likes' ) . '</a>' );
		return $links;
	}
}

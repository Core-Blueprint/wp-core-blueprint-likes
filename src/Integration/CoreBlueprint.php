<?php
declare(strict_types=1);

namespace CB\Likes\Integration;

use CB\Core\Admin\PageRegistry;
use CB\Core\Dashboard\CardRegistry;
use CB\Core\ExtensionRegistry;
use CB\Likes\Admin\CoreBlueprintPage;
use CB\Likes\Capabilities;

defined( 'ABSPATH' ) || exit;

final class CoreBlueprint {
	public const ID = 'core-blueprint-likes';

	public static function init(): void {
		add_action( 'cb_core_register_pages', [ __CLASS__, 'register_page' ] );
		add_action( 'cb_core_register_extensions', [ __CLASS__, 'register_extension' ] );
		add_filter( 'cb_core_module_status_definitions', [ __CLASS__, 'register_status_definition' ] );
		add_filter( 'plugin_action_links_' . CB_LIKES_BASENAME, [ __CLASS__, 'plugin_links' ] );
		add_action( 'cb_core_dashboard_register_cards', [ __CLASS__, 'register_dashboard_shortcuts' ] );
	}

	public static function register_dashboard_shortcuts(): void {
		CardRegistry::register_shortcut( self::ID, [
			'id'         => 'settings',
			'label'      => __( 'Settings', 'core-blueprint-likes' ),
			'url'        => admin_url( 'admin.php?page=' . self::ID ),
			'capability' => Capabilities::MANAGE,
			'order'      => 10,
		] );
	}

	public static function register_page(): void {
		PageRegistry::register(
			new CoreBlueprintPage(),
			[
				'foundations' => [ 'clipboard' ],
				'components'  => [ 'cards', 'nav-tabs', 'fields', 'disclosure', 'form-controls' ],
			]
		);
	}

	public static function register_extension(): void {
		ExtensionRegistry::register( [
			'id'           => self::ID,
			'plugin_file'  => CB_LIKES_BASENAME,
			'requires_api' => '1.0',
			'menu_url'     => admin_url( 'admin.php?page=' . self::ID ),
			'status_id'    => self::ID,
		] );
	}

	/** @param array<string,array<string,mixed>> $definitions
	 *  @return array<string,array<string,mixed>>
	 */
	public static function register_status_definition( array $definitions ): array {
		$definitions[ self::ID ] = [
			'provider' => [ __CLASS__, 'status' ],
			'label'    => __( 'Likes', 'core-blueprint-likes' ),
			'url'      => admin_url( 'admin.php?page=' . self::ID ),
		];
		return $definitions;
	}

	/** @return array{state:string,detail:string,url:string} */
	public static function status(): array {
		return [
			'state'  => 'ok',
			'detail' => __( 'Connected through the Core Blueprint public extension contracts.', 'core-blueprint-likes' ),
			'url'    => admin_url( 'admin.php?page=' . self::ID ),
		];
	}

	/** @param string[] $links
	 *  @return string[]
	 */
	public static function plugin_links( array $links ): array {
		$url = admin_url( 'admin.php?page=' . self::ID );
		array_unshift( $links, '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Settings', 'core-blueprint-likes' ) . '</a>' );
		return $links;
	}
}

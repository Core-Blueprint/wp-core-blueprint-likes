<?php
declare(strict_types=1);

namespace CB\Likes\Integration;

use CB\Core\Admin\SettingsRegistry;
use CB\Core\Dashboard\CardRegistry;
use CB\Core\ExtensionRegistry;
use CB\Likes\Admin\PageContent;
use CB\Likes\Capabilities;
use CB\Likes\Repository;
use CB\Likes\Settings;

defined( 'ABSPATH' ) || exit;

final class CoreBlueprint {
	public const ID = 'core-blueprint-likes';

	public static function init(): void {
		add_action( 'cb_core_register_settings', [ __CLASS__, 'register_settings_provider' ] );
		add_action( 'cb_core_register_extensions', [ __CLASS__, 'register_extension' ] );
		add_filter( 'cb_core_module_status_definitions', [ __CLASS__, 'register_status_definition' ] );
		add_filter( 'plugin_action_links_' . CB_LIKES_BASENAME, [ __CLASS__, 'plugin_links' ] );
		add_action( 'cb_core_dashboard_register_cards', [ __CLASS__, 'register_dashboard_shortcuts' ] );
	}

	public static function register_dashboard_shortcuts(): void {
		CardRegistry::register_shortcut( self::ID, [
			'id'         => 'settings',
			'label'      => __( 'Settings', 'core-blueprint-likes' ),
			'url'        => SettingsRegistry::url( self::ID ),
			'capability' => Capabilities::MANAGE,
			'order'      => 10,
		] );
	}

	public static function register_settings_provider(): void {
		SettingsRegistry::register(
			self::ID,
			[
				'label'       => __( 'Likes', 'core-blueprint-likes' ),
				'description' => __( 'Privacy-first likes and optional dislikes for selected WordPress content types and, optionally, user profiles. Reactions are account-based and store no IP address, fingerprint, browser, device or location data.', 'core-blueprint-likes' ),
				'group'       => SettingsRegistry::GROUP_COMMUNITY,
				'capability'  => Capabilities::MANAGE,
				'renderer'    => [ PageContent::class, 'render' ],
				'requirements' => [
					'foundations' => [ 'clipboard' ],
					'components'  => [ 'actions', 'cards', 'metric-tiles', 'nav-tabs', 'fields', 'disclosure', 'form-controls', 'integration-grid', 'status' ],
				],
			]
		);
	}

	public static function register_extension(): void {
		ExtensionRegistry::register( [
			'id'           => self::ID,
			'plugin_file'  => CB_LIKES_BASENAME,
			'requires_api' => '1.0',
			'menu_url'     => SettingsRegistry::url( self::ID ),
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
			'url'      => SettingsRegistry::url( self::ID ),
		];
		return $definitions;
	}

	/** @return array{state:string,detail:string,url:string} */
	public static function status(): array {
		$settings = Settings::all();

		return [
			'state'  => 'ok',
			'detail' => sprintf(
				'%1$s: %2$s · %3$s: %4$s · %5$s: %6$s',
				__( 'Reactions', 'core-blueprint-likes' ),
				number_format_i18n( Repository::total_count() ),
				__( 'Post types', 'core-blueprint-likes' ),
				number_format_i18n( count( $settings['post_types'] ) ),
				__( 'User profiles', 'core-blueprint-likes' ),
				$settings['users_enabled'] ? __( 'Enabled', 'core-blueprint-likes' ) : __( 'Disabled', 'core-blueprint-likes' )
			),
			'url'    => SettingsRegistry::url( self::ID ),
		];
	}

	/** @param string[] $links
	 *  @return string[]
	 */
	public static function plugin_links( array $links ): array {
		$url = SettingsRegistry::url( self::ID );
		array_unshift( $links, '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Settings', 'core-blueprint-likes' ) . '</a>' );
		return $links;
	}
}

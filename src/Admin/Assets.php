<?php
declare(strict_types=1);

namespace CB\Likes\Admin;

use CB\Likes\Icons;

defined( 'ABSPATH' ) || exit;

final class Assets {
	public static function init(): void {
		add_action( 'admin_enqueue_scripts', [ __CLASS__, 'enqueue' ], 20 );
	}

	public static function enqueue( string $hook ): void {
		if ( ! self::is_likes_screen( $hook ) ) {
			return;
		}

		$dependencies = [];
		if ( wp_style_is( 'cb-core-css-tokens', 'registered' ) || wp_style_is( 'cb-core-css-tokens', 'enqueued' ) ) {
			$dependencies[] = 'cb-core-css-tokens';
		}

		wp_enqueue_style(
			'cb-likes-admin',
			CB_LIKES_URL . 'assets/css/admin.css',
			$dependencies,
			CB_LIKES_VERSION
		);

		$clipboard_available = class_exists( '\\CB\\Core\\UI\\Assets' )
			&& method_exists( '\\CB\\Core\\UI\\Assets', 'enqueue_clipboard' );

		if ( $clipboard_available ) {
			\CB\Core\UI\Assets::enqueue_clipboard();
		}

		wp_enqueue_script(
			'cb-likes-admin',
			CB_LIKES_URL . 'assets/js/admin.js',
			[],
			CB_LIKES_VERSION,
			true
		);

		if ( $clipboard_available ) {
			wp_enqueue_script_module(
				'@cb-likes/clipboard',
				CB_LIKES_URL . 'assets/js/clipboard.js',
				[ '@cb-core/clipboard' ],
				CB_LIKES_VERSION
			);
		}

		wp_enqueue_media();
		$settings_saved = isset( $_GET['settings-updated'] )
			&& 'true' === sanitize_key( wp_unslash( (string) $_GET['settings-updated'] ) );

		wp_localize_script(
			'cb-likes-admin',
			'cbLikesAdmin',
			[
				'mediaTitle'          => __( 'Choose reaction icon', 'core-blueprint-likes' ),
				'mediaButton'         => __( 'Use this icon', 'core-blueprint-likes' ),
				'builtInIcons'        => Icons::svgs(),
				'corePresentation'    => defined( 'CB_CORE_VERSION' ) && 'core-blueprint_page_core-blueprint-likes' === $hook,
				'settingsSaved'       => $settings_saved,
				'settingsSavedMessage'  => __( 'Settings saved.', 'core-blueprint-likes' ),
			]
		);
	}

	private static function is_likes_screen( string $hook ): bool {
		if ( in_array( $hook, [ 'settings_page_core-blueprint-likes', 'core-blueprint_page_core-blueprint-likes' ], true ) ) {
			return true;
		}

		return isset( $_GET['page'] ) && 'core-blueprint-likes' === sanitize_key( wp_unslash( (string) $_GET['page'] ) );
	}
}

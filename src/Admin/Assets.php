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

		wp_enqueue_style(
			'cb-likes-admin',
			CB_LIKES_URL . 'assets/css/admin.css',
			[],
			CB_LIKES_VERSION
		);

		wp_enqueue_script(
			'cb-likes-admin',
			CB_LIKES_URL . 'assets/js/admin.js',
			[],
			CB_LIKES_VERSION,
			true
		);

		wp_enqueue_script_module(
			'@cb-likes/clipboard',
			CB_LIKES_URL . 'assets/js/clipboard.js',
			[ '@cb-core/clipboard' ],
			CB_LIKES_VERSION
		);

		wp_enqueue_media();
		$settings_saved = isset( $_GET['settings-updated'] )
			&& 'true' === sanitize_key( wp_unslash( (string) $_GET['settings-updated'] ) );

		wp_localize_script(
			'cb-likes-admin',
			'cbLikesAdmin',
			[
				'mediaTitle'            => __( 'Choose reaction icon', 'core-blueprint-likes' ),
				'mediaButton'           => __( 'Use this icon', 'core-blueprint-likes' ),
				'builtInIcons'          => Icons::svgs(),
				'corePresentation'      => true,
				'settingsSaved'         => $settings_saved,
				'settingsSavedMessage'  => __( 'Settings saved.', 'core-blueprint-likes' ),
			]
		);
	}

	private static function is_likes_screen( string $hook ): bool {
		return 'core-blueprint_page_core-blueprint-likes' === $hook;
	}
}

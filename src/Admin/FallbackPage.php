<?php
declare(strict_types=1);

namespace CB\Likes\Admin;

use CB\Likes\Capabilities;

defined( 'ABSPATH' ) || exit;

final class FallbackPage {
	public static function init(): void {
		add_action( 'admin_menu', [ __CLASS__, 'menu' ] );
	}

	public static function menu(): void {
		if ( defined( 'CB_CORE_VERSION' ) ) {
			return;
		}
		add_options_page(
			__( 'Core Blueprint Likes', 'core-blueprint-likes' ),
			__( 'Likes', 'core-blueprint-likes' ),
			Capabilities::MANAGE,
			'core-blueprint-likes',
			[ __CLASS__, 'render' ]
		);
	}

	public static function render(): void {
		if ( ! current_user_can( Capabilities::MANAGE ) && ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to manage Likes.', 'core-blueprint-likes' ) );
		}
		PageContent::render( false );
	}
}

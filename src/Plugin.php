<?php
declare(strict_types=1);

namespace CB\Likes;

use CB\Likes\Admin\Assets as AdminAssets;
use CB\Likes\Bricks\Integration as BricksIntegration;
use CB\Likes\Frontend\Renderer;
use CB\Likes\Governance\Audit as GovernanceAudit;
use CB\Likes\Integration\CoreBlueprint;
use CB\Likes\Privacy\Integration as PrivacyIntegration;
use CB\Likes\Rest\Controller;

defined( 'ABSPATH' ) || exit;

final class Plugin {
	private static bool $booted = false;

	public static function boot(): void {
		if ( self::$booted ) {
			return;
		}
		self::$booted = true;

		Install::maybe_upgrade();

		add_action( 'admin_init', [ __CLASS__, 'register_settings' ] );
		add_filter( 'option_page_capability_cb_likes_settings_group', static fn(): string => Capabilities::MANAGE );
		add_filter( 'cb_core_capability_catalog', [ Capabilities::class, 'register_catalog' ] );
		add_action( 'before_delete_post', static fn( int $post_id ): mixed => Repository::delete_target( Targets::POST, $post_id ) );
		add_action( 'deleted_user', [ __CLASS__, 'deleted_user' ], 10, 1 );

		AdminAssets::init();
		Controller::init();
		Renderer::init();
		CoreBlueprint::init();
		PrivacyIntegration::init();
		BricksIntegration::init();
		GovernanceAudit::init();

		do_action( 'cb_likes_loaded' );
	}

	public static function register_settings(): void {
		register_setting( 'cb_likes_settings_group', Settings::OPTION, [
			'type' => 'array',
			'sanitize_callback' => [ Settings::class, 'sanitize' ],
			'default' => [ 'post_types' => [ 'post' ], 'users_enabled' => false, 'global' => [ 'logged_in_only' => true ] ],
		] );
	}

	public static function deleted_user( int $user_id ): void {
		Repository::delete_by_user( $user_id );
		Repository::delete_target( Targets::USER, $user_id );
	}
}

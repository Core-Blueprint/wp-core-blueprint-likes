<?php
declare(strict_types=1);

namespace CB\Likes\Integration;

defined( 'ABSPATH' ) || exit;

/** Optional adapter for the central Core Blueprint Updates client. */
final class Updates {
	public const PRODUCT_KEY = 'core-blueprint-likes';
	public const VENDOR_ID   = 'core-blueprint';

	private static bool $registered = false;

	public static function init(): void {
		if ( self::$registered ) {
			return;
		}
		self::$registered = true;
		add_action( 'cb_updates_register_products', [ self::class, 'register_product' ] );
	}

	public static function register_product(): void {
		$registry = '\\CB\\Updates\\ProductRegistry';
		if ( ! class_exists( $registry ) ) {
			return;
		}

		$registry::register( [
			'name'          => 'Core Blueprint Likes',
			'plugin'        => CB_LIKES_BASENAME,
			'version'       => CB_LIKES_VERSION,
			'product_key'   => self::PRODUCT_KEY,
			'vendor_id'     => self::VENDOR_ID,
			'software_uuid' => '',
		] );
	}
}

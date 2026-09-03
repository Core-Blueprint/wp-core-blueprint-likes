<?php
/**
 * Plugin Name:       Core Blueprint Likes
 * Plugin URI:        https://coreblueprint.io
 * Description:       Lightweight privacy-first likes and optional dislikes for WordPress posts and users, with optional Bricks integration.
 * Version:           1.0.0-rc1
 * Author:            Core Blueprint
 * Author URI:        https://coreblueprint.io
 * License:           GPL-2.0+
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       core-blueprint-likes
 * Domain Path:       /languages
 * Requires at least: 7.0
 * Requires PHP:      8.0
 *
 * @package CB_Likes
 */

declare(strict_types=1);

defined( 'ABSPATH' ) || exit;

define( 'CB_LIKES_VERSION', '1.0.0-rc1' );
define( 'CB_LIKES_REQUIRED_API', '1.0' );
define( 'CB_LIKES_FILE', __FILE__ );
define( 'CB_LIKES_DIR', plugin_dir_path( __FILE__ ) );
define( 'CB_LIKES_URL', plugin_dir_url( __FILE__ ) );
define( 'CB_LIKES_BASENAME', plugin_basename( __FILE__ ) );

if ( version_compare( PHP_VERSION, '8.0', '<' ) ) {
	add_action( 'admin_notices', static function (): void {
		echo '<div class="notice notice-error"><p><strong>Core Blueprint Likes:</strong> ';
		printf(
			/* translators: %s: current PHP version */
			esc_html__( 'requires PHP 8.0 or higher. This server runs PHP %s.', 'core-blueprint-likes' ),
			esc_html( PHP_VERSION )
		);
		echo '</p></div>';
	} );
	return;
}

spl_autoload_register( static function ( string $class ): void {
	$prefix = 'CB\\Likes\\';
	if ( 0 !== strncmp( $class, $prefix, strlen( $prefix ) ) ) {
		return;
	}
	$relative = substr( $class, strlen( $prefix ) );
	$file     = CB_LIKES_DIR . 'src/' . str_replace( '\\', '/', $relative ) . '.php';
	if ( is_readable( $file ) ) {
		require_once $file;
	}
} );

function cb_likes_api_compatible( string $available, string $required ): bool {
	if ( 1 !== preg_match( '/^(\d+)\.(\d+)$/', $available, $available_match ) ) {
		return false;
	}
	if ( 1 !== preg_match( '/^(\d+)\.(\d+)$/', $required, $required_match ) ) {
		return false;
	}

	return (int) $available_match[1] === (int) $required_match[1]
		&& (int) $available_match[2] >= (int) $required_match[2];
}

function cb_likes_base_ready(): bool {
	if ( ! defined( 'CB_CORE_API_VERSION' ) ) {
		return false;
	}
	if ( ! cb_likes_api_compatible( (string) CB_CORE_API_VERSION, CB_LIKES_REQUIRED_API ) ) {
		return false;
	}

	return class_exists( '\\CB\\Core\\ExtensionRegistry' )
		&& class_exists( '\\CB\\Core\\Admin\\PageRegistry' )
		&& interface_exists( '\\CB\\Core\\Admin\\Page' );
}

function cb_likes_dependency_message(): string {
	if ( ! defined( 'CB_CORE_API_VERSION' ) ) {
		return __( 'Core Blueprint Likes requires an active Core Blueprint Base plugin.', 'core-blueprint-likes' );
	}

	if ( ! cb_likes_api_compatible( (string) CB_CORE_API_VERSION, CB_LIKES_REQUIRED_API ) ) {
		return sprintf(
			/* translators: 1: required Core API version, 2: available Core API version. */
			__( 'Core Blueprint Likes requires Core API %1$s or a newer compatible minor version. This site provides %2$s.', 'core-blueprint-likes' ),
			CB_LIKES_REQUIRED_API,
			(string) CB_CORE_API_VERSION
		);
	}

	return __( 'Core Blueprint Likes cannot access the required public Base contracts.', 'core-blueprint-likes' );
}

function cb_likes_activate(): void {
	if ( ! cb_likes_base_ready() ) {
		if ( ! function_exists( 'deactivate_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		deactivate_plugins( CB_LIKES_BASENAME );
		wp_die(
			esc_html( 'Core Blueprint Likes requires an active, Core API 1.x compatible Core Blueprint Base installation.' ),
			esc_html( 'Core Blueprint dependency required' ),
			[ 'back_link' => true ]
		);
	}

	\CB\Likes\Install::activate();
}
register_activation_hook( __FILE__, 'cb_likes_activate' );

add_action( 'init', static function (): void {
	load_plugin_textdomain( 'core-blueprint-likes', false, dirname( CB_LIKES_BASENAME ) . '/languages' );
}, 1 );

add_action( 'plugins_loaded', static function (): void {
	if ( ! cb_likes_base_ready() ) {
		if ( is_admin() ) {
			add_action( 'admin_notices', static function (): void {
				if ( ! current_user_can( 'activate_plugins' ) ) {
					return;
				}
				printf(
					'<div class="notice notice-error"><p><strong>%s</strong> %s</p></div>',
					esc_html__( 'Core Blueprint Likes:', 'core-blueprint-likes' ),
					esc_html( cb_likes_dependency_message() )
				);
			} );
		}
		return;
	}

	\CB\Likes\Plugin::boot();
}, 30 );

/** Public API: return the number of likes for a target. */
function cb_likes_count( string $target_type, int $target_id ): int {
	return \CB\Likes\Repository::count( $target_type, $target_id );
}

/** Public API: determine whether a user likes a target. */
function cb_likes_user_has_liked( int $user_id, string $target_type, int $target_id ): bool {
	return \CB\Likes\Repository::user_has_liked( $user_id, $target_type, $target_id );
}

/** Public API: set a user's like state for a valid target. */
function cb_likes_set_liked( int $user_id, string $target_type, int $target_id, bool $liked ): bool {
	$result = \CB\Likes\Service::set_liked( $user_id, $target_type, $target_id, $liked );
	return ! is_wp_error( $result ) && (bool) $result['liked'];
}

/** Public API: return the number of dislikes for a target. */
function cb_likes_dislike_count( string $target_type, int $target_id ): int {
	return \CB\Likes\Repository::dislike_count( $target_type, $target_id );
}

/** Public API: determine whether a user dislikes a target. */
function cb_likes_user_has_disliked( int $user_id, string $target_type, int $target_id ): bool {
	return \CB\Likes\Repository::user_has_disliked( $user_id, $target_type, $target_id );
}

/** Public API: set a user's dislike state for a valid target. */
function cb_likes_set_disliked( int $user_id, string $target_type, int $target_id, bool $disliked ): bool {
	$result = \CB\Likes\Service::set_disliked( $user_id, $target_type, $target_id, $disliked );
	return ! is_wp_error( $result ) && (bool) $result['disliked'];
}

/** Public API: set one mutually exclusive reaction (`like`, `dislike` or null). */
function cb_likes_set_reaction( int $user_id, string $target_type, int $target_id, ?string $reaction ): array|\WP_Error {
	return \CB\Likes\Service::set_reaction( $user_id, $target_type, $target_id, $reaction );
}

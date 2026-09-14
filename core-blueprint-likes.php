<?php
/**
 * Plugin Name:       Core Blueprint Likes
 * Plugin URI:        https://coreblueprint.io
 * Update URI:        https://coreblueprint.io/
 * Description:       Lightweight privacy-first likes and optional dislikes for WordPress posts and users, with optional Bricks integration.
 * Version:           1.0.0-rc1
 * Author:            Core Blueprint
 * Author URI:        https://coreblueprint.io
 * License:           GPL-2.0+
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       core-blueprint-likes
 * Domain Path:       /languages
 * Requires at least: 7.0
 * Requires PHP:      8.4
 * Requires Plugins:  core-blueprint
 *
 * @package CB_Likes
 */

declare(strict_types=1);

defined( 'ABSPATH' ) || exit;

define( 'CB_LIKES_VERSION', '1.0.0-rc1' );
define( 'CB_LIKES_MIN_PHP', '8.4' );
define( 'CB_LIKES_REQUIRED_API', '1.0' );
define( 'CB_LIKES_FILE', __FILE__ );
define( 'CB_LIKES_DIR', plugin_dir_path( __FILE__ ) );
define( 'CB_LIKES_URL', plugin_dir_url( __FILE__ ) );
define( 'CB_LIKES_BASENAME', plugin_basename( __FILE__ ) );

/* Bootstrap v1 earliest-safe PHP boundary. */
if ( version_compare( PHP_VERSION, CB_LIKES_MIN_PHP, '<' ) ) {
	register_activation_hook( __FILE__, static function (): void {
		if ( ! function_exists( 'deactivate_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		deactivate_plugins( CB_LIKES_BASENAME );
		wp_die(
			esc_html( sprintf( 'PHP %1$s or newer is required. This server runs PHP %2$s.', CB_LIKES_MIN_PHP, PHP_VERSION ) ),
			esc_html( 'Core Blueprint requirements not met' ),
			[ 'back_link' => true ]
		);
	} );

	add_action( 'admin_notices', static function (): void {
		echo '<div class="notice notice-error"><p><strong>Core Blueprint Likes:</strong> ';
		printf(
			/* translators: %s: current PHP version */
			esc_html__( 'requires PHP 8.4 or higher. This server runs PHP %s.', 'core-blueprint-likes' ),
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

add_action( 'init', static function (): void {
	load_plugin_textdomain( 'core-blueprint-likes', false, dirname( CB_LIKES_BASENAME ) . '/languages' );
}, 1 );

/** Product-specific public Base contracts. Not part of generic Bootstrap v1 readiness. */
function cb_likes_base_contracts_ready(): bool {
	return class_exists( '\\CB\\Core\\ExtensionRegistry' )
		&& class_exists( '\\CB\\Core\\Admin\\SettingsRegistry' )
		&& class_exists( '\\CB\\Core\\UI\\IntegrationGrid' )
		&& method_exists( '\\CB\\Core\\UI\\IntegrationGrid', 'render' )
		&& class_exists( '\\CB\\Core\\UI\\Icon' )
		&& method_exists( '\\CB\\Core\\UI\\Icon', 'render' );
}

/** Canonical current-time readiness boundary used by all public Likes APIs. */
function cb_likes_runtime_ready(): bool {
	return \CB\Likes\Support\Requirements::runtime_ready()
		&& cb_likes_base_contracts_ready();
}

function cb_likes_dependency_message(): string {
	$issues = \CB\Likes\Support\Requirements::issues();
	if ( in_array( 'base-missing', $issues, true ) ) {
		return __( 'Core Blueprint Likes requires an active Core Blueprint Base plugin.', 'core-blueprint-likes' );
	}
	if ( in_array( 'base-api-incompatible', $issues, true ) ) {
		return sprintf(
			/* translators: 1: required Core API version, 2: available Core API version. */
			__( 'Core Blueprint Likes requires Core API %1$s or a newer compatible minor version. This site provides %2$s.', 'core-blueprint-likes' ),
			CB_LIKES_REQUIRED_API,
			defined( 'CB_CORE_API_VERSION' ) ? (string) CB_CORE_API_VERSION : 'none'
		);
	}
	return __( 'Core Blueprint Likes cannot access the required public Base contracts.', 'core-blueprint-likes' );
}

function cb_likes_fail_activation( string $message ): void {
	if ( ! function_exists( 'deactivate_plugins' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}
	deactivate_plugins( CB_LIKES_BASENAME );
	wp_die(
		esc_html( $message ),
		esc_html( 'Core Blueprint dependency required' ),
		[ 'back_link' => true ]
	);
}

function cb_likes_activate(): void {
	if ( ! \CB\Likes\Support\Requirements::runtime_ready() ) {
		cb_likes_fail_activation( cb_likes_dependency_message() );
	}
	if ( ! cb_likes_base_contracts_ready() ) {
		cb_likes_fail_activation( cb_likes_dependency_message() );
	}
	\CB\Likes\Install::activate();
}
register_activation_hook( __FILE__, 'cb_likes_activate' );

add_action( 'plugins_loaded', static function (): void {
	if ( ! \CB\Likes\Support\Requirements::runtime_ready() ) {
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

	if ( ! cb_likes_base_contracts_ready() ) {
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

	// Suite integrations attach only after Base/Core and Likes contracts are ready.
	\CB\Likes\Integration\Updates::init();

	\CB\Likes\Plugin::boot();
}, 30 );

/** Public API: return the number of likes for a target. */
function cb_likes_count( string $target_type, int $target_id ): int {
	if ( ! cb_likes_runtime_ready() ) {
		return 0;
	}
	return \CB\Likes\Repository::count( $target_type, $target_id );
}

/** Public API: determine whether a user likes a target. */
function cb_likes_user_has_liked( int $user_id, string $target_type, int $target_id ): bool {
	if ( ! cb_likes_runtime_ready() ) {
		return false;
	}
	return \CB\Likes\Repository::user_has_liked( $user_id, $target_type, $target_id );
}

/** Public API: set a user's like state for a valid target. */
function cb_likes_set_liked( int $user_id, string $target_type, int $target_id, bool $liked ): bool {
	if ( ! cb_likes_runtime_ready() ) {
		return false;
	}
	$result = \CB\Likes\Service::set_liked( $user_id, $target_type, $target_id, $liked );
	return ! is_wp_error( $result ) && (bool) $result['liked'];
}

/** Public API: return the number of dislikes for a target. */
function cb_likes_dislike_count( string $target_type, int $target_id ): int {
	if ( ! cb_likes_runtime_ready() ) {
		return 0;
	}
	return \CB\Likes\Repository::dislike_count( $target_type, $target_id );
}

/** Public API: determine whether a user dislikes a target. */
function cb_likes_user_has_disliked( int $user_id, string $target_type, int $target_id ): bool {
	if ( ! cb_likes_runtime_ready() ) {
		return false;
	}
	return \CB\Likes\Repository::user_has_disliked( $user_id, $target_type, $target_id );
}

/** Public API: set a user's dislike state for a valid target. */
function cb_likes_set_disliked( int $user_id, string $target_type, int $target_id, bool $disliked ): bool {
	if ( ! cb_likes_runtime_ready() ) {
		return false;
	}
	$result = \CB\Likes\Service::set_disliked( $user_id, $target_type, $target_id, $disliked );
	return ! is_wp_error( $result ) && (bool) $result['disliked'];
}

/** Public API: set one mutually exclusive reaction (`like`, `dislike` or null). */
function cb_likes_set_reaction( int $user_id, string $target_type, int $target_id, ?string $reaction ): array|\WP_Error {
	if ( ! cb_likes_runtime_ready() ) {
		return new \WP_Error( 'cb_likes_runtime_not_ready', cb_likes_dependency_message(), [ 'status' => 503 ] );
	}
	return \CB\Likes\Service::set_reaction( $user_id, $target_type, $target_id, $reaction );
}

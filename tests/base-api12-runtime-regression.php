<?php
declare(strict_types=1);

namespace CoreBlueprint\Core {
	final class ExtensionRegistry {
		public static array $registered = [];
		public static function register( array $value ): bool {
			self::$registered[] = $value;
			return true;
		}
	}
}
namespace CoreBlueprint\Core\Admin {
	final class SettingsRegistry {
		public const GROUP_COMMUNITY = 'community';
		public static array $registered = [];
		public static function register( string $id, array $definition ): bool {
			self::$registered[ $id ] = $definition;
			return true;
		}
		public static function url( string $id, array $query = [] ): string {
			return '/wp-admin/admin.php?page=core-blueprint-settings&extension=' . $id;
		}
	}
}
namespace CoreBlueprint\Core\UI {
	final class IntegrationGrid {
		public static function render( array $items ): string {
			return '';
		}
	}
	final class Icon {
		public static function render( string $name, array $args = [] ): string {
			return '';
		}
	}
}
namespace CoreBlueprint\Core\Dashboard {
	final class CardRegistry {
		public static array $shortcuts = [];
		public static function register_shortcut( string $id, array $shortcut ): bool {
			self::$shortcuts[ $id ] = $shortcut;
			return true;
		}
	}
}
namespace {
	define( 'ABSPATH', __DIR__ . '/wordpress/' );
	$GLOBALS['likes_hooks'] = [];
	function plugin_dir_path( string $path ): string { return dirname( $path ) . '/'; }
	function plugin_dir_url( string $path ): string { return 'https://example.test/plugins/core-blueprint-likes/'; }
	function plugin_basename( string $path ): string { return 'core-blueprint-likes/core-blueprint-likes.php'; }
	function register_activation_hook( string $file, callable $callback ): void {}
	function add_action( string $hook, callable $callback, int $priority = 10, int $accepted_args = 1 ): void {
		$GLOBALS['likes_hooks'][] = $hook;
	}
	function add_filter( string $hook, callable $callback, int $priority = 10, int $accepted_args = 1 ): void {
		$GLOBALS['likes_hooks'][] = $hook;
	}
	function __( string $value, string $domain = '' ): string { return $value; }
	class WP_Error {
		public function __construct( private string $code, private string $message = '', private array $data = [] ) {}
		public function get_error_code(): string { return $this->code; }
	}
	function check_likes( bool $ok, string $message ): void {
		if ( ! $ok ) {
			fwrite( STDERR, "FAIL: {$message}\n" );
			exit( 1 );
		}
	}

	require dirname( __DIR__ ) . '/core-blueprint-likes.php';

	check_likes( ! cb_likes_base_ready(), 'Missing Base API must be refused.' );
	check_likes( 0 === cb_likes_count( 'post', 123 ), 'Count API must fail closed.' );
	check_likes( 0 === cb_likes_dislike_count( 'post', 123 ), 'Dislike count must fail closed.' );
	check_likes( ! cb_likes_user_has_liked( 3, 'post', 123 ), 'Liked query must fail closed.' );
	check_likes( ! cb_likes_user_has_disliked( 3, 'post', 123 ), 'Disliked query must fail closed.' );
	check_likes( ! cb_likes_set_liked( 3, 'post', 123, true ), 'Like mutation must fail closed.' );
	check_likes( ! cb_likes_set_disliked( 3, 'post', 123, true ), 'Dislike mutation must fail closed.' );
	$result = cb_likes_set_reaction( 3, 'post', 123, 'like' );
	check_likes( $result instanceof WP_Error && 'cb_likes_base_unavailable' === $result->get_error_code(), 'Reaction API must return a dependency error.' );
	\CB\Likes\Plugin::boot();
	check_likes( ! in_array( 'admin_init', $GLOBALS['likes_hooks'], true ), 'Plugin boot must not mutate state without Base.' );

	check_likes( cb_likes_api_compatible( '1.2', '1.0' ), 'Current compatible API must be accepted.' );
	check_likes( ! cb_likes_api_compatible( '2.0', '1.0' ), 'New API major must be refused.' );
	check_likes( ! cb_likes_api_compatible( 'bogus', '1.0' ), 'Malformed API must be refused.' );
	define( 'CB_CORE_API_VERSION', '1.2' );
	check_likes( cb_likes_base_ready(), 'Modern public Base classes and API 1.2 must be accepted.' );

	\CB\Likes\Integration\CoreBlueprint::init();
	foreach ( [
		'core_blueprint_register_settings',
		'core_blueprint_register_extensions',
		'core_blueprint_module_status_definitions',
		'core_blueprint_dashboard_register_cards',
	] as $hook ) {
		check_likes( in_array( $hook, $GLOBALS['likes_hooks'], true ), 'Missing public lifecycle: ' . $hook );
	}
	check_likes( ! in_array( 'cb_core_register_settings', $GLOBALS['likes_hooks'], true ), 'Legacy lifecycle must not remain.' );

	\CB\Likes\Integration\CoreBlueprint::register_extension();
	check_likes( 'core-blueprint-likes' === (\CoreBlueprint\Core\ExtensionRegistry::$registered[0]['id'] ?? '' ), 'Extension identity must remain stable.' );
	\CB\Likes\Integration\CoreBlueprint::register_settings_provider();
	$definition = \CoreBlueprint\Core\Admin\SettingsRegistry::$registered['core-blueprint-likes'] ?? [];
	check_likes( 'community' === ( $definition['group'] ?? '' ), 'Community group must be retained.' );
	$components = $definition['requirements']['components'] ?? [];
	check_likes( in_array( 'buttons', $components, true ) && in_array( 'integration-grid', $components, true ), 'Public UI semantics must be requested.' );
	check_likes( ! in_array( 'actions', $components, true ), 'Base-private actions semantic must not be requested.' );

	echo "Likes Base 1.2 bootstrap and fail-closed public API regression: PASS\n";
}

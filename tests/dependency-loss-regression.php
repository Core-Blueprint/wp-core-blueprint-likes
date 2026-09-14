<?php
declare(strict_types=1);

define( 'ABSPATH', __DIR__ . '/' );

final class WP_Error {
	public function __construct( public string $code = '', public string $message = '', public mixed $data = null ) {}
}

final class CBLikesWpdbSpy {
	public int $accesses = 0;
	public string $posts = 'wp_posts';
	public function prepare( mixed ...$args ): string { ++$this->accesses; throw new RuntimeException( 'wpdb prepare reached while Base is unavailable' ); }
	public function get_var( mixed ...$args ): mixed { ++$this->accesses; throw new RuntimeException( 'wpdb read reached while Base is unavailable' ); }
	public function get_col( mixed ...$args ): array { ++$this->accesses; throw new RuntimeException( 'wpdb read reached while Base is unavailable' ); }
	public function get_results( mixed ...$args ): array { ++$this->accesses; throw new RuntimeException( 'wpdb read reached while Base is unavailable' ); }
	public function query( mixed ...$args ): mixed { ++$this->accesses; throw new RuntimeException( 'wpdb write reached while Base is unavailable' ); }
	public function delete( mixed ...$args ): mixed { ++$this->accesses; throw new RuntimeException( 'wpdb delete reached while Base is unavailable' ); }
}

$GLOBALS['cb_likes_test_hooks'] = [];
$GLOBALS['wpdb'] = new CBLikesWpdbSpy();

function plugin_dir_path( string $file ): string { return dirname( $file ) . '/'; }
function plugin_dir_url( string $file ): string { return 'https://example.test/wp-content/plugins/core-blueprint-likes/'; }
function plugin_basename( string $file ): string { return 'core-blueprint-likes/core-blueprint-likes.php'; }
function add_action( string $hook, callable $callback, int $priority = 10, int $accepted_args = 1 ): void { $GLOBALS['cb_likes_test_hooks'][] = [ $hook, $callback, $priority, $accepted_args ]; }
function add_filter( string $hook, callable $callback, int $priority = 10, int $accepted_args = 1 ): void { $GLOBALS['cb_likes_test_hooks'][] = [ $hook, $callback, $priority, $accepted_args ]; }
function register_activation_hook( string $file, callable|string $callback ): void {}
function register_setting( mixed ...$args ): void {}
function __( string $text, ?string $domain = null ): string { return $text; }
function is_wp_error( mixed $value ): bool { return $value instanceof WP_Error; }

require dirname( __DIR__ ) . '/core-blueprint-likes.php';

$expect = static function ( bool $condition, string $message ): void {
	if ( ! $condition ) {
		fwrite( STDERR, "FAIL: {$message}\n" );
		exit( 1 );
	}
};

$expect( ! cb_likes_runtime_ready(), 'Likes runtime must be unavailable without Base.' );
$expect( 0 === cb_likes_count( 'post', 10 ), 'Public count must fail closed.' );
$expect( false === cb_likes_user_has_liked( 1, 'post', 10 ), 'Public liked state must fail closed.' );
$expect( false === cb_likes_set_liked( 1, 'post', 10, true ), 'Public like mutation must fail closed.' );
$expect( 0 === cb_likes_dislike_count( 'post', 10 ), 'Public dislike count must fail closed.' );
$expect( false === cb_likes_user_has_disliked( 1, 'post', 10 ), 'Public disliked state must fail closed.' );
$expect( false === cb_likes_set_disliked( 1, 'post', 10, true ), 'Public dislike mutation must fail closed.' );
$expect( cb_likes_set_reaction( 1, 'post', 10, 'like' ) instanceof WP_Error, 'Public reaction mutation must return a fail-closed error.' );

$expect( \CB\Likes\Service::set_reaction( 1, 'post', 10, 'like' ) instanceof WP_Error, 'Direct Service mutation must fail closed.' );
$expect( \CB\Likes\Service::set_liked( 1, 'post', 10, true ) instanceof WP_Error, 'Direct Service like mutation must fail closed.' );
$expect( \CB\Likes\Service::set_disliked( 1, 'post', 10, true ) instanceof WP_Error, 'Direct Service dislike mutation must fail closed.' );
$expect( false === \CB\Likes\Repository::set_reaction( 1, 'post', 10, 'like' ), 'Direct Repository write must fail closed.' );
$expect( false === \CB\Likes\Repository::clear_reaction( 1, 'post', 10 ), 'Direct Repository delete must fail closed.' );
$expect( 0 === \CB\Likes\Repository::count( 'post', 10 ), 'Direct Repository read must fail closed.' );
$expect( [] === \CB\Likes\Repository::rows_for_user( 1 ), 'Direct Repository export read must fail closed.' );
$expect( 0 === \CB\Likes\Repository::delete_by_user( 1 ), 'Direct privacy/user delete must fail closed.' );
\CB\Likes\Repository::delete_target( 'post', 10 );

$before = count( $GLOBALS['cb_likes_test_hooks'] );
\CB\Likes\Plugin::boot();
$expect( $before === count( $GLOBALS['cb_likes_test_hooks'] ), 'Direct Plugin::boot() must remain inert without Base.' );
$expect( 0 === $GLOBALS['wpdb']->accesses, 'No database read or mutation may escape the readiness gate.' );

echo "Likes dependency-loss regression PASS\n";

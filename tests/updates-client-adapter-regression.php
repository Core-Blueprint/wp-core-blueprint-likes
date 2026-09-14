<?php
declare(strict_types=1);

$root = (string) file_get_contents( dirname( __DIR__ ) . '/core-blueprint-likes.php' );
$adapter = (string) file_get_contents( dirname( __DIR__ ) . '/src/Integration/Updates.php' );

function cb_likes_updates_expect( bool $condition, string $message ): void {
	if ( $condition ) {
		return;
	}
	fwrite( STDERR, "FAIL: {$message}\n" );
	exit( 1 );
}

cb_likes_updates_expect( str_contains( $root, 'Update URI:        https://coreblueprint.io/' ), 'Canonical Update URI header must be declared.' );
cb_likes_updates_expect( str_contains( $root, '\\CB\\Likes\\Integration\\Updates::init();' ), 'Updates adapter must attach from the canonical runtime boundary.' );
cb_likes_updates_expect( str_contains( $adapter, "[ self::class, 'register_product' ]" ), 'Updates hook must use the canonical parameterless callback.' );
cb_likes_updates_expect( str_contains( $adapter, "'\\\\CB\\\\Updates\\\\ProductRegistry'" ), 'Adapter must target the central ProductRegistry.' );
cb_likes_updates_expect( str_contains( $adapter, '$registry::register( [' ), 'Adapter must call the static ProductRegistry contract.' );
cb_likes_updates_expect( str_contains( $adapter, "'core-blueprint-likes'" ), 'Canonical Likes product key must be registered.' );
cb_likes_updates_expect( str_contains( $adapter, "'core-blueprint'" ), 'Canonical vendor id must be registered.' );
cb_likes_updates_expect( str_contains( $adapter, 'CB_LIKES_BASENAME' ), 'Adapter must advertise the actual plugin basename.' );
cb_likes_updates_expect( str_contains( $adapter, 'CB_LIKES_VERSION' ), 'Adapter must advertise the installed version.' );
cb_likes_updates_expect( str_contains( $adapter, "'software_uuid' => ''" ), 'Marketplace UUID must remain learnable rather than hardcoded.' );

$plugins_loaded = strpos( $root, "add_action( 'plugins_loaded'" );
$runtime_gate = false === $plugins_loaded ? false : strpos( $root, '\\CB\\Likes\\Support\\Requirements::runtime_ready()', $plugins_loaded );
$contracts_gate = false === $plugins_loaded ? false : strpos( $root, 'if ( ! cb_likes_base_contracts_ready() )', $plugins_loaded );
$updates_init = false === $plugins_loaded ? false : strpos( $root, '\\CB\\Likes\\Integration\\Updates::init();', $plugins_loaded );
$feature_boot = false === $plugins_loaded ? false : strpos( $root, '\\CB\\Likes\\Plugin::boot();', $plugins_loaded );
cb_likes_updates_expect(
	false !== $runtime_gate && false !== $contracts_gate && false !== $updates_init && false !== $feature_boot
		&& $runtime_gate < $contracts_gate && $contracts_gate < $updates_init && $updates_init < $feature_boot,
	'Bootstrap order must be readiness -> product contracts -> Updates integration -> feature runtime.'
);
cb_likes_updates_expect( 1 === substr_count( $root, '\\CB\\Likes\\Integration\\Updates::init();' ), 'Updates adapter must attach exactly once.' );

echo "Likes Updates adapter regression PASS\n";

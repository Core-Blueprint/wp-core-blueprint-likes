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
cb_likes_updates_expect( str_contains( $root, '\\CB\\Likes\\Integration\\Updates::init();' ), 'Updates adapter must attach before Likes runtime gate.' );
cb_likes_updates_expect( str_contains( $adapter, "[ self::class, 'register_product' ]" ), 'Updates hook must use the canonical parameterless callback.' );
cb_likes_updates_expect( str_contains( $adapter, "'\\\\CB\\\\Updates\\\\ProductRegistry'" ), 'Adapter must target the central ProductRegistry.' );
cb_likes_updates_expect( str_contains( $adapter, '$registry::register( [' ), 'Adapter must call the static ProductRegistry contract.' );
cb_likes_updates_expect( str_contains( $adapter, "'core-blueprint-likes'" ), 'Canonical Likes product key must be registered.' );
cb_likes_updates_expect( str_contains( $adapter, "'core-blueprint'" ), 'Canonical vendor id must be registered.' );
cb_likes_updates_expect( str_contains( $adapter, 'CB_LIKES_BASENAME' ), 'Adapter must advertise the actual plugin basename.' );
cb_likes_updates_expect( str_contains( $adapter, 'CB_LIKES_VERSION' ), 'Adapter must advertise the installed version.' );
cb_likes_updates_expect( str_contains( $adapter, "'software_uuid' => ''" ), 'Marketplace UUID must remain learnable rather than hardcoded.' );

echo "Likes Updates adapter regression PASS\n";

<?php
declare(strict_types=1);

$root = dirname( __DIR__ );
$bootstrap = (string) file_get_contents( $root . '/core-blueprint-likes.php' );
$readme    = (string) file_get_contents( $root . '/readme.txt' );

if ( ! preg_match( '/^ \\* Requires Plugins:\\s+core-blueprint\\s*$/m', $bootstrap ) ) {
	fwrite( STDERR, "FAIL: Likes WordPress.org release must declare Requires Plugins: core-blueprint.\n" );
	exit( 1 );
}
if ( ! str_starts_with( $readme, '=== Core Blueprint Likes ===' ) ) {
	fwrite( STDERR, "FAIL: Likes WordPress.org readme.txt is missing or invalid.\n" );
	exit( 1 );
}

$tests = glob( dirname( __DIR__ ) . '/tests/*-regression.php' ) ?: [];
sort( $tests );
if ( [] === $tests ) {
	fwrite( STDERR, "No regression tests found.\n" );
	exit( 1 );
}

try {
	foreach ( $tests as $test ) {
		require $test;
	}
} catch ( Throwable $error ) {
	fwrite( STDERR, 'FAIL: ' . $error->getMessage() . "\n" );
	exit( 1 );
}

echo "Likes conformance PASS\n";

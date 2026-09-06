<?php
declare(strict_types=1);

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

echo "conformance: PASS\n";

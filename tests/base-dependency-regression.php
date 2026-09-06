<?php
declare(strict_types=1);

$main = file_get_contents( dirname( __DIR__ ) . '/core-blueprint-likes.php' );
if ( false === $main ) {
	fwrite( STDERR, "Unable to read Likes bootstrap.\n" );
	exit( 1 );
}

// Normalize escaped namespace separators as they appear inside PHP string literals
// so the regression verifies the contract names rather than source escaping syntax.
$normalized = str_replace( '\\\\', '\\', $main );

$checks = [
	'public version stays RC1' => str_contains( $main, 'Version:           1.0.0-rc1' ) && str_contains( $main, "define( 'CB_LIKES_VERSION', '1.0.0-rc1' );" ),
	'Core API 1.0 contract' => str_contains( $main, "define( 'CB_LIKES_REQUIRED_API', '1.0' );" ),
	'ExtensionRegistry required' => str_contains( $normalized, 'CB\\Core\\ExtensionRegistry' ),
	'SettingsRegistry required' => str_contains( $normalized, 'CB\\Core\\Admin\\SettingsRegistry' ),
	'PageRegistry no longer required' => ! str_contains( $normalized, 'CB\\Core\\Admin\\PageRegistry' ),
	'public Page contract no longer required' => ! str_contains( $normalized, 'CB\\Core\\Admin\\Page' ),
	'IntegrationGrid required' => str_contains( $normalized, 'CB\\Core\\UI\\IntegrationGrid' ),
	'Icon renderer required' => str_contains( $normalized, 'CB\\Core\\UI\\Icon' ),
	'no Base product-version gate' => ! str_contains( $main, 'CB_CORE_VERSION' ),
	'no WordPress Requires Plugins header' => ! str_contains( $main, 'Requires Plugins:' ),
];

$failed = array_keys( array_filter( $checks, static fn( bool $passed ): bool => ! $passed ) );
if ( [] !== $failed ) {
	fwrite( STDERR, "Likes Base dependency regression failed:\n- " . implode( "\n- ", $failed ) . "\n" );
	exit( 1 );
}

echo "Likes Base dependency regression PASS\n";

<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

$main = cb_likes_source( 'core-blueprint-likes.php' );
cb_likes_assert_contains( "Version:           1.0.0-rc1", $main, 'Public plugin version must remain 1.0.0-rc1.' );
cb_likes_assert_contains( "define( 'CB_LIKES_VERSION', '1.0.0-rc1' );", $main, 'Runtime version must remain 1.0.0-rc1.' );
cb_likes_assert_contains( "define( 'CB_LIKES_REQUIRED_API', '1.0' );", $main, 'Likes must target Core API 1.0.' );
foreach ( [
	'CB\\Core\\ExtensionRegistry',
	'CB\\Core\\Admin\\PageRegistry',
	'CB\\Core\\Admin\\Page',
	'CB\\Core\\UI\\IntegrationGrid',
	'CB\\Core\\UI\\Icon',
] as $contract ) {
	cb_likes_assert_contains( $contract, $main, 'Missing required public Base contract: ' . $contract );
}
cb_likes_assert_not_contains( 'CB_CORE_VERSION', $main, 'Runtime compatibility must not use the Base product version.' );
cb_likes_assert_not_contains( 'Requires Plugins:', $main, 'Likes must not declare a WordPress Requires Plugins header.' );

echo "base-dependency-regression: PASS\n";

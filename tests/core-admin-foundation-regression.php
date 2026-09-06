<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

$page = cb_likes_source( 'src/Admin/PageContent.php' );
$tabs = [ 'overview', 'general', 'post-types', 'user-profiles', 'integrations' ];
$last = -1;
foreach ( $tabs as $tab ) {
	$position = strpos( $page, 'data-cb-likes-tab="' . $tab . '"' );
	cb_likes_assert( false !== $position, 'Missing Golden Admin tab: ' . $tab );
	cb_likes_assert( $position > $last, 'Golden Admin tab order is incorrect at: ' . $tab );
	$last = $position;
}
cb_likes_assert( 1 === substr_count( $page, '<form method="post" action="options.php"' ), 'Likes settings must use one scope-safe form.' );
foreach ( [ 'GeneralSettings::render', 'PostTypeSettings::render', 'UserProfileSettings::render' ] as $renderer ) {
	cb_likes_assert_contains( $renderer, $page, 'Settings form missing renderer: ' . $renderer );
}
cb_likes_assert_contains( 'IntegrationGrid::render', $page, 'Integrations must use the public Base Integration Grid.' );
cb_likes_assert_not_contains( 'cb-likes-tab-usage', $page, 'Legacy Usage settings tab must not remain.' );

$integration = cb_likes_source( 'src/Integration/CoreBlueprint.php' );
foreach ( [ 'cards', 'metric-tiles', 'nav-tabs', 'fields', 'disclosure', 'form-controls', 'integration-grid' ] as $component ) {
	cb_likes_assert_contains( "'" . $component . "'", $integration, 'Missing semantic Base component: ' . $component );
}
cb_likes_assert_contains( "'clipboard'", $integration, 'Clipboard Foundation must be requested semantically.' );

$page_contract = cb_likes_source( 'src/Admin/CoreBlueprintPage.php' );
cb_likes_assert_not_contains( 'PageBase', $page_contract, 'Likes must implement the public Page contract, not internal PageBase.' );

$assets = cb_likes_source( 'src/Admin/Assets.php' );
cb_likes_assert_not_contains( '@cb-core/clipboard', $assets, 'Likes must not depend on a private Base asset handle.' );
cb_likes_assert_not_contains( 'wp_enqueue_script_module', $assets, 'Likes must not register a private clipboard module dependency.' );

$css = cb_likes_source( 'assets/css/admin.css' );
cb_likes_assert_not_contains( 'cb-likes-native', $css, 'Obsolete standalone Base-missing presentation must be removed.' );
cb_likes_assert_not_contains( 'DetailRows', $page, 'Editable Likes settings must not be forced into DetailRows.' );

echo "core-admin-foundation-regression: PASS\n";

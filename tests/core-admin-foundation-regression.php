<?php
declare(strict_types=1);

$root = dirname( __DIR__ );

$read = static function ( string $relative ) use ( $root ): string {
	$source = file_get_contents( $root . '/' . $relative );
	if ( false === $source ) {
		throw new RuntimeException( 'Unable to read ' . $relative );
	}
	return $source;
};

$page          = $read( 'src/Admin/PageContent.php' );
$integration   = $read( 'src/Integration/CoreBlueprint.php' );
$page_contract = $read( 'src/Admin/CoreBlueprintPage.php' );
$assets        = $read( 'src/Admin/Assets.php' );
$css           = $read( 'assets/css/admin.css' );
$bootstrap     = $read( 'core-blueprint-likes.php' );

$checks = [];
$tabs = [ 'overview', 'general', 'post-types', 'user-profiles', 'integrations' ];
$last = -1;
foreach ( $tabs as $tab ) {
	$position = strpos( $page, 'data-cb-likes-tab="' . $tab . '"' );
	$checks['tab exists: ' . $tab] = false !== $position;
	$checks['tab order: ' . $tab] = false !== $position && $position > $last;
	if ( false !== $position ) {
		$last = $position;
	}
}

$checks += [
	'one scope-safe settings form' => 1 === substr_count( $page, '<form method="post" action="options.php"' ),
	'General renderer wired' => str_contains( $page, 'GeneralSettings::render' ),
	'Post types renderer wired' => str_contains( $page, 'PostTypeSettings::render' ),
	'User profiles renderer wired' => str_contains( $page, 'UserProfileSettings::render' ),
	'public Integration Grid used' => str_contains( $page, 'IntegrationGrid::render' ),
	'legacy Usage tab removed' => ! str_contains( $page, 'cb-likes-tab-usage' ),
	'public Page contract retained' => str_contains( $page_contract, 'implements PageContract' ) && ! str_contains( $page_contract, 'PageBase' ),
	'Clipboard requested semantically' => str_contains( $integration, "'foundations' => [ 'clipboard' ]" ),
	'Integration Grid requested semantically' => str_contains( $integration, "'integration-grid'" ),
	'Metric tiles requested semantically' => str_contains( $integration, "'metric-tiles'" ),
	'no private Clipboard handle' => ! str_contains( $assets, '@cb-core/clipboard' ) && ! str_contains( $assets, 'wp_enqueue_script_module' ),
	'no standalone admin fallback CSS' => ! str_contains( $css, 'cb-likes-native' ),
	'no DetailRows misuse' => ! str_contains( $page, 'DetailRows' ),
	'IntegrationGrid runtime contract gated' => str_contains( $bootstrap, "class_exists( '\\\\CB\\\\Core\\\\UI\\\\IntegrationGrid' )" ) && str_contains( $bootstrap, "method_exists( '\\\\CB\\\\Core\\\\UI\\\\IntegrationGrid', 'render' )" ),
	'Icon runtime contract gated' => str_contains( $bootstrap, "class_exists( '\\\\CB\\\\Core\\\\UI\\\\Icon' )" ) && str_contains( $bootstrap, "method_exists( '\\\\CB\\\\Core\\\\UI\\\\Icon', 'render' )" ),
	'RC1 version retained' => str_contains( $bootstrap, 'Version:           1.0.0-rc1' ) && str_contains( $bootstrap, "define( 'CB_LIKES_VERSION', '1.0.0-rc1' )" ),
];

$failed = array_keys( array_filter( $checks, static fn( bool $passed ): bool => ! $passed ) );
if ( [] !== $failed ) {
	fwrite( STDERR, "Likes Core Admin Foundation regression failed:\n- " . implode( "\n- ", $failed ) . "\n" );
	exit( 1 );
}

echo "Likes Core Admin Foundation regression PASS\n";

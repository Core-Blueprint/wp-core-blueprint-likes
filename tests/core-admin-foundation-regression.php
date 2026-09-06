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

$page        = $read( 'src/Admin/PageContent.php' );
$integration = $read( 'src/Integration/CoreBlueprint.php' );
$post_types  = $read( 'src/Admin/PostTypeSettings.php' );
$fields      = $read( 'src/Admin/Fields.php' );
$assets      = $read( 'src/Admin/Assets.php' );
$js          = $read( 'assets/js/admin.js' );
$css         = $read( 'assets/css/admin.css' );
$bootstrap   = $read( 'core-blueprint-likes.php' );

$checks = [];
$tabs = [
	"self::TAB_OVERVIEW      => __( 'Overview'",
	"self::TAB_GENERAL       => __( 'General'",
	"self::TAB_POST_TYPES    => __( 'Post types'",
	"self::TAB_USER_PROFILES => __( 'User profiles'",
	"self::TAB_INTEGRATIONS  => __( 'Integrations'",
];
$last = -1;
foreach ( $tabs as $tab ) {
	$position = strpos( $page, $tab );
	$checks['tab exists: ' . $tab] = false !== $position;
	$checks['tab order: ' . $tab] = false !== $position && $position > $last;
	if ( false !== $position ) {
		$last = $position;
	}
}

$checks += [
	'canonical extension id exposed' => str_contains( $integration, "public const ID = 'core-blueprint-likes';" ),
	'Settings Hub lifecycle registered' => str_contains( $integration, "add_action( 'cb_core_register_settings'" ) && str_contains( $integration, 'SettingsRegistry::register(' ),
	'Community group registered' => str_contains( $integration, 'SettingsRegistry::GROUP_COMMUNITY' ),
	'PageContent remains provider renderer' => str_contains( $integration, "'renderer'    => [ PageContent::class, 'render' ]" ),
	'manage capability retained' => str_contains( $integration, "'capability'  => Capabilities::MANAGE" ),
	'old PageRegistry lifecycle removed' => ! str_contains( $integration, 'cb_core_register_pages' ) && ! str_contains( $integration, 'PageRegistry::register' ),
	'obsolete page shell removed' => ! is_file( $root . '/src/Admin/CoreBlueprintPage.php' ),
	'server-side tab allowlist' => str_contains( $page, 'private static function current_tab()' ) && str_contains( $page, 'array_key_exists( $tab, self::tabs() )' ),
	'canonical Settings Hub tab URLs' => str_contains( $page, "SettingsRegistry::url( CoreBlueprint::ID, [ 'tab' => \$tab ] )" ),
	'anchor tab navigation' => str_contains( $page, '<a class="nav-tab ' ) && str_contains( $page, 'aria-current="page"' ),
	'active tab rendered server-side' => str_contains( $page, 'switch ( $tab )' ),
	'no duplicate Base settings shell' => ! str_contains( $page, 'class="wrap cb-core-wrap' ) && ! str_contains( $page, 'cb-core-title' ) && ! str_contains( $page, 'cb-core-intro' ),
	'no client-side tab router markup' => ! str_contains( $page, 'data-cb-likes-tab' ) && ! str_contains( $page, 'data-cb-likes-tab-panel' ),
	'no client-side tab session state' => ! str_contains( $js, 'sessionStorage' ) && ! str_contains( $js, 'cb-likes-active-tab' ) && ! str_contains( $js, 'data-cb-likes-tab' ),
	'one scope-safe settings form' => 1 === substr_count( $page, '<form method="post" action="options.php"' ),
	'General renderer wired' => str_contains( $page, 'GeneralSettings::render' ),
	'Post types renderer wired' => str_contains( $page, 'PostTypeSettings::render' ),
	'User profiles renderer wired' => str_contains( $page, 'UserProfileSettings::render' ),
	'public Integration Grid used' => str_contains( $page, 'IntegrationGrid::render' ),
	'legacy Usage tab removed' => ! str_contains( $page, 'cb-likes-tab-usage' ),
	'Clipboard requested semantically' => str_contains( $integration, "'foundations' => [ 'clipboard' ]" ),
	'Integration Grid requested semantically' => str_contains( $integration, "'integration-grid'" ),
	'Metric tiles requested semantically' => str_contains( $integration, "'metric-tiles'" ),
	'Actions requested semantically' => str_contains( $integration, "'actions'" ),
	'Status requested semantically' => str_contains( $integration, "'status'" ),
	'provider assets keyed by extension identity' => str_contains( $assets, "\$_GET['extension']" ) && str_contains( $assets, 'CoreBlueprint::ID !== $extension_id' ),
	'Foundation interactive rows used' => str_contains( $post_types, '<details class="cb-core-interactive-row cb-likes-post-type' ) && str_contains( $post_types, '<summary class="cb-core-interactive-row__summary cb-likes-post-type__summary">' ),
	'Foundation status indicators used' => str_contains( $post_types, 'cb-core-status cb-likes-post-type__state-enabled' ) && str_contains( $post_types, 'cb-core-status__dot cb-core-status__dot--muted' ),
	'Foundation native checkbox retained' => str_contains( $post_types, 'type="checkbox"' ) && str_contains( $post_types, 'data-cb-likes-post-type-toggle' ),
	'Foundation action row used' => str_contains( $post_types, 'class="cb-core-actions"' ) && str_contains( $post_types, 'cb-core-button cb-core-button--primary' ),
	'Foundation disclosure icon semantics used' => str_contains( $fields, "Icon::render( 'expand'" ) && str_contains( $fields, 'cb-core-interactive-row__icon' ),
	'no pseudo Foundation rack toggle' => ! str_contains( $post_types, 'cb-core-rack-toggle' ) && ! str_contains( $css, 'cb-core-rack-toggle' ),
	'no pseudo Foundation module collapse' => ! str_contains( $post_types, 'cb-core-module-collapse' ) && ! str_contains( $css, 'cb-core-module-collapse' ),
	'native details owns disclosure state' => ! str_contains( $js, 'setExpanded' ) && ! str_contains( $js, 'data-cb-likes-post-type-collapse' ),
	'no private Clipboard handle' => ! str_contains( $assets, '@cb-core/clipboard' ) && ! str_contains( $assets, 'wp_enqueue_script_module' ),
	'no standalone admin fallback CSS' => ! str_contains( $css, 'cb-likes-native' ),
	'no DetailRows misuse' => ! str_contains( $page, 'DetailRows' ),
	'SettingsRegistry runtime contract gated' => str_contains( $bootstrap, "class_exists( '\\\\CB\\\\Core\\\\Admin\\\\SettingsRegistry' )" ),
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

<?php
declare(strict_types=1);

$root = dirname( __DIR__ );

$plugin       = file_get_contents( $root . '/src/Plugin.php' );
$integration  = file_get_contents( $root . '/src/Integration/Bricks/Integration.php' );
$registry     = file_get_contents( $root . '/src/Integration/Bricks/ElementRegistry.php' );
$element      = file_get_contents( $root . '/src/Integration/Bricks/Elements/Element.php' );
$like         = file_get_contents( $root . '/src/Integration/Bricks/Elements/Like.php' );
$dislike      = file_get_contents( $root . '/src/Integration/Bricks/Elements/Dislike.php' );
$data         = file_get_contents( $root . '/src/Builder/Data.php' );
$conditions   = file_get_contents( $root . '/src/Builder/Conditions.php' );
$queries      = file_get_contents( $root . '/src/Builder/Queries.php' );
$context      = file_get_contents( $root . '/src/Builder/Context.php' );
$components   = file_get_contents( $root . '/src/Builder/Components.php' );
$targets      = file_get_contents( $root . '/src/Builder/Targets.php' );
$renderer     = file_get_contents( $root . '/src/Frontend/Renderer.php' );

if ( in_array( false, [ $plugin, $integration, $registry, $element, $like, $dislike, $data, $conditions, $queries, $context, $components, $targets, $renderer ], true ) ) {
	fwrite( STDERR, "Unable to read Likes builder boundary sources.\n" );
	exit( 1 );
}

$builder_sources = $data . "\n" . $conditions . "\n" . $queries . "\n" . $context . "\n" . $components . "\n" . $targets;
$bricks_sources  = $integration . "\n" . $registry . "\n" . $element . "\n" . $like . "\n" . $dislike;

$checks = [
	'canonical Bricks adapter namespace' => str_contains( $plugin, 'Integration\\Bricks\\Integration as BricksIntegration' ),
	'Bricks consumes neutral data' => str_contains( $integration, 'Builder\\Data' ) && str_contains( $integration, 'Data::value' ),
	'Bricks consumes neutral conditions' => str_contains( $integration, 'Builder\\Conditions' ) && str_contains( $integration, 'Conditions::current_user_has_' ),
	'Bricks consumes neutral queries' => str_contains( $integration, 'Builder\\Queries' ) && str_contains( $integration, 'Queries::liked_posts' ) && str_contains( $integration, 'Queries::most_liked_posts' ),
	'Bricks does not import Repository' => ! str_contains( $bricks_sources, 'use CB\\Likes\\Repository;' ),
	'Bricks does not import Settings' => ! str_contains( $bricks_sources, 'use CB\\Likes\\Settings;' ),
	'Bricks does not import domain Targets' => ! str_contains( $bricks_sources, 'use CB\\Likes\\Targets;' ),
	'Bricks has no direct storage mutation' => ! preg_match( '/\b(?:set_reaction|clear_reaction|delete_target|delete_by_user)\s*\(/', $bricks_sources ),
	'builder contracts have no Bricks dependency' => ! str_contains( $builder_sources, 'Bricks\\' ) && ! str_contains( $builder_sources, 'bricks/' ),
	'dynamic tag ids preserved' => str_contains( $integration, 'cb_likes_count' ) && str_contains( $integration, 'cb_likes_dislike_count' ) && str_contains( $integration, 'cb_likes_has_liked' ) && str_contains( $integration, 'cb_likes_has_disliked' ),
	'condition ids preserved' => str_contains( $integration, 'cb_likes_has_liked' ) && str_contains( $integration, 'cb_likes_has_disliked' ),
	'query loop ids preserved' => str_contains( $integration, 'cb_likes_liked_posts' ) && str_contains( $integration, 'cb_likes_most_liked' ),
	'optional Bricks element guard' => str_contains( $registry, "class_exists( '\\\\Bricks\\\\Elements' )" ) && str_contains( $registry, "class_exists( '\\\\Bricks\\\\Element' )" ),
	'exactly two interaction elements' => 2 === substr_count( $registry, "'name' => 'cb-likes-" ),
	'Like delegates to neutral component' => str_contains( $like, 'Components::like_button' ),
	'Dislike delegates to neutral component' => str_contains( $dislike, 'Components::dislike_button' ),
	'human-readable manual targets' => str_contains( $element, "'searchable' => true" ) && str_contains( $targets, 'Specific target' ),
	'current context is default' => str_contains( $element, "'default' => 'current'" ) && str_contains( $element, 'Context::target()' ),
	'Dislike manual targets exclude users' => str_contains( $dislike, 'reaction_controls( false )' ) && str_contains( $dislike, 'target( false )' ),
	'Like manual targets include users' => str_contains( $like, 'reaction_controls( true )' ) && str_contains( $like, 'target( true )' ),
	'shared renderer entries exist' => str_contains( $renderer, 'function like_button' ) && str_contains( $renderer, 'function dislike_button' ),
	'neutral components reuse renderer entries' => str_contains( $components, 'Renderer::like_button' ) && str_contains( $components, 'Renderer::dislike_button' ),
];

$failed = [];
foreach ( $checks as $label => $passed ) {
	if ( ! $passed ) {
		$failed[] = $label;
	}
}

if ( [] !== $failed ) {
	fwrite( STDERR, "Likes builder boundary regression failed:\n- " . implode( "\n- ", $failed ) . "\n" );
	exit( 1 );
}

echo "Likes builder boundary regression PASS\n";

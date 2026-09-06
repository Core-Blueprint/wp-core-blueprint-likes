<?php
declare(strict_types=1);

$root = dirname( __DIR__ );

$integration = file_get_contents( $root . '/src/Bricks/Integration.php' );
$data        = file_get_contents( $root . '/src/Builder/Data.php' );
$conditions  = file_get_contents( $root . '/src/Builder/Conditions.php' );
$queries     = file_get_contents( $root . '/src/Builder/Queries.php' );
$context     = file_get_contents( $root . '/src/Builder/Context.php' );

if ( in_array( false, [ $integration, $data, $conditions, $queries, $context ], true ) ) {
	fwrite( STDERR, "Unable to read Likes builder boundary sources.\n" );
	exit( 1 );
}

$builder_sources = $data . "\n" . $conditions . "\n" . $queries . "\n" . $context;

$checks = [
	'Bricks consumes neutral data' => str_contains( $integration, 'Builder\\Data' ) && str_contains( $integration, 'Data::value' ),
	'Bricks consumes neutral conditions' => str_contains( $integration, 'Builder\\Conditions' ) && str_contains( $integration, 'Conditions::current_user_has_' ),
	'Bricks consumes neutral queries' => str_contains( $integration, 'Builder\\Queries' ) && str_contains( $integration, 'Queries::liked_posts' ) && str_contains( $integration, 'Queries::most_liked_posts' ),
	'Bricks does not import Repository' => ! str_contains( $integration, 'use CB\\Likes\\Repository;' ),
	'Bricks does not import Settings' => ! str_contains( $integration, 'use CB\\Likes\\Settings;' ),
	'Bricks does not import Targets' => ! str_contains( $integration, 'use CB\\Likes\\Targets;' ),
	'Bricks has no direct storage mutation' => ! preg_match( '/\b(?:set_reaction|clear_reaction|add|remove|delete_target|delete_by_user)\s*\(/', $integration ),
	'builder contracts have no Bricks dependency' => ! str_contains( $builder_sources, 'Bricks\\' ) && ! str_contains( $builder_sources, 'bricks/' ),
	'dynamic tag ids preserved' => str_contains( $integration, 'cb_likes_count' ) && str_contains( $integration, 'cb_likes_dislike_count' ) && str_contains( $integration, 'cb_likes_has_liked' ) && str_contains( $integration, 'cb_likes_has_disliked' ),
	'condition ids preserved' => str_contains( $integration, 'cb_likes_has_liked' ) && str_contains( $integration, 'cb_likes_has_disliked' ),
	'query loop ids preserved' => str_contains( $integration, 'cb_likes_liked_posts' ) && str_contains( $integration, 'cb_likes_most_liked' ),
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

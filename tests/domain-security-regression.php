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

$service    = $read( 'src/Service.php' );
$rest       = $read( 'src/Rest/Controller.php' );
$install    = strtolower( $read( 'src/Install.php' ) );
$main       = $read( 'core-blueprint-likes.php' );
$plugin     = $read( 'src/Plugin.php' );
$renderer   = $read( 'src/Frontend/Renderer.php' );
$repository = $read( 'src/Repository.php' );
$targets    = $read( 'src/Targets.php' );

$checks = [
	'reaction mutations enforce authorization' => str_contains( $service, 'Targets::user_can_like' ),
	'dislikes enforce target configuration' => str_contains( $service, 'Settings::dislike_enabled_for_target' ),
	'reaction persistence is centralized' => str_contains( $service, 'Repository::set_reaction' ),
	'Service mutations re-check runtime readiness' => substr_count( $service, 'if ( ! self::runtime_ready() )' ) >= 3,
	'Repository access is runtime gated' => substr_count( $repository, 'self::runtime_ready()' ) >= 10,
	'Plugin boot re-checks runtime readiness' => str_contains( $plugin, "! \\cb_likes_runtime_ready()" ),
	'public helpers re-check runtime readiness' => substr_count( $main, 'if ( ! cb_likes_runtime_ready() )' ) >= 7,
	'REST requires authentication' => str_contains( $rest, 'is_user_logged_in' ),
	'REST delegates mutations to Service' => str_contains( $rest, 'Service::set_reaction' ),
	'one reaction per user target' => str_contains( $install, 'unique key user_target' ),
	'user self-like blocked' => str_contains( $targets, '$user_id === $target_id' ),
	'user visibility extension point retained' => str_contains( $targets, 'cb_likes_user_can_view_target' ),
	'user participation extension point retained' => str_contains( $targets, 'cb_likes_user_can_like_target' ),
	'pre-v1 Repository aliases removed' => ! str_contains( $repository, 'function add(' ) && ! str_contains( $repository, 'function remove(' ),
	'legacy liked_label shortcode alias removed' => ! str_contains( $renderer, "'liked_label'  =>" ) && ! str_contains( $renderer, '$legacy_active' ),
];

foreach ( [ 'ip_address', 'fingerprint', 'device_id', 'geolocation', 'latitude', 'longitude' ] as $forbidden ) {
	$checks['no forbidden storage field: ' . $forbidden] = ! str_contains( $install, $forbidden );
}

foreach ( [
	'cb_likes_count', 'cb_likes_user_has_liked', 'cb_likes_set_liked',
	'cb_likes_dislike_count', 'cb_likes_user_has_disliked', 'cb_likes_set_disliked', 'cb_likes_set_reaction',
] as $function ) {
	$checks['public helper retained: ' . $function] = str_contains( $main, 'function ' . $function . '(' );
}

foreach ( [ 'cb_like_button', 'cb_like_count', 'cb_dislike_button', 'cb_dislike_count' ] as $shortcode ) {
	$checks['public shortcode retained: ' . $shortcode] = str_contains( $renderer, "'" . $shortcode . "'" );
}

$failed = array_keys( array_filter( $checks, static fn( bool $passed ): bool => ! $passed ) );
if ( [] !== $failed ) {
	fwrite( STDERR, "Likes domain/security regression failed:\n- " . implode( "\n- ", $failed ) . "\n" );
	exit( 1 );
}

echo "Likes domain/security regression PASS\n";

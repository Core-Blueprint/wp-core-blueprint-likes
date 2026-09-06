<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

$service = cb_likes_source( 'src/Service.php' );
cb_likes_assert_contains( 'Targets::user_can_like', $service, 'Reaction mutations must enforce target authorization.' );
cb_likes_assert_contains( 'Settings::dislike_enabled_for_target', $service, 'Dislike mutations must enforce target configuration.' );
cb_likes_assert_contains( 'Repository::set_reaction', $service, 'Mutual-exclusive reaction persistence must stay centralized.' );

$rest = cb_likes_source( 'src/Rest/Controller.php' );
cb_likes_assert_contains( 'is_user_logged_in', $rest, 'REST mutation permission must require authentication.' );
cb_likes_assert_contains( 'Service::set_reaction', $rest, 'REST mutation must delegate to the secured domain service.' );

$install = strtolower( cb_likes_source( 'src/Install.php' ) );
foreach ( [ 'ip_address', 'fingerprint', 'device_id', 'geolocation', 'latitude', 'longitude' ] as $forbidden ) {
	cb_likes_assert_not_contains( $forbidden, $install, 'Privacy regression: forbidden storage field ' . $forbidden );
}
cb_likes_assert_contains( 'unique key user_target', $install, 'Reaction storage must preserve one reaction per user/target.' );

$main = cb_likes_source( 'core-blueprint-likes.php' );
foreach ( [
	'cb_likes_count', 'cb_likes_user_has_liked', 'cb_likes_set_liked',
	'cb_likes_dislike_count', 'cb_likes_user_has_disliked', 'cb_likes_set_disliked', 'cb_likes_set_reaction',
] as $function ) {
	cb_likes_assert_contains( 'function ' . $function . '(', $main, 'Public helper missing: ' . $function );
}

$renderer = cb_likes_source( 'src/Frontend/Renderer.php' );
foreach ( [ 'cb_like_button', 'cb_like_count', 'cb_dislike_button', 'cb_dislike_count' ] as $shortcode ) {
	cb_likes_assert_contains( "'" . $shortcode . "'", $renderer, 'Public shortcode missing: ' . $shortcode );
}

echo "domain-security-regression: PASS\n";

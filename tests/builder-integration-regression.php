<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

$root = cb_likes_test_root();
cb_likes_assert( ! is_file( $root . '/src/Bricks/Integration.php' ), 'Legacy Bricks adapter must not remain.' );

$adapter = cb_likes_source( 'src/Integration/Bricks/Integration.php' );
foreach ( [ 'Builder\\Data', 'Builder\\Conditions', 'Builder\\Queries' ] as $contract ) {
	cb_likes_assert_contains( $contract, $adapter, 'Bricks adapter must consume ' . $contract . '.' );
}
foreach ( [ 'CB\\Likes\\Repository', 'CB\\Likes\\Settings', 'CB\\Likes\\Targets', 'CB\\Likes\\Service' ] as $domain ) {
	cb_likes_assert_not_contains( $domain, $adapter, 'Bricks adapter must not directly consume ' . $domain . '.' );
}
foreach ( [ 'cb_likes_count', 'cb_likes_dislike_count', 'cb_likes_has_liked', 'cb_likes_has_disliked', 'cb_likes_liked_posts', 'cb_likes_most_liked' ] as $id ) {
	cb_likes_assert_contains( $id, $adapter, 'Existing Bricks contract missing: ' . $id );
}

$registry = cb_likes_source( 'src/Integration/Bricks/ElementRegistry.php' );
foreach ( [ 'cb-likes-like', 'cb-likes-dislike' ] as $element ) {
	cb_likes_assert_contains( $element, $registry, 'Expected dedicated Bricks element missing: ' . $element );
}

foreach ( [ 'Like.php', 'Dislike.php' ] as $file ) {
	$element = cb_likes_source( 'src/Integration/Bricks/Elements/' . $file );
	cb_likes_assert_contains( 'Builder\\Components', $element, $file . ' must use builder-neutral Components.' );
	cb_likes_assert_not_contains( 'Repository', $element, $file . ' must not own storage.' );
	cb_likes_assert_not_contains( 'Service', $element, $file . ' must not own mutation logic.' );
}

echo "builder-integration-regression: PASS\n";

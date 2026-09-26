<?php
declare(strict_types=1);

$root = dirname( __DIR__ );

$required = [
	'tools/build-release',
	'tools/check',
	'tools/i18n/catalog.py',
	'tools/i18n/check',
	'tools/i18n/check-reference',
	'tools/i18n/config.json',
	'tools/i18n/config.json.example',
	'tools/i18n/reference.json',
	'tools/i18n/update',
];

foreach ( $required as $relative ) {
	if ( ! is_file( $root . '/' . $relative ) ) {
		throw new RuntimeException( 'Missing canonical release tooling path: ' . $relative );
	}
}

$config = json_decode( (string) file_get_contents( $root . '/tools/i18n/config.json' ), true, 512, JSON_THROW_ON_ERROR );
$expected = [
	'product'          => 'Core Blueprint Likes',
	'domain'           => 'core-blueprint-likes',
	'main_file'        => 'core-blueprint-likes.php',
	'version_constant' => 'CB_LIKES_VERSION',
	'skip_js'          => true,
	'commit_mo'        => true,
	'commit_l10n_php'  => false,
];

foreach ( $expected as $key => $value ) {
	if ( ! array_key_exists( $key, $config ) || $config[ $key ] !== $value ) {
		throw new RuntimeException( 'Unexpected Likes i18n config value: ' . $key );
	}
}

$builder = (string) file_get_contents( $root . '/tools/build-release' );
if ( ! str_contains( $builder, '"$ROOT/tools/check"' ) ) {
	throw new RuntimeException( 'Release builder must run the canonical product check before packaging.' );
}
if ( str_contains( $builder, 'sync-i18n.py' ) ) {
	throw new RuntimeException( 'Release builder must not invoke the retired mutating i18n sync.' );
}
if ( ! str_contains( $builder, 'mktemp -d' ) || ! str_contains( $builder, '.zip.sha256' ) ) {
	throw new RuntimeException( 'Release builder must use temporary staging and emit a checksum sidecar.' );
}

$legacy = [
	'tools/sync-i18n.py',
	'tools/i18n-translations-admin.json',
	'tools/i18n-translations-bricks.json',
	'tools/i18n-translations-builder-context.json',
	'tools/i18n-translations-frontend.json',
];

foreach ( $legacy as $relative ) {
	if ( file_exists( $root . '/' . $relative ) ) {
		throw new RuntimeException( 'Retired translation authority remains present: ' . $relative );
	}
}

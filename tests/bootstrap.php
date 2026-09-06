<?php
declare(strict_types=1);

function cb_likes_test_root(): string {
	return dirname( __DIR__ );
}

function cb_likes_source( string $path ): string {
	$file = cb_likes_test_root() . '/' . ltrim( $path, '/' );
	if ( ! is_file( $file ) ) {
		throw new RuntimeException( 'Missing expected file: ' . $path );
	}
	$content = file_get_contents( $file );
	if ( false === $content ) {
		throw new RuntimeException( 'Unable to read: ' . $path );
	}
	return $content;
}

function cb_likes_assert( bool $condition, string $message ): void {
	if ( ! $condition ) {
		throw new RuntimeException( $message );
	}
}

function cb_likes_assert_contains( string $needle, string $haystack, string $message ): void {
	cb_likes_assert( str_contains( $haystack, $needle ), $message );
}

function cb_likes_assert_not_contains( string $needle, string $haystack, string $message ): void {
	cb_likes_assert( ! str_contains( $haystack, $needle ), $message );
}

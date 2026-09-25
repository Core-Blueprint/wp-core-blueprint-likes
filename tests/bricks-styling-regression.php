<?php
declare(strict_types=1);

$root     = dirname( __DIR__ );
$registry = (string) file_get_contents( $root . '/src/Integration/Bricks/ElementRegistry.php' );
$element  = (string) file_get_contents( $root . '/src/Integration/Bricks/Elements/Element.php' );
$renderer = (string) file_get_contents( $root . '/src/Frontend/Renderer.php' );

function cb_likes_bricks_assert( bool $condition, string $message ): void {
	if ( ! $condition ) {
		fwrite( STDERR, "FAIL: $message\n" );
		exit( 1 );
	}
}

function cb_likes_control_block( string $content, string $name ): string {
	$marker = "\t\t\$this->controls['" . $name . "'] = [";
	$start  = strpos( $content, $marker );
	cb_likes_bricks_assert( false !== $start, 'Missing Bricks control: ' . $name );

	$next   = strpos( $content, "\n\t\t\$this->controls[", (int) $start + strlen( $marker ) );
	$target = strpos( $content, "\n\t}\n\n\t/** @return array{type:string,id:int}|null */", (int) $start + strlen( $marker ) );
	$end    = false !== $next ? $next : $target;
	cb_likes_bricks_assert( false !== $end, 'Could not delimit Bricks control: ' . $name );

	return substr( $content, (int) $start, (int) $end - (int) $start );
}

cb_likes_bricks_assert( str_contains( $registry, "public const CATEGORY = 'core-blueprint-likes';" ), 'Likes must use one canonical Bricks category id.' );
cb_likes_bricks_assert( str_contains( $registry, "add_filter( 'bricks/builder/i18n'" ), 'Likes must expose a translated Bricks category label.' );
cb_likes_bricks_assert( str_contains( $element, 'public $category = ElementRegistry::CATEGORY;' ), 'Likes elements must consume the canonical category id.' );

cb_likes_bricks_assert( ! str_contains( $element, "'tab'   => 'style'" ) && ! str_contains( $element, "'tab'      => 'style'" ), 'Likes-specific controls must remain under Content.' );
cb_likes_bricks_assert( ! preg_match( "/'type'\s*=>\s*'slider'/", $element ), 'Likes must not use sliders for CSS lengths.' );

foreach ( [
	'flexWrap'       => 'flex-wrap',
	'direction'      => 'flex-direction',
	'justifyContent' => 'justify-content',
	'alignItems'     => 'align-items',
	'columnGap'      => 'column-gap',
	'rowGap'         => 'row-gap',
] as $name => $property ) {
	$block = cb_likes_control_block( $element, $name );
	cb_likes_bricks_assert( str_contains( $block, "'property' => '" . $property . "'" ), $name . ' must target ' . $property . '.' );
}

foreach ( [ 'columnGap', 'rowGap', 'buttonMinHeight', 'iconSize' ] as $name ) {
	$block = cb_likes_control_block( $element, $name );
	cb_likes_bricks_assert( (bool) preg_match( "/'type'\s*=>\s*'number'/", $block ), $name . ' must use native Bricks number control.' );
	cb_likes_bricks_assert( (bool) preg_match( "/'units'\s*=>\s*true/", $block ), $name . ' must expose native Bricks units.' );
}

$count = cb_likes_control_block( $element, 'countTypography' );
cb_likes_bricks_assert( str_contains( $count, "'showCount'" ) && str_contains( $count, 'true' ), 'Count styling must only appear when count markup is enabled.' );

$background_count = preg_match_all( "/'type'\s*=>\s*'background'/", $element );
$video_excludes   = preg_match_all( "/'exclude'\s*=>\s*\[\s*'videoUrl',\s*'videoScale'\s*\]/", $element );
cb_likes_bricks_assert( $background_count === $video_excludes, 'Every Likes internal Background control must exclude video settings.' );

foreach ( [
	"'buttonHoverBackground'",
	"'buttonFocusBackground'",
	"'activeTypography'",
	"'activeBackground'",
	"'activeBorder'",
	"'activeIconColor'",
	"'disabledOpacity'",
] as $needle ) {
	cb_likes_bricks_assert( str_contains( $element, $needle ), 'Likes Golden state contract missing ' . $needle . '.' );
}

preg_match_all( "/'selector'\s*=>\s*'([^']+)'/", $element, $selectors );
foreach ( $selectors[1] ?? [] as $selector ) {
	preg_match_all( '/\.([a-zA-Z0-9_-]+)/', (string) $selector, $classes );
	foreach ( $classes[1] ?? [] as $class ) {
		cb_likes_bricks_assert( str_contains( $renderer, (string) $class ), 'Likes selector class .' . $class . ' is absent from canonical renderer markup.' );
	}
}

fwrite( STDOUT, "Likes Golden Bricks regression PASS\n" );

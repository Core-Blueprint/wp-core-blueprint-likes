<?php
declare(strict_types=1);

namespace CB\Likes;

defined( 'ABSPATH' ) || exit;

/**
 * Curated local Lucide icon catalog used by the built-in reaction icon picker.
 *
 * Only the icons shipped here are included; there is no Lucide runtime or
 * external request. See THIRD-PARTY-LICENSES.md for license notices.
 */
final class Icons {
	/** @return array<string,string> */
	public static function labels(): array {
		return [
			'heart'        => __( 'Heart', 'core-blueprint-likes' ),
			'heart-crack'  => __( 'Heart Crack', 'core-blueprint-likes' ),
			'thumbs-up'    => __( 'Thumbs Up', 'core-blueprint-likes' ),
			'thumbs-down'  => __( 'Thumbs Down', 'core-blueprint-likes' ),
			'lightbulb'    => __( 'Lightbulb', 'core-blueprint-likes' ),
			'circle-slash' => __( 'Circle Slash', 'core-blueprint-likes' ),
			'star'         => __( 'Star', 'core-blueprint-likes' ),
			'sparkles'     => __( 'Sparkles', 'core-blueprint-likes' ),
		];
	}

	/** @return array<string,string> */
	public static function svgs(): array {
		$root = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">';

		return [
			'heart'        => $root . '<path d="M2 9.5a5.5 5.5 0 0 1 9.591-3.676.56.56 0 0 0 .818 0A5.49 5.49 0 0 1 22 9.5c0 2.29-1.5 4-3 5.5l-5.492 5.313a2 2 0 0 1-3 .019L5 15c-1.5-1.5-3-3.2-3-5.5" /></svg>',
			'heart-crack'  => $root . '<path d="M12.409 5.824c-.702.792-1.15 1.496-1.415 2.166l2.153 2.156a.5.5 0 0 1 0 .707l-2.293 2.293a.5.5 0 0 0 0 .707L12 15" /><path d="M13.508 20.313a2 2 0 0 1-3 .019L5 15c-1.5-1.5-3-3.2-3-5.5a5.5 5.5 0 0 1 9.591-3.677.6.6 0 0 0 .818.001A5.5 5.5 0 0 1 22 9.5c0 2.29-1.5 4-3 5.5z" /></svg>',
			'thumbs-up'    => $root . '<path d="M15 5.88 14 10h5.83a2 2 0 0 1 1.92 2.56l-2.33 8A2 2 0 0 1 17.5 22H4a2 2 0 0 1-2-2v-8a2 2 0 0 1 2-2h2.76a2 2 0 0 0 1.79-1.11L12 2a3.13 3.13 0 0 1 3 3.88Z" /><path d="M7 10v12" /></svg>',
			'thumbs-down'  => $root . '<path d="M9 18.12 10 14H4.17a2 2 0 0 1-1.92-2.56l2.33-8A2 2 0 0 1 6.5 2H20a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2h-2.76a2 2 0 0 0-1.79 1.11L12 22a3.13 3.13 0 0 1-3-3.88Z" /><path d="M17 14V2" /></svg>',
			'lightbulb'    => $root . '<path d="M15 14c.2-1 .7-1.7 1.5-2.5 1-.9 1.5-2.2 1.5-3.5A6 6 0 0 0 6 8c0 1 .2 2.2 1.5 3.5.7.7 1.3 1.5 1.5 2.5" /><path d="M9 18h6" /><path d="M10 22h4" /></svg>',
			'circle-slash' => $root . '<circle cx="12" cy="12" r="10" /><line x1="9" x2="15" y1="15" y2="9" /></svg>',
			'star'         => $root . '<path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z" /></svg>',
			'sparkles'     => $root . '<path d="M11.017 2.814a1 1 0 0 1 1.966 0l1.051 5.558a2 2 0 0 0 1.594 1.594l5.558 1.051a1 1 0 0 1 0 1.966l-5.558 1.051a2 2 0 0 0-1.594 1.594l-1.051 5.558a1 1 0 0 1-1.966 0l-1.051-5.558a2 2 0 0 0-1.594-1.594l-5.558-1.051a1 1 0 0 1 0-1.966l5.558-1.051a2 2 0 0 0 1.594-1.594z" /><path d="M20 2v4" /><path d="M22 4h-4" /><circle cx="4" cy="20" r="2" /></svg>',
		];
	}

	public static function exists( string $name ): bool {
		return isset( self::svgs()[ $name ] );
	}

	public static function svg( string $name, string $fallback = 'heart' ): string {
		$icons = self::svgs();
		if ( isset( $icons[ $name ] ) ) {
			return $icons[ $name ];
		}
		return $icons[ $fallback ] ?? $icons['heart'];
	}
}

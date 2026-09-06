<?php
declare(strict_types=1);

namespace CB\Likes\Admin;

use CB\Core\Admin\Page as PageContract;
use CB\Likes\Capabilities;

defined( 'ABSPATH' ) || exit;

final class CoreBlueprintPage implements PageContract {
	public const SLUG = 'core-blueprint-likes';

	public function slug(): string { return self::SLUG; }
	public function title(): string { return __( 'Likes', 'core-blueprint-likes' ); }
	public function menu_title(): string { return __( 'Likes', 'core-blueprint-likes' ); }
	public function capability(): string { return Capabilities::MANAGE; }
	public function position(): ?int { return 145; }

	public function render(): void {
		if ( ! current_user_can( $this->capability() ) ) {
			wp_die( esc_html__( 'You are not allowed to manage Likes.', 'core-blueprint-likes' ) );
		}
		PageContent::render();
	}
}

<?php
declare(strict_types=1);

namespace CB\Likes\Integration\Bricks\Elements;

use CB\Likes\Builder\Components;

defined( 'ABSPATH' ) || exit;

final class Like extends Element {
	public $name = 'cb-likes-like';
	public $icon = 'ti-heart';

	public function get_label(): string {
		return esc_html__( 'Like', 'core-blueprint-likes' );
	}

	public function set_controls(): void {
		$this->reaction_controls( true );
	}

	public function render(): void {
		$this->render_component(
			Components::like_button( $this->target( true ), $this->show_count() ),
			'like',
			esc_html__( 'No enabled Like target detected. Use this element in a supported template or Query Loop, or choose a specific target.', 'core-blueprint-likes' )
		);
	}
}

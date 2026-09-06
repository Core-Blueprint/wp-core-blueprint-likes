<?php
declare(strict_types=1);

namespace CB\Likes\Integration\Bricks\Elements;

use CB\Likes\Builder\Components;

defined( 'ABSPATH' ) || exit;

final class Dislike extends Element {
	public $name = 'cb-likes-dislike';
	public $icon = 'ti-thumb-down';

	public function get_label(): string {
		return esc_html__( 'Dislike', 'core-blueprint-likes' );
	}

	public function set_controls(): void {
		$this->reaction_controls( false );
	}

	public function render(): void {
		$this->render_component(
			Components::dislike_button( $this->target( false ), $this->show_count() ),
			'dislike',
			esc_html__( 'No enabled dislike target detected. Use this element in a supported post template or Query Loop, or choose a specific target with dislikes enabled.', 'core-blueprint-likes' )
		);
	}
}

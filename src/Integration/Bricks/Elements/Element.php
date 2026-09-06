<?php
declare(strict_types=1);

namespace CB\Likes\Integration\Bricks\Elements;

use CB\Likes\Builder\Context;
use CB\Likes\Builder\Targets;

defined( 'ABSPATH' ) || exit;

abstract class Element extends \Bricks\Element {
	public $category = 'core-blueprint-likes';

	/** @return string[] */
	public function get_keywords(): array {
		return [ 'core blueprint', 'likes', 'like', 'reaction' ];
	}

	protected function reaction_controls( bool $include_users ): void {
		$this->controls['cbSource'] = [
			'tab' => 'content',
			'label' => esc_html__( 'Target source', 'core-blueprint-likes' ),
			'type' => 'select',
			'options' => [
				'current' => Targets::current_label(),
				'manual' => Targets::specific_label(),
			],
			'default' => 'current',
			'clearable' => false,
			'pasteStyles' => false,
			'description' => esc_html__( 'Use the current template or Query Loop target by default. Choose a specific target only for layouts outside normal context.', 'core-blueprint-likes' ),
		];

		$this->controls['cbManualTarget'] = [
			'tab' => 'content',
			'label' => esc_html__( 'Target', 'core-blueprint-likes' ),
			'type' => 'select',
			'options' => Targets::options( $include_users ),
			'placeholder' => Targets::select_placeholder( $include_users ),
			'searchable' => true,
			'clearable' => true,
			'pasteStyles' => false,
			'description' => esc_html__( 'Advanced override. Targets remain subject to the Likes configuration and access rules.', 'core-blueprint-likes' ),
			'required' => [ 'cbSource', '=', 'manual' ],
		];

		$this->controls['showCount'] = [
			'tab' => 'content',
			'label' => esc_html__( 'Show count', 'core-blueprint-likes' ),
			'type' => 'checkbox',
			'default' => true,
		];
	}

	/** @return array{type:string,id:int}|null */
	protected function target( bool $include_users ): ?array {
		if ( 'manual' === (string) ( $this->settings['cbSource'] ?? 'current' ) ) {
			return Targets::from_option( (string) ( $this->settings['cbManualTarget'] ?? '' ), $include_users );
		}

		return Context::target();
	}

	protected function show_count(): bool {
		return ! isset( $this->settings['showCount'] ) || (bool) $this->settings['showCount'];
	}

	protected function render_component( string $html, string $modifier, string $empty_message ): void {
		if ( '' === trim( $html ) ) {
			if ( function_exists( 'bricks_is_builder' ) && bricks_is_builder() ) {
				$this->render_element_placeholder( [
					'icon-class' => (string) $this->icon,
					'text' => $empty_message,
				] );
			}
			return;
		}

		$this->set_attribute( '_root', 'class', [
			'cb-likes-element',
			'cb-likes-element--' . sanitize_html_class( $modifier ),
		] );
		echo '<div ' . $this->render_attributes( '_root' ) . '>' . $html . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted Likes renderer output.
	}
}

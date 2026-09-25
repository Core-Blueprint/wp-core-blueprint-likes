<?php
declare(strict_types=1);

namespace CB\Likes\Integration\Bricks\Elements;

use CB\Likes\Builder\Context;
use CB\Likes\Builder\Targets;
use CB\Likes\Integration\Bricks\ElementRegistry;

defined( 'ABSPATH' ) || exit;

abstract class Element extends \Bricks\Element {
	public $category = ElementRegistry::CATEGORY;

	/** @return string[] */
	public function get_keywords(): array {
		return [ 'core blueprint', 'likes', 'like', 'reaction' ];
	}

	public function set_control_groups(): void {
		foreach ( [
			'reaction' => esc_html__( 'Reaction', 'core-blueprint-likes' ),
			'layout'   => esc_html__( 'Layout', 'core-blueprint-likes' ),
			'button'   => esc_html__( 'Button', 'core-blueprint-likes' ),
			'icon'     => esc_html__( 'Icon', 'core-blueprint-likes' ),
			'label'    => esc_html__( 'Label', 'core-blueprint-likes' ),
			'count'    => esc_html__( 'Count', 'core-blueprint-likes' ),
			'active'   => esc_html__( 'Active state', 'core-blueprint-likes' ),
			'disabled' => esc_html__( 'Disabled state', 'core-blueprint-likes' ),
		] as $key => $title ) {
			$this->control_groups[ $key ] = [
				'title' => $title,
				'tab'   => 'content',
			];
		}
	}

	protected function reaction_controls( bool $include_users ): void {
		$this->controls['cbSource'] = [
			'tab'         => 'content',
			'group'       => 'reaction',
			'label'       => esc_html__( 'Target source', 'core-blueprint-likes' ),
			'type'        => 'select',
			'options'     => [
				'current' => Targets::current_label(),
				'manual'  => Targets::specific_label(),
			],
			'default'     => 'current',
			'clearable'   => false,
			'pasteStyles' => false,
			'description' => esc_html__( 'Use the current template or Query Loop target by default. Choose a specific target only for layouts outside normal context.', 'core-blueprint-likes' ),
		];

		$this->controls['cbManualTarget'] = [
			'tab'         => 'content',
			'group'       => 'reaction',
			'label'       => esc_html__( 'Target', 'core-blueprint-likes' ),
			'type'        => 'select',
			'options'     => Targets::options( $include_users ),
			'placeholder' => Targets::select_placeholder( $include_users ),
			'searchable'  => true,
			'clearable'   => true,
			'pasteStyles' => false,
			'description' => esc_html__( 'Advanced override. Targets remain subject to the Likes configuration and access rules.', 'core-blueprint-likes' ),
			'required'    => [ 'cbSource', '=', 'manual' ],
		];

		$this->controls['showCount'] = [
			'tab'     => 'content',
			'group'   => 'reaction',
			'label'   => esc_html__( 'Show count', 'core-blueprint-likes' ),
			'type'    => 'checkbox',
			'default' => true,
		];

		$this->controls['flexWrap'] = [
			'tab'     => 'content',
			'group'   => 'layout',
			'label'   => esc_html__( 'Flex wrap', 'core-blueprint-likes' ),
			'type'    => 'select',
			'options' => [
				'nowrap'       => esc_html__( 'No wrap', 'core-blueprint-likes' ),
				'wrap'         => esc_html__( 'Wrap', 'core-blueprint-likes' ),
				'wrap-reverse' => esc_html__( 'Wrap reverse', 'core-blueprint-likes' ),
			],
			'inline'  => true,
			'css'     => [ [ 'property' => 'flex-wrap', 'selector' => '.cb-reaction-button' ] ],
		];
		$this->controls['direction'] = [
			'tab'      => 'content',
			'group'    => 'layout',
			'label'    => esc_html__( 'Direction', 'core-blueprint-likes' ),
			'type'     => 'direction',
			'inline'   => true,
			'rerender' => true,
			'css'      => [ [ 'property' => 'flex-direction', 'selector' => '.cb-reaction-button' ] ],
		];
		$this->controls['justifyContent'] = [
			'tab'   => 'content',
			'group' => 'layout',
			'label' => esc_html__( 'Align main axis', 'core-blueprint-likes' ),
			'type'  => 'justify-content',
			'css'   => [ [ 'property' => 'justify-content', 'selector' => '.cb-reaction-button' ] ],
		];
		$this->controls['alignItems'] = [
			'tab'   => 'content',
			'group' => 'layout',
			'label' => esc_html__( 'Align cross axis', 'core-blueprint-likes' ),
			'type'  => 'align-items',
			'css'   => [ [ 'property' => 'align-items', 'selector' => '.cb-reaction-button' ] ],
		];
		$this->controls['columnGap'] = [
			'tab'   => 'content',
			'group' => 'layout',
			'label' => esc_html__( 'Column gap', 'core-blueprint-likes' ),
			'type'  => 'number',
			'units' => true,
			'css'   => [ [ 'property' => 'column-gap', 'selector' => '.cb-reaction-button' ] ],
		];
		$this->controls['rowGap'] = [
			'tab'   => 'content',
			'group' => 'layout',
			'label' => esc_html__( 'Row gap', 'core-blueprint-likes' ),
			'type'  => 'number',
			'units' => true,
			'css'   => [ [ 'property' => 'row-gap', 'selector' => '.cb-reaction-button' ] ],
		];

		$this->controls['buttonTypography'] = [
			'tab'   => 'content',
			'group' => 'button',
			'label' => esc_html__( 'Typography', 'core-blueprint-likes' ),
			'type'  => 'typography',
			'css'   => [ [ 'property' => 'typography', 'selector' => '.cb-reaction-button' ] ],
		];
		$this->controls['buttonBackground'] = [
			'tab'     => 'content',
			'group'   => 'button',
			'label'   => esc_html__( 'Background', 'core-blueprint-likes' ),
			'type'    => 'background',
			'exclude' => [ 'videoUrl', 'videoScale' ],
			'css'     => [ [ 'property' => 'background', 'selector' => '.cb-reaction-button' ] ],
		];
		$this->controls['buttonBorder'] = [
			'tab'   => 'content',
			'group' => 'button',
			'label' => esc_html__( 'Border', 'core-blueprint-likes' ),
			'type'  => 'border',
			'css'   => [ [ 'property' => 'border', 'selector' => '.cb-reaction-button' ] ],
		];
		$this->controls['buttonPadding'] = [
			'tab'   => 'content',
			'group' => 'button',
			'label' => esc_html__( 'Padding', 'core-blueprint-likes' ),
			'type'  => 'dimensions',
			'css'   => [ [ 'property' => 'padding', 'selector' => '.cb-reaction-button' ] ],
		];
		$this->controls['buttonShadow'] = [
			'tab'   => 'content',
			'group' => 'button',
			'label' => esc_html__( 'Box shadow', 'core-blueprint-likes' ),
			'type'  => 'box-shadow',
			'css'   => [ [ 'property' => 'box-shadow', 'selector' => '.cb-reaction-button' ] ],
		];
		$this->controls['buttonMinHeight'] = [
			'tab'   => 'content',
			'group' => 'button',
			'label' => esc_html__( 'Minimum height', 'core-blueprint-likes' ),
			'type'  => 'number',
			'units' => true,
			'css'   => [ [ 'property' => 'min-height', 'selector' => '.cb-reaction-button' ] ],
		];
		$this->controls['buttonHoverColor'] = [
			'tab'   => 'content',
			'group' => 'button',
			'label' => esc_html__( 'Hover text color', 'core-blueprint-likes' ),
			'type'  => 'color',
			'css'   => [ [ 'property' => 'color', 'selector' => '.cb-reaction-button:hover' ] ],
		];
		$this->controls['buttonHoverBackground'] = [
			'tab'   => 'content',
			'group' => 'button',
			'label' => esc_html__( 'Hover background color', 'core-blueprint-likes' ),
			'type'  => 'color',
			'css'   => [ [ 'property' => 'background-color', 'selector' => '.cb-reaction-button:hover' ] ],
		];
		$this->controls['buttonHoverBorder'] = [
			'tab'   => 'content',
			'group' => 'button',
			'label' => esc_html__( 'Hover border', 'core-blueprint-likes' ),
			'type'  => 'border',
			'css'   => [ [ 'property' => 'border', 'selector' => '.cb-reaction-button:hover' ] ],
		];
		$this->controls['buttonFocusColor'] = [
			'tab'   => 'content',
			'group' => 'button',
			'label' => esc_html__( 'Focus text color', 'core-blueprint-likes' ),
			'type'  => 'color',
			'css'   => [ [ 'property' => 'color', 'selector' => '.cb-reaction-button:focus-visible' ] ],
		];
		$this->controls['buttonFocusBackground'] = [
			'tab'   => 'content',
			'group' => 'button',
			'label' => esc_html__( 'Focus background color', 'core-blueprint-likes' ),
			'type'  => 'color',
			'css'   => [ [ 'property' => 'background-color', 'selector' => '.cb-reaction-button:focus-visible' ] ],
		];
		$this->controls['buttonFocusBorder'] = [
			'tab'   => 'content',
			'group' => 'button',
			'label' => esc_html__( 'Focus border', 'core-blueprint-likes' ),
			'type'  => 'border',
			'css'   => [ [ 'property' => 'border', 'selector' => '.cb-reaction-button:focus-visible' ] ],
		];

		$this->controls['iconColor'] = [
			'tab'   => 'content',
			'group' => 'icon',
			'label' => esc_html__( 'Color', 'core-blueprint-likes' ),
			'type'  => 'color',
			'css'   => [ [ 'property' => 'color', 'selector' => '.cb-reaction-button__icon' ] ],
		];
		$this->controls['iconSize'] = [
			'tab'   => 'content',
			'group' => 'icon',
			'label' => esc_html__( 'Size', 'core-blueprint-likes' ),
			'type'  => 'number',
			'units' => true,
			'css'   => [ [ 'property' => 'font-size', 'selector' => '.cb-reaction-button__icon' ] ],
		];
		$this->controls['labelTypography'] = [
			'tab'   => 'content',
			'group' => 'label',
			'label' => esc_html__( 'Typography', 'core-blueprint-likes' ),
			'type'  => 'typography',
			'css'   => [ [ 'property' => 'typography', 'selector' => '.cb-reaction-button__label' ] ],
		];
		$this->controls['countTypography'] = [
			'tab'      => 'content',
			'group'    => 'count',
			'label'    => esc_html__( 'Typography', 'core-blueprint-likes' ),
			'type'     => 'typography',
			'css'      => [ [ 'property' => 'typography', 'selector' => '.cb-reaction-button__count' ] ],
			'required' => [ 'showCount', '=', true ],
		];

		$this->controls['activeTypography'] = [
			'tab'   => 'content',
			'group' => 'active',
			'label' => esc_html__( 'Typography', 'core-blueprint-likes' ),
			'type'  => 'typography',
			'css'   => [ [ 'property' => 'typography', 'selector' => '.cb-reaction-button.is-active' ] ],
		];
		$this->controls['activeBackground'] = [
			'tab'     => 'content',
			'group'   => 'active',
			'label'   => esc_html__( 'Background', 'core-blueprint-likes' ),
			'type'    => 'background',
			'exclude' => [ 'videoUrl', 'videoScale' ],
			'css'     => [ [ 'property' => 'background', 'selector' => '.cb-reaction-button.is-active' ] ],
		];
		$this->controls['activeBorder'] = [
			'tab'   => 'content',
			'group' => 'active',
			'label' => esc_html__( 'Border', 'core-blueprint-likes' ),
			'type'  => 'border',
			'css'   => [ [ 'property' => 'border', 'selector' => '.cb-reaction-button.is-active' ] ],
		];
		$this->controls['activeIconColor'] = [
			'tab'   => 'content',
			'group' => 'active',
			'label' => esc_html__( 'Icon color', 'core-blueprint-likes' ),
			'type'  => 'color',
			'css'   => [ [ 'property' => 'color', 'selector' => '.cb-reaction-button.is-active .cb-reaction-button__icon' ] ],
		];

		$this->controls['disabledOpacity'] = [
			'tab'   => 'content',
			'group' => 'disabled',
			'label' => esc_html__( 'Opacity', 'core-blueprint-likes' ),
			'type'  => 'number',
			'min'   => 0,
			'max'   => 1,
			'step'  => 0.1,
			'css'   => [ [ 'property' => 'opacity', 'selector' => '.cb-reaction-button:disabled' ] ],
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
					'text'       => $empty_message,
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

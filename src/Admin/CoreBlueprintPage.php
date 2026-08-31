<?php
declare(strict_types=1);

namespace CB\Likes\Admin;

use CB\Likes\Capabilities;

defined( 'ABSPATH' ) || exit;

final class CoreBlueprintPage extends \CB\Core\Admin\PageBase {
	public function slug(): string { return 'core-blueprint-likes'; }
	public function title(): string { return __( 'Likes', 'core-blueprint-likes' ); }
	public function menu_title(): string { return __( 'Likes', 'core-blueprint-likes' ); }
	public function capability(): string { return Capabilities::MANAGE; }
	public function position(): ?int { return 145; }
	public function render(): void { $this->guard(); PageContent::render( true ); }
}

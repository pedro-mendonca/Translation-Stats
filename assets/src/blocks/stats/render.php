<?php
/**
 * Render the Translation Stats block.
 *
 * @package Translation_Stats
 *
 * @since 2.1.0
 *
 * @var array<string, mixed> $attributes   Block attributes.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit; // @codeCoverageIgnore
}

echo Translation_Stats\Stats_Block::render( $attributes ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped, VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable -- Escaped in Stats_Block::render(), with the attributes from the block render.

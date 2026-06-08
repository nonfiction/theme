<?php

namespace Nonfiction\Theme\Timber;

use Nonfiction\Theme\WordPress\BlockTypeRegistrar;
use Nonfiction\Theme\WordPress\Meta;
use Timber\Timber;

use function Nonfiction\Theme\ends_with;
use function Nonfiction\Theme\import;

class Block
{
  public static $name = null;
  public static $args = [];
  public static $props = [];

  // Register a block from JSON config or a PHP override array.
  public static function register_block_type($json = [], $override = [])
  {

    // If the first parameter is a JSON path, import it first.
    if ((is_string($json)) and (ends_with($json, '.json'))) {
      $json = import($json);
    }

    // Combine json with override
    $args = array_merge($json, $override);

    // Look for $name inside $args
    if (is_array($json)) {
      $name = ($args['name']) ?? false;
    }

    // If no name has been passed, bail out
    if (empty($name)) {
      return false;
    }

    // Post Meta
    $meta = $args['meta'] ?? [];
    unset($args['meta']);

    // Set default values for args array
    $args['render_callback'] ??= false;

    // If there's a render value, overwrite the render_callback
    if (isset($args['render'])) {
      $args['render_callback'] = static::render_callback($args['render']);
    }
    unset($args['render']);

    // Register block
    if (! static::register_custom_block_type($name, $args)) {
      return false;
    }

    // Save $name and reset $args, $props
    static::$name = $name;
    static::$args = $args;
    static::$props = [
      'meta' => $meta,
    ];

    // Register meta
    static::register_post_meta();
  }

  // Turn a render callback into compiled Twig output.
  protected static function render_callback($render)
  {
    return function ($attributes, $inner = '', $block = null) use ($render) {
      $attributes = \Nonfiction\Theme\Assets::normalize_asset_value($attributes);

      // Merge the Timber context with the WP attributes, add inner blocks
      $context = array_merge(Timber::context(), $attributes);
      $context['inner'] = $inner;
      $context['block'] = $block;
      $context['parsedBlock'] = $block->parsed_block ?? [];
      $context['innerBlocks'] = $block->parsed_block['innerBlocks'] ?? [];

      // Run the passed render() function to get the twig template and updated context
      $template = ($render)($context);

      // Get the compiled template from twig file
      if (ends_with($template, '.twig')) {
        $html = Timber::compile($template, $context);

        // Otherwise, compile the returned string as Twig.
      } else {
        $html = Timber::compile_string($template, $context);
      }

      // Return the compiled template
      return $html;
    };
  }

  // Delegate block registration to WordPress after de-duping names.
  protected static function register_custom_block_type($name, array $args)
  {
    return BlockTypeRegistrar::register_block_type($name, $args);
  }

  // Register block meta after the block type is saved.
  protected static function register_post_meta()
  {
    Meta::register_block_post_meta(static::$props['meta'] ?? []);
  }
}

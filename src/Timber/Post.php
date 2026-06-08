<?php

namespace Nonfiction\Theme\Timber;

use Nonfiction\Theme\WordPress\Meta;
use Nonfiction\Theme\WordPress\PostTypeRegistrar;
use Nonfiction\Theme\WordPress\TaxonomyRegistrar;

use function Nonfiction\Theme\camelize;
use function Nonfiction\Theme\ends_with;
use function Nonfiction\Theme\hyphenate;
use function Nonfiction\Theme\import;
use function Nonfiction\Theme\pluralize;
use function Nonfiction\Theme\singularize;
use function Nonfiction\Theme\titleize;
use function Nonfiction\Theme\underscore;
use function Nonfiction\Theme\unique_pluralize;

class Post extends \Timber\Post
{
  private static $post_types = [];
  public static $classmap = [];
  protected static $register_post_type = [];

  // Register the post type hook when the class boots.
  public static function __constructStatic()
  {
    \add_action('init', [ static::class, 'register_post_type' ], 10);
  }

  // Default Timber lookups to this post type.
  public static function get_post($query = [], $options = [])
  {
    $query = (is_array($query)) ? array_merge([
      'post_type' => static::$name,
    ], $query) : $query;

    return \Timber::get_post($query, $options);
  }

  // Return all posts for this type unless the query narrows it.
  public static function get_posts($query = [], $options = [])
  {
    $query = (is_array($query)) ? array_merge([
      'post_type' => static::$name,
      'posts_per_page' => -1,
    ], $query) : $query;

    return \Timber::get_posts($query, $options);
  }

  // Look up one post in this type by a Timber search field.
  public static function get_post_by($type, $search_value, $args = null)
  {
    $args = (is_array($args)) ? $args : [ 'post_type' => static::$name ];

    return \Timber::get_post_by($type, $search_value, $args);
  }

  // Keep Timber links relative when the helper exists.
  public function link()
  {
    $link = parent::link();

    return (function_exists('\Nonfiction\Theme\make_link_relative')) ? \Nonfiction\Theme\make_link_relative($link) : $link;
  }

  public static $name = null;
  public static $names = [];
  public static $args = [];
  public static $props = [];

  // Register the class-backed post type and its extras.
  public static function register_post_type($json = [], $override = [])
  {

    // If the first parameter is a path to .json file, import that
    if ((is_string($json)) and (ends_with($json, '.json'))) {
      $json = array_merge(static::$register_post_type, import($json));

      // Otherwise, use the parameter as an array
    } else {
      $json = array_merge(static::$register_post_type, (is_array($json) ? $json : []));
    }

    // Combine json with override
    $args = array_merge($json, $override);

    // Look for $name inside $args
    if (is_array($json)) {
      $name = ($args['name']) ?? false;
    }

    // If no name passed, determine one from the classname
    if (empty($name)) {
      $name = underscore(preg_replace('/^.*\\\s*/', '', get_called_class()));
    }

    // Generate names from $name
    $names = static::generate_names($name, $args['names'] ?? []);
    $name = $names['key_single']; // update $name arg in case it got modified
    unset($args['names']);

    // If this post type has already been registered, bail out
    if (isset(self::$post_types[$name])) {
      return false;
    }
    self::$post_types[$name] = $name;

    // Save $names, $name and reset $args, $props
    static::$names = $names;
    static::$name = $name;
    static::$args = [];
    static::$props = [];

    // Add to classmap (Timber classes)
    self::$classmap = array_merge(self::$classmap, [ $name => $names['class'] ]);
    \add_filter('timber/post/classmap', function ($classmap) use ($name, $names) {
      return array_merge($classmap, [ $name => $names['class'] ]);
    });

    // Post Meta
    static::$props['meta'] = $args['meta'] ?? [];
    unset($args['meta']);

    // Blocks
    static::$props['blocks'] = $args['blocks'] ?? true;
    unset($args['blocks']);

    // Custom Metaboxes CMB2
    // https://github.com/CMB2/CMB2/wiki/Field-Types
    static::$props['metaboxes'] = $args['metaboxes'] ?? [];
    unset($args['metaboxes']);

    static::$props['taxonomies'] = $args['taxonomies'] ?? [];
    unset($args['taxonomies']);

    // No archive pages by default
    static::$props['has_archive'] = false;

    // If has_archive is set, use the plural slug
    if ((isset($args['has_archive'])) && ($args['has_archive'] !== false)) {
      static::$props['has_archive'] = $names['slug_plural'];
    }
    unset($args['has_archive']);

    // Save args to object
    static::$args = $args;

    static::before_register_post_type();

    // Finally register or customize the post type
    if (static::is_native_post_type()) {
      PostTypeRegistrar::customize_native_post_type(static::$name, static::$args);
    } else {
      static::register_custom_post_type();
    }

    static::register_taxonomies();
    static::register_post_meta();
    static::register_allowed_block_types();
    static::register_block_categories();
    static::register_custom_meta_boxes();

    static::after_register_post_type();

    static::activate();

    // Unset args and props
    static::$args = [];
    static::$props = [];
  }

  // Hook for subclasses before registration finishes.
  private static function before_register_post_type()
  {
    return true;
  }

  // Hook for subclasses after registration finishes.
  private static function after_register_post_type()
  {
    return true;
  }

  // Build the key, label, slug, and class name variants.
  private static function generate_names($name, $names = [])
  {

    // Ensure name is lowercase
    $name = strtolower($name);
    // Determine single and plural values from $name
    $single = singularize($name);
    $plural = pluralize($name);
    $unique_plural = unique_pluralize($name, $single);

    // Fill out these values if necessary
    if ((!isset($names['label_single'])) and (isset($names['label']))) {
      $names['label_single'] = singularize($names['label']);
    }
    if ((!isset($names['label_plural'])) and (isset($names['label']))) {
      $names['label_plural'] = pluralize($names['label']);
    }
    if ((!isset($names['slug_single'])) and (isset($names['slug']))) {
      $names['slug_single'] = singularize($names['slug']);
    }
    if ((!isset($names['slug_plural'])) and (isset($names['slug']))) {
      $names['slug_plural'] = pluralize($names['slug']);
    }
    if ((!isset($names['camel_single'])) and (isset($names['camel']))) {
      $names['camel_single'] = singularize($names['camel']);
    }
    if ((!isset($names['camel_plural'])) and (isset($names['camel']))) {
      $names['camel_plural'] = unique_pluralize($names['camel']);
    }

    // Return $names object
    return [

      // foo_bar
      'key_single' => underscore($names['key_single'] ?? $single),

      // foo_bars
      'key_plural' => underscore($names['key_plural'] ?? $unique_plural),

      // Foo Bar
      'label_single' => $names['label_single'] ?? titleize($single),

      // Foo Bars
      'label_plural' => $names['label_plural'] ?? titleize($plural),

      // foo-bar
      'slug_single' => hyphenate($names['slug_single'] ?? $single),

      // foo-bars
      'slug_plural' => hyphenate($names['slug_plural'] ?? $plural),

      // fooBar
      'camel_single' => camelize(($names['camel_single'] ?? $single), true),

      // fooBars
      'camel_plural' => camelize(($names['camel_plural'] ?? $unique_plural), true),

      // \Nonfiction\Theme\FooBar
      'class' => $names['class'] ?? get_called_class(),

    ];
  }

  // Register the custom post type with the computed names and args.
  private static function register_custom_post_type()
  {
    PostTypeRegistrar::register_custom_post_type(static::$names, static::$args, static::$props);
  }

  // Register any configured CMB2 metaboxes for this type.
  private static function register_custom_meta_boxes()
  {
    Meta::register_custom_meta_boxes(static::$names, static::$props['metaboxes'] ?? []);
  }

  // Limit the editor to the configured block types for this post type.
  private static function register_allowed_block_types()
  {

    $names = static::$names;
    $props = static::$props;

    \add_filter('allowed_block_types_all', function ($allowed_block_types, $editor_context) use ($names, $props) {

      if (! empty($editor_context->post)) {
        $post = $editor_context->post;
        if ($post->post_type === $names['key_single']) {
          if (is_array($props['blocks'])) {
            $types = [];
            foreach ($props['blocks'] as $type) {
              $types[] = $type;
            };

            return $types;
          } else {
            return true;
          }
        }
      }

      return $allowed_block_types;
    }, 10, 2);
  }

  // Add a block category named after this post type.
  private static function register_block_categories()
  {
    $names = static::$names;
    \add_filter('block_categories_all', function ($block_categories, $editor_context) use ($names) {
      if (! empty($editor_context->post)) {
        $block_categories = array_merge($block_categories, [
          [ 'slug' => $names['slug_single'], 'title' => $names['label_single'] ],
        ]);
      }

      return $block_categories;
    }, 10, 2);
  }

  // Register any taxonomies declared for this post type.
  private static function register_taxonomies()
  {
    TaxonomyRegistrar::register_taxonomies(static::$name, static::$props['taxonomies'] ?? [], function ($name, $names = []) {
      return static::generate_names($name, $names);
    });
  }

  // Register post meta fields for this type.
  private static function register_post_meta()
  {
    Meta::register_post_meta(static::$name, static::$props['meta'] ?? []);
  }

  // Treat core posts and pages as native types.
  private static function is_native_post_type()
  {
    if ((static::$name == 'post') or (static::$name == 'page')) {
      return true;
    } else {
      return false;
    }
  }

  // Activate role caps and rewrite rules for this type.
  public static function activate($force = false)
  {

    PostTypeRegistrar::activate_post_type(static::$names, $force);
  }

  // Reset activation markers for every registered post type.
  public static function activate_all()
  {
    foreach (self::$post_types as $post_type) {
      PostTypeRegistrar::reset_activation($post_type);
    }
  }
}

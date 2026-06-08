<?php

namespace Nonfiction\Theme;

use Nonfiction\Theme\Timber\Post;
use Timber;

class App {
  private static $path = __DIR__;
  private static $init = false;

  // Bootstrap Timber and base theme settings once.
  public static function init($path = false) {

    // Guard against double bootstrapping.
    if (static::$init) {
      return;
    }
    static::$init = true;

    // Default to the parent directory.
    static::$path = ($path) ? $path : dirname(__DIR__, 1);

    // Initialize Timber before setting template locations.
    \Timber\Timber::init();

    // Point Timber at the active template roots.
    Timber::$locations = [ 'templates', 'views' ];

    // Support HTML5 by default.
    \add_action('after_setup_theme', function () {
      \add_theme_support('html5', ['comment-form','comment-list','search-form','gallery','caption']);
    });
  }

  // Enqueue built assets when a manifest path is available.
  public static function enqueue($manifest_path = false, $path = false) {
    if ($manifest_path) {
      $path = ($path) ? $path : static::$path;
      $path = str_replace('//', '/', $path . '/' . $manifest_path);
      if (file_exists($path)) {
        new Enqueue($path);
      }
    }
  }

  // Import PHP files from a list of glob patterns.
  public static function import($resource_paths = [], $path = false) {
    $path = ($path) ? $path : static::$path;
    foreach ($resource_paths as $resource_path) {
      $resource_path = str_replace('//', '/', $path . '/' . $resource_path);
      foreach (glob($resource_path) as $file) {
        require_once $file;
      }
    }
  }

  // Set Timber's template locations from absolute paths.
  public static function views($locations = [ 'templates', 'views'], $path = false) {
    $path = ($path) ? $path : static::$path;
    Timber::$locations = array_map(fn ($d) => str_replace('//', '/', $path . '/' . $d), $locations);
  }

  // Flush registered post types, theme hooks, and object cache.
  public static function flush($force = false) {

    Post::activate_all();

    \do_action('nf/app/flush');
    \wp_cache_flush();
  }
}

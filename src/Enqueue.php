<?php

namespace Nonfiction\Theme;

class Enqueue {

  public $src = [];
  public $script_types = [];

  // Wire the manifest into the relevant frontend and admin queues.
  public function __construct( $manifest_path ) {
    $this->load( $manifest_path );
    $this->enable_script_types();
    $this->do_admin();
    $this->do_head();
    $this->do_body();
    $this->do_blocks();
    $this->do_editor();
  }


  // Load the manifest.json to get paths to compiled assets
  private function load( $manifest_path ) {

    $manifest = new ViteManifest( $manifest_path );

    $this->src['nf-head-css']   = $manifest->head->css   ?? null;
    $this->src['nf-head-js']    = $manifest->head->js    ?? null;

    $this->src['nf-body-css']   = $manifest->body->css   ?? null;
    $this->src['nf-body-js']    = $manifest->body->js    ?? null;

    $this->src['nf-blocks-css'] = $manifest->blocks->css ?? null;
    $this->src['nf-blocks-js']  = $manifest->blocks->js  ?? null;

    $this->src['nf-editor-css'] = $manifest->editor->css ?? null;
    $this->src['nf-editor-js']  = $manifest->editor->js  ?? null;

    $this->src['nf-admin-css']  = $manifest->admin->css  ?? null;
    $this->src['nf-admin-js']   = $manifest->admin->js   ?? null;

  }


  // <head> styles and scripts
  private function do_head() {
    add_action('wp_enqueue_scripts', function() {

      $this->enqueue([ 'handle' => 'nf-head-css' ]);
      $this->enqueue([ 'handle' => 'nf-head-js' ]);

    },100);
  }

  // <body> styles and scripts
  private function do_body() {
    add_action('wp_enqueue_scripts', function() {

      $this->enqueue([ 'handle' => 'nf-body-css' ]);
      $this->enqueue([ 'handle' => 'nf-body-js', 'in_footer' => true, 'type' => 'module' ]);

    },100);
  }


  // Admin styles and scripts
  private function do_admin() {
    add_action('admin_enqueue_scripts', function( $hook ) {

      // This breaks these admin pages, so skip them
      if ( in_array( $hook, ['nav-menus.php'] ) ) {
        return;
      }

      $this->enqueue([ 'handle' => 'nf-admin-css' ]);
      $this->enqueue([ 'handle' => 'nf-admin-js', 'in_footer' => true ]);

    },100);
  }


  // Blocks styles and scripts (both front-end and admin)
  private function do_blocks() {
    add_action('enqueue_block_assets', function() {

      $this->enqueue([ 'handle' => 'nf-blocks-css', 'deps' => ['wp-editor'] ]);
      $this->enqueue([ 'handle' => 'nf-blocks-js', 'in_footer' => true ]);

    },100);
  }


  // Editor (admin only)
  private function do_editor() {
    add_action('enqueue_block_assets', function() {

      if ( ! is_admin() ) {
        return;
      }

      $this->enqueue([ 'handle' => 'nf-editor-css', 'deps' => ['wp-edit-blocks'] ]);

    },100);

    add_action('enqueue_block_editor_assets', function() {

      $this->enqueue([ 'handle' => 'nf-editor-js', 'deps' => [
        'wp-blocks',
        'wp-components',
        'wp-editor',
        'wp-element',
        'wp-i18n',
        'wp-data', 
        'wp-api',
        'wp-edit-post', 
      ], 'type' => 'module' ]);

    },100);
  }


  // Honor custom script types when registering assets.
  private function enable_script_types() {
    add_filter( 'script_loader_tag', function( $tag, $handle, $src ) {
      $type = $this->script_types[ $handle ] ?? '';

      if ( empty( $type ) ) {
        return $tag;
      }

      if ( preg_match( '/\stype=(["\']).*?\1/', $tag ) ) {
        return preg_replace(
          '/\stype=(["\']).*?\1/',
          sprintf( ' type="%s"', esc_attr( $type ) ),
          $tag,
          1
        );
      }

      return preg_replace(
        '/<script\s/',
        sprintf( '<script type="%s" ', esc_attr( $type ) ),
        $tag,
        1
      );
    }, 20, 3 );
  }


  // Universal register/enqueue function
  private function register( $args=[], $enqueue = false ) {

    // Stop if these are missing
    $handle  = $args['handle'] ?? '';
    if ( empty($handle) ) return false;

    $src = $args['src'] ?? $this->src[$handle] ?? '';
    if ( empty($src) ) return false;

    if ( is_array( $src ) ) {
      foreach ( array_values( $src ) as $index => $item_src ) {
        $item_args = $args;
        $item_args['handle'] = $handle . '-' . $index;
        $item_args['src'] = $item_src;
        $this->register( $item_args, $enqueue );
      }

      return true;
    }

    // Extract variables from object array
    if ( ! preg_match('/^https?:\/\//', $src) ) {
      if (
        0 === strpos( $src, '/assets/' ) ||
        0 === strpos( $src, 'assets/' ) ||
        0 === strpos( $src, '/dist/' ) ||
        0 === strpos( $src, 'dist/' )
      ) {
        $src = Assets::asset_uri( ltrim( $src, '/' ) );
      } elseif ( 0 === strpos( $src, '/' ) ) {
        $src = home_url() . $src;
      } else {
        $src = get_theme_file_uri( ltrim( $src, '/' ) );
      }
    }
    $deps    = $args['deps'] ?? [];
    $ver     = $args['ver'] ?? null;

    // Adding CSS
    if ( substr_compare($src, '.css', -4, 4) === 0 ) {

      $media = $args['media'] ?? 'all';
      wp_register_style( $handle, $src, $deps, $ver, $media );
      if ( $enqueue ) wp_enqueue_style( $handle );

      // Adding JS
    } else {

      $in_footer = $args['in_footer'] ?? false;
      $type      = $args['type'] ?? '';
      wp_register_script( $handle, $src, $deps, $ver, $in_footer );

      if ( ! empty( $type ) ) {
        $this->script_types[ $handle ] = $type;
      }

      if ( $enqueue ) wp_enqueue_script( $handle );

    }
  }


  // Universal enqueue function
  private function enqueue( $args=[] ) {
    $this->register( $args, true );
  }

}

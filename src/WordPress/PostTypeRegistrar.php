<?php

namespace Nonfiction\Theme\WordPress;

class PostTypeRegistrar {

  // Map each WordPress role to the caps this registrar assigns.
  private static function roles_caps(): array {

    $all_caps = [
      'create',
      'edit',
      'edit_others',
      'publish',
      'read_private',
      'delete',
      'delete_private',
      'delete_published',
      'delete_others',
      'edit_private',
      'edit_published',
    ];

    $most_caps = [
      'edit',
      'publish',
      'delete',
      'delete_published',
      'edit_published',
    ];

    $few_caps = [
      'edit',
      'delete',
    ];

    return [
      'administrator' => $all_caps,
      'editor'        => $all_caps,
      'author'        => $most_caps,
      'contributor'   => $few_caps,
    ];

  }

  // Grant post-type caps once and refresh rewrites when needed.
  public static function activate_post_type( array $names, $force = false ) {

    // If this wordpress site isn't installed yet, bail
    if ( ! \is_blog_installed() ) return;

    // Post Type in single and plural
    $key_single = $names['key_single'];
    $key_plural = $names['key_plural'];

    // Check if this has been activated before
    $isnt_activated = ( '1' !== \get_option( "nf_{$key_single}_activated" ) ) ? true : false;

    // Activate if it hasn't, or if being forced to
    if ( ($isnt_activated) or ($force) ) {

      // Add default caps to default roles
      foreach( self::roles_caps() as $role_type => $cap_types ) {
        $role = \get_role( $role_type );

        if ( ! $role ) {
          continue;
        }

        foreach( $cap_types as $cap_type ) {
          $role->add_cap( "{$cap_type}_{$key_plural}" );
        }
      }

      // Update rewrite database
      \flush_rewrite_rules(false);

    }

    // Set this as activated now
    if ($isnt_activated) {
      \update_option( "nf_{$key_single}_activated", '1' );
    }

  }

  // Clear the activation flag so the type can be reprocessed.
  public static function reset_activation( $key_single ) {
    \update_option( "nf_{$key_single}_activated", '0' );
  }

  // Normalize core args, then register a custom post type.
  public static function register_custom_post_type( array $names, array $args = [], array $props = [] ) {

    $args = array_merge([
        'public'          => true,
        'show_ui'         => true,
        'show_in_menu'    => true,
        'supports'        => ['title', 'editor', 'thumbnail', 'revisions', 'custom-fields'],
        'has_archive'     => $props['has_archive'] ?? false,
        'query_var'       => $names['slug_single'],
        'capability_type' => [$names['key_single'], $names['key_plural']],
        'map_meta_cap'    => true,
        'show_in_rest'    => true,
        'rest_base'       => $names['slug_plural'],
        'taxonomies'      => array_keys( $props['taxonomies'] ?? [] ),

        'rewrite' => [
        ],

      ], $args);

    \register_post_type( $names['key_single'], self::filter_core_post_type_args( $args, $names ) );

    foreach ( $args['unsupports'] ?? [] as $feature ) {
      \remove_post_type_support( $names['key_single'], $feature );
    }

  }

  // Apply template settings to an existing core post type.
  public static function customize_native_post_type( $post_type, array $args = [] ) {

    $post_type_object = \get_post_type_object( $post_type );

    if ( $post_type_object ) {
      $post_type_object->template = $args['template'] ?? null;
      $post_type_object->template_lock = $args['template_lock'] ?? false;
    }

    foreach ( $args['unsupports'] ?? [] as $feature ) {
      \remove_post_type_support( $post_type, $feature );
    }

  }

  // Strip unsupported custom keys before passing args to WordPress.
  private static function filter_core_post_type_args( $args, $names ) {
    unset(
      $args['site_sortables'],
      $args['site_filters'],
      $args['admin_cols'],
      $args['dashboard_glance'],
      $args['enter_title_here'],
      $args['featured_image'],
      $args['quick_edit'],
      $args['block_editor'],
      $args['show_in_feed']
    );

    if ( isset( $args['rewrite'] ) && is_array( $args['rewrite'] ) && array_key_exists( 'permastruct', $args['rewrite'] ) ) {
      unset( $args['rewrite']['permastruct'] );

      if ( $args['rewrite'] === [] ) {
        $args['rewrite'] = true;
      }
    }

    $args['labels'] = array_replace( self::default_post_type_labels( $names ), $args['labels'] ?? [] );

    return $args;
  }

  // Merge the default labels with any custom labels.
  private static function default_post_type_labels( $names ) {
    return [
      'name' => $names['label_plural'],
      'singular_name' => $names['label_single'],
      'add_new' => 'Add ' . $names['label_single'],
      'add_new_item' => 'Add New ' . $names['label_single'],
      'edit_item' => 'Edit ' . $names['label_single'],
      'new_item' => 'New ' . $names['label_single'],
      'view_item' => 'View ' . $names['label_single'],
      'view_items' => 'View ' . $names['label_plural'],
      'search_items' => 'Search ' . $names['label_plural'],
      'not_found' => 'No ' . $names['label_plural'] . ' found',
      'not_found_in_trash' => 'No ' . $names['label_plural'] . ' found in Trash',
      'parent_item_colon' => 'Parent ' . $names['label_single'] . ':',
      'all_items' => 'All ' . $names['label_plural'],
      'archives' => $names['label_single'] . ' Archives',
      'attributes' => $names['label_single'] . ' Attributes',
      'insert_into_item' => 'Insert into ' . $names['label_single'],
      'uploaded_to_this_item' => 'Uploaded to this ' . $names['label_single'],
      'featured_image' => 'Featured image',
      'set_featured_image' => 'Set featured image',
      'remove_featured_image' => 'Remove featured image',
      'use_featured_image' => 'Use as featured image',
      'menu_name' => $names['label_plural'],
      'filter_items_list' => 'Filter ' . $names['label_plural'] . ' list',
      'filter_by_date' => 'Filter by date',
      'items_list_navigation' => $names['label_plural'] . ' list navigation',
      'items_list' => $names['label_plural'] . ' list',
      'item_published' => $names['label_single'] . ' published.',
      'item_published_privately' => $names['label_single'] . ' published privately.',
      'item_reverted_to_draft' => $names['label_single'] . ' reverted to draft.',
      'item_scheduled' => $names['label_single'] . ' scheduled.',
      'item_updated' => $names['label_single'] . ' updated.',
      'item_link' => $names['label_single'] . ' Link',
      'item_link_description' => 'A link to a ' . $names['label_single'] . '.',
      'name_admin_bar' => $names['label_single'],
    ];
  }

}

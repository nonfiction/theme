<?php

namespace Nonfiction\Theme\WordPress;

class TaxonomyRegistrar {
  // Register shared taxonomies or attach core ones to the post type.
  public static function register_taxonomies($post_type, array $taxonomies, callable $name_generator) {
    foreach ($taxonomies as $name => $args) {
      if (($name == 'category') || ($name == 'tag')) {
        \register_taxonomy_for_object_type($name, $post_type);

        continue;
      }

      $tax_args = (is_array($args)) ? $args : [];
      $tax_names = $name_generator($name, $tax_args['names'] ?? []);
      $tax_name = $tax_names['key_single']; // update $name arg in case it got modified

      // Default taxonomy args before filtering out custom-only keys.
      $args = array_merge([
        'public' => true,
        'show_ui' => true,
        'hierarchical' => true,
        'query_var' => $tax_names['slug_single'],
        'exclusive' => false,
        'allow_hierarchy' => false,
        'meta_box' => null,
        'dashboard_glance' => false,
        'checked_ontop' => null,
        'admin_cols' => null,
        'required' => false,

      ], $tax_args);

      \register_taxonomy($tax_name, $post_type, self::filter_core_taxonomy_args($args, $tax_names));
    }
  }

  // Strip unsupported custom taxonomy args before core registration.
  private static function filter_core_taxonomy_args($args, $names) {
    unset(
      $args['exclusive'],
      $args['allow_hierarchy'],
      $args['meta_box'],
      $args['dashboard_glance'],
      $args['checked_ontop'],
      $args['admin_cols'],
      $args['required'],
      $args['site_sortables'],
      $args['site_filters'],
    );

    $args['rewrite'] = $args['rewrite'] ?? [ 'slug' => $names['slug_plural'] ];
    $args['labels'] = array_replace(self::default_taxonomy_labels($names), $args['labels'] ?? []);

    return $args;
  }

  // Merge default labels with any custom taxonomy labels.
  private static function default_taxonomy_labels($names) {
    return [
      'name' => $names['label_plural'],
      'singular_name' => $names['label_single'],
      'search_items' => 'Search ' . $names['label_plural'],
      'popular_items' => 'Popular ' . $names['label_plural'],
      'all_items' => 'All ' . $names['label_plural'],
      'parent_item' => 'Parent ' . $names['label_single'],
      'parent_item_colon' => 'Parent ' . $names['label_single'] . ':',
      'edit_item' => 'Edit ' . $names['label_single'],
      'view_item' => 'View ' . $names['label_single'],
      'update_item' => 'Update ' . $names['label_single'],
      'add_new_item' => 'Add New ' . $names['label_single'],
      'new_item_name' => 'New ' . $names['label_single'] . ' Name',
      'separate_items_with_commas' => 'Separate ' . $names['label_plural'] . ' with commas',
      'add_or_remove_items' => 'Add or remove ' . $names['label_plural'],
      'choose_from_most_used' => 'Choose from the most used ' . $names['label_plural'],
      'not_found' => 'No ' . $names['label_plural'] . ' found',
      'no_terms' => 'No ' . $names['label_plural'],
      'filter_by_item' => 'Filter by ' . $names['label_single'],
      'items_list_navigation' => $names['label_plural'] . ' list navigation',
      'items_list' => $names['label_plural'] . ' list',
      'back_to_items' => 'Back to ' . $names['label_plural'],
      'item_link' => $names['label_single'] . ' Link',
      'item_link_description' => 'A link to a ' . $names['label_single'] . '.',
      'menu_name' => $names['label_plural'],
      'name_admin_bar' => $names['label_plural'],
    ];
  }
}

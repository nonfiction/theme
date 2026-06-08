<?php

namespace Nonfiction\Theme\WordPress;

use function Nonfiction\Theme\titleize;
use function Nonfiction\Theme\underscore;

class Meta
{
  // Register per-post-type meta with sane defaults and caps.
  public static function register_post_meta($post_type, array $meta)
  {
    foreach ($meta as $meta_key => $meta_args) {
      $meta_key = underscore($meta_key);
      $meta_args = (is_array($meta_args)) ? $meta_args : [];

      \register_post_meta($post_type, $meta_key, array_merge([

        'show_in_rest' => true,
        'type' => 'string', // string, boolean, integer, number, array, object
        'single' => true,

        'sanitize_callback' => function ($meta_value, $meta_key, $meta_type) {
          switch ($meta_type) {
            case 'string':
              return (string) $meta_value;
            case 'boolean':
              return (bool) $meta_value;
            case 'integer':
              return (int) $meta_value;
            case 'number':
              return (float) $meta_value;
            case 'array':
              return (array) $meta_value;
            case 'object':
              return (object) $meta_value;
          }

          return $meta_value;
        },

        'auth_callback' => function ($allowed, $meta_key, $post_id, $user_id, $cap, $caps) {
          $post = \get_post($post_id);
          if (! $post instanceof \WP_Post) {
            return false;
          }

          $cpt = \get_post_type_object($post->post_type);
          if (! $cpt || empty($cpt->cap->edit_posts)) {
            return false;
          }

          return \current_user_can($cpt->cap->edit_posts);
        },

      ], $meta_args));
    }
  }

  // Register block-scoped meta when the config provides a key.
  public static function register_block_post_meta(array $meta)
  {
    foreach ($meta as $meta_args) {
      $meta_args = (is_array($meta_args)) ? $meta_args : [];

      $post_type = $meta_args['post_type'] ?? '';
      unset($meta_args['post_type']);

      $meta_key = $meta_args['key'] ?? false;
      unset($meta_args['key']);

      if ($meta_key) {
        \register_post_meta($post_type, $meta_key, array_merge([

          'show_in_rest' => true,
          'type' => 'string', // string, boolean, integer, number, array, object
          'single' => true,

        ], $meta_args));
      }
    }
  }

  // Build CMB2 boxes from the configured metabox definitions.
  public static function register_custom_meta_boxes(array $names, array $metaboxes)
  {
    if (! function_exists('new_cmb2_box')) {
      return;
    }

    \add_action('cmb2_admin_init', function () use ($names, $metaboxes) {
      foreach ($metaboxes as $metabox) {
        $metabox['title'] ??= $names['label_single'];
        $metabox['context'] ??= 'side';
        $metabox['id'] ??= $names['key_single'] . '_' . $metabox['context'];
        $metabox['fields'] ??= [];

        if (count($metabox['fields']) > 0) {
          $cmb = \new_cmb2_box([
            'id' => underscore($metabox['id']),
            'title' => $metabox['title'],
            'object_types' => [ $names['key_single'] ],
            'context' => $metabox['context'],
            'priority' => $metabox['priority'] ?? 'high',
            'show_names' => $metabox['true'] ?? true,
          ]);

          foreach ($metabox['fields'] as $field) {
            $field['id'] ??= underscore($field['name']);
            $field['name'] ??= titleize($field['id']);
            $cmb->add_field($field);
          }
        }
      }
    }, 20);
  }
}

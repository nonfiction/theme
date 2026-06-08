<?php

// LSP-only global WordPress/CMB2 stubs, not package source.

if (! class_exists('WP_Post')) {
  class WP_Post
  {
    public $post_type;
  }
}

if (! class_exists('WP_Role')) {
  class WP_Role
  {
    public function add_cap($cap)
    {
    }
  }
}

if (! class_exists('WP_Post_Type')) {
  class WP_Post_Type
  {
    public $cap;
    public $template;
    public $template_lock;
  }
}

if (! class_exists('CMB2_Box')) {
  class CMB2_Box
  {
    public function add_field($field)
    {
    }
  }
}

if (! function_exists('add_action')) {
  function add_action($hook_name, $callback, $priority = 10, $accepted_args = 1)
  {
  }
}

if (! function_exists('add_filter')) {
  function add_filter($hook_name, $callback, $priority = 10, $accepted_args = 1)
  {
  }
}

if (! function_exists('add_theme_support')) {
  function add_theme_support($feature, ...$args)
  {
  }
}

if (! function_exists('apply_filters')) {
  function apply_filters($hook_name, $value, ...$args)
  {
    return $value;
  }
}

if (! function_exists('current_user_can')) {
  function current_user_can($capability, ...$args)
  {
    return false;
  }
}

if (! function_exists('do_action')) {
  function do_action($hook_name, ...$arg)
  {
  }
}

if (! function_exists('esc_attr')) {
  function esc_attr($text)
  {
    return $text;
  }
}

if (! function_exists('get_option')) {
  function get_option($option, $default = false)
  {
    return $default;
  }
}

if (! function_exists('get_post')) {
  function get_post($post = null, $output = 'OBJECT', $filter = 'raw')
  {
    return null;
  }
}

if (! function_exists('get_post_type_object')) {
  function get_post_type_object($post_type)
  {
    return null;
  }
}

if (! function_exists('get_role')) {
  function get_role($role)
  {
    return null;
  }
}

if (! function_exists('get_template_directory')) {
  function get_template_directory()
  {
    return '';
  }
}

if (! function_exists('get_template_directory_uri')) {
  function get_template_directory_uri()
  {
    return '';
  }
}

if (! function_exists('get_theme_file_uri')) {
  function get_theme_file_uri($file = '')
  {
    return $file;
  }
}

if (! function_exists('home_url')) {
  function home_url($path = '', $scheme = null)
  {
    return $path;
  }
}

if (! function_exists('is_admin')) {
  function is_admin()
  {
    return false;
  }
}

if (! function_exists('is_blog_installed')) {
  function is_blog_installed()
  {
    return true;
  }
}

if (! function_exists('is_feed')) {
  function is_feed($feeds = '')
  {
    return false;
  }
}

if (! function_exists('mime_content_type')) {
  function mime_content_type($filename)
  {
    return '';
  }
}

if (! function_exists('network_home_url')) {
  function network_home_url($path = '', $scheme = null)
  {
    return $path;
  }
}

if (! function_exists('new_cmb2_box')) {
  function new_cmb2_box(array $meta_box)
  {
    return new CMB2_Box();
  }
}

if (! function_exists('register_block_type')) {
  function register_block_type($block_type, $args = [])
  {
    return true;
  }
}

if (! function_exists('register_post_meta')) {
  function register_post_meta($post_type, $meta_key, array $args)
  {
    return true;
  }
}

if (! function_exists('register_post_type')) {
  function register_post_type($post_type, $args = [])
  {
    return null;
  }
}

if (! function_exists('register_taxonomy')) {
  function register_taxonomy($taxonomy, $object_type, $args = [])
  {
    return null;
  }
}

if (! function_exists('register_taxonomy_for_object_type')) {
  function register_taxonomy_for_object_type($taxonomy, $object_type)
  {
    return true;
  }
}

if (! function_exists('remove_post_type_support')) {
  function remove_post_type_support($post_type, $feature)
  {
  }
}

if (! function_exists('sanitize_text_field')) {
  function sanitize_text_field($str)
  {
    return $str;
  }
}

if (! function_exists('status_header')) {
  function status_header($code, $description = '')
  {
  }
}

if (! function_exists('untrailingslashit')) {
  function untrailingslashit($string)
  {
    return rtrim($string, '/\\');
  }
}

if (! function_exists('update_option')) {
  function update_option($option, $value, $autoload = null)
  {
    return true;
  }
}

if (! function_exists('wp_cache_flush')) {
  function wp_cache_flush()
  {
    return true;
  }
}

if (! function_exists('wp_check_filetype')) {
  function wp_check_filetype($filename, $mimes = null)
  {
    return ['ext' => '', 'type' => ''];
  }
}

if (! function_exists('wp_enqueue_script')) {
  function wp_enqueue_script($handle, $src = '', $deps = [], $ver = false, $args = [])
  {
  }
}

if (! function_exists('wp_enqueue_style')) {
  function wp_enqueue_style($handle, $src = '', $deps = [], $ver = false, $media = 'all')
  {
  }
}

if (! function_exists('wp_make_link_relative')) {
  function wp_make_link_relative($link)
  {
    return $link;
  }
}

if (! function_exists('wp_parse_url')) {
  function wp_parse_url($url, $component = -1)
  {
    return parse_url($url, $component);
  }
}

if (! function_exists('wp_register_script')) {
  function wp_register_script($handle, $src, $deps = [], $ver = false, $args = [])
  {
    return true;
  }
}

if (! function_exists('wp_register_style')) {
  function wp_register_style($handle, $src, $deps = [], $ver = false, $media = 'all')
  {
    return true;
  }
}

if (! function_exists('wp_unslash')) {
  function wp_unslash($value)
  {
    return $value;
  }
}

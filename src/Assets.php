<?php

namespace Nonfiction\Theme;

class Assets {
  // Build a theme asset URL from a relative path.
  public static function asset_uri($path = '') {
    $path = ltrim((string) $path, '/');
    $base = \untrailingslashit(\get_template_directory_uri());

    return $path === '' ? $base : $base . '/' . $path;
  }

  // Build a theme asset filesystem path from a relative path.
  public static function asset_path($path = '') {
    $path = ltrim((string) $path, '/');
    $base = \untrailingslashit(\get_template_directory());

    return $path === '' ? $base : $base . '/' . $path;
  }

  // Normalize theme-relative asset URLs without touching content or external links.
  public static function normalize_asset_url($url) {
    if (! is_string($url)) {
      return $url;
    }

    $url = trim($url);

    if ($url === '') {
      return $url;
    }

    if (preg_match('#^(?:[a-z][a-z0-9+.-]*:)?//#i', $url) || 0 === strpos($url, 'data:')) {
      return $url;
    }

    $relative = ltrim($url, '/');

    if (0 === strpos($relative, 'assets/') || 0 === strpos($relative, 'app/views/img/') || $relative === 'app/views/img') {
      return static::asset_uri($relative);
    }

    return $url;
  }

  // Recursively normalize asset URLs in arrays and scalars.
  public static function normalize_asset_value($value) {
    if (is_array($value)) {
      foreach ($value as $key => $item) {
        $value[ $key ] = static::normalize_asset_value($item);
      }

      return $value;
    }

    return static::normalize_asset_url($value);
  }
}

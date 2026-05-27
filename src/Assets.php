<?php

namespace Nonfiction\Theme;

class Assets {

  // Build a theme asset URL from a relative path.
  public static function asset_uri( $path = '' ) {
    $path = ltrim( (string) $path, '/' );
    $base = untrailingslashit( get_template_directory_uri() );

    return $path === '' ? $base : $base . '/' . $path;
  }

  // Build a theme asset filesystem path from a relative path.
  public static function asset_path( $path = '' ) {
    $path = ltrim( (string) $path, '/' );
    $base = untrailingslashit( get_template_directory() );

    return $path === '' ? $base : $base . '/' . $path;
  }

  // Point seed media URLs at the theme's seed uploads directory.
  public static function seed_asset_uri( $path = '' ) {
    $path = ltrim( (string) $path, '/' );

    return static::asset_uri( $path === '' ? 'seed/uploads' : 'seed/uploads/' . $path );
  }

  // Build the filesystem path for seeded uploads.
  public static function seed_asset_path( $path = '' ) {
    $path = ltrim( (string) $path, '/' );

    return static::asset_path( $path === '' ? 'seed/uploads' : 'seed/uploads/' . $path );
  }

  // Extract a seed-relative path from a legacy upload URL.
  public static function seed_relative_asset_path( $url ) {
    if ( ! is_string( $url ) ) {
      return null;
    }

    $url = trim( $url );

    if ( $url === '' ) {
      return null;
    }

    $path = wp_parse_url( $url, PHP_URL_PATH );

    if ( ! is_string( $path ) || $path === '' ) {
      $path = 0 === strpos( $url, '/' ) ? $url : '';
    }

    if ( $path === '' ) {
      return null;
    }

    if ( 0 === strpos( $path, '/content/uploads/' ) ) {
      $relative = substr( $path, strlen( '/content/uploads/' ) );
    } elseif ( 0 === strpos( $path, '/upload/' ) ) {
      $relative = 'upload/' . substr( $path, strlen( '/upload/' ) );
    } elseif ( 0 === strpos( $path, '/seed/uploads/' ) ) {
      $relative = substr( $path, strlen( '/seed/uploads/' ) );
    } else {
      return null;
    }

    if ( ! is_string( $relative ) || $relative === '' ) {
      return null;
    }

    return $relative;
  }

  // Rewrite legacy seed media URLs to the active theme path.
  public static function normalize_seed_media_url( $url ) {
    $relative = static::seed_relative_asset_path( $url );

    if ( ! is_string( $relative ) || $relative === '' ) {
      return $url;
    }

    $query = wp_parse_url( $url, PHP_URL_QUERY );
    $fragment = wp_parse_url( $url, PHP_URL_FRAGMENT );
    $normalized = static::seed_asset_uri( $relative );

    if ( is_string( $query ) && $query !== '' ) {
      $normalized .= '?' . $query;
    }

    if ( is_string( $fragment ) && $fragment !== '' ) {
      $normalized .= '#' . $fragment;
    }

    return $normalized;
  }

  // Serve seeded uploads directly from the theme when requested.
  public static function maybe_serve_seed_asset() {
    $request_uri = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( (string) $_SERVER['REQUEST_URI'] ) : '';
    $relative = static::seed_relative_asset_path( $request_uri );

    if ( ! is_string( $relative ) || $relative === '' ) {
      return;
    }

    $file = static::seed_asset_path( $relative );

    if ( ! is_file( $file ) ) {
      return;
    }

    $type = wp_check_filetype( $file )['type'] ?? '';

    if ( $type === '' && function_exists( 'mime_content_type' ) ) {
      $type = (string) mime_content_type( $file );
    }

    status_header( 200 );
    header( 'Content-Type: ' . ( $type !== '' ? $type : 'application/octet-stream' ) );
    header( 'Content-Length: ' . (string) filesize( $file ) );
    header( 'Cache-Control: public, max-age=3600' );

    readfile( $file );
    exit;
  }

  // Rewrite seed media URLs inside rendered HTML content.
  public static function normalize_seed_media_html( $content ) {
    if ( ! is_string( $content ) || $content === '' ) {
      return $content;
    }

    return preg_replace_callback(
      "#(?P<prefix>\\b(?:src|href)=([\"']))(?P<url>[^\"']+)(?P<suffix>\\2)#i",
      function( $matches ) {
        $normalized = static::normalize_seed_media_url( $matches['url'] );
        return $matches['prefix'] . $normalized . $matches['suffix'];
      },
      $content
    );
  }

  // Normalize theme-relative asset URLs without touching external links.
  public static function normalize_asset_url( $url ) {
    if ( ! is_string( $url ) ) {
      return $url;
    }

    $url = trim( $url );

    if ( $url === '' ) {
      return $url;
    }

    $seed_url = static::normalize_seed_media_url( $url );

    if ( $seed_url !== $url ) {
      return $seed_url;
    }

    if ( preg_match( '#^(?:[a-z][a-z0-9+.-]*:)?//#i', $url ) || 0 === strpos( $url, 'data:' ) ) {
      return $url;
    }

    $relative = ltrim( $url, '/' );

    if ( 0 === strpos( $relative, 'assets/' ) || 0 === strpos( $relative, 'app/views/img/' ) || $relative === 'app/views/img' ) {
      return static::asset_uri( $relative );
    }

    return $url;
  }

  // Recursively normalize asset URLs in arrays and scalars.
  public static function normalize_asset_value( $value ) {
    if ( is_array( $value ) ) {
      foreach ( $value as $key => $item ) {
        $value[ $key ] = static::normalize_asset_value( $item );
      }

      return $value;
    }

    return static::normalize_asset_url( $value );
  }
}

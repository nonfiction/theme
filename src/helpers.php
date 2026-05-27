<?php
namespace Nonfiction\Theme;

use ICanBoogie\StaticInflector;


/* Utility Functions */

// Alias to the inflector pluralize helper.
function pluralize(   ...$args ) { return StaticInflector::pluralize(   ...$args ); }
// Alias to the inflector singularize helper.
function singularize( ...$args ) { return StaticInflector::singularize( ...$args ); }
// Alias to the inflector underscore helper.
function underscore(  ...$args ) { return StaticInflector::underscore(  ...$args ); }
// Alias to the inflector hyphenate helper.
function hyphenate(   ...$args ) { return StaticInflector::hyphenate(   ...$args ); }
// Alias to the inflector camelize helper.
function camelize(    ...$args ) { return StaticInflector::camelize(    ...$args ); }
// Alias to the inflector humanize helper.
function humanize(    ...$args ) { return StaticInflector::humanize(    ...$args ); }
// Alias to the inflector titleize helper.
function titleize(    ...$args ) { return StaticInflector::titleize(    ...$args ); }

// Custom pluralize inflection to ensure uniqueness.
function unique_pluralize($word, $word_to_compare = false) {

  // If no comparison word is provided, use the word being pluralized.
  $word_to_compare = ($word_to_compare) ? $word_to_compare : $word;

  // Pluralize the word.
  $word = pluralize($word);

  // If the word matches, add an s or es.
  if ( $word == $word_to_compare ) {
    $word .= ( 's' == substr($word, -1) ) ? 'es' : 's';
  }

  return $word;
}

/**
 * If you have a callback you want to run on multiple actions, pass them here.
 *
 * @param array $tags
 * @param callable $function_to_add
 * @param int $priority
 * @param int $accepted_args
 */
function add_actions( array $tags, $function_to_add, $priority = 10, $accepted_args = 1 ) {
  foreach ( $tags as $tag ) {
    add_action( $tag, $function_to_add, $priority, $accepted_args );
  }
}

// Register matching authenticated and public AJAX handlers.
function add_ajax_action( $tag, $function_to_add, $priority = 10, $accepted_args = 1 ) {
  add_action( "wp_ajax_{$tag}", $function_to_add, $priority, $accepted_args );
  add_action( "wp_ajax_nopriv_{$tag}", $function_to_add, $priority, $accepted_args );
}


// Recursively sanitize mixed request values into scalars.
function sanitize_param($param) {

  // Recurse into arrays.
  if ( is_array($param) ) {
    foreach($param as $key => $val) {
      $param[$key] = sanitize_param($val);
    }

  } else {

    // Sanitize the input as a string.
    $param = sanitize_text_field( $param );

    // Empty strings or the word "null" become null.
    if ( ( $param == '' ) || ( strtolower($param) == 'null' ) ) {
      $param = null;

    // The word "false" becomes boolean false.
    } elseif ( strtolower($param) == 'false' ){
      $param = false;

    // The word "true" becomes boolean true.
    } elseif ( strtolower($param) == 'true' ) {
      $param = true;

    // Numeric strings become numbers.
    } elseif ( is_numeric($param) ) {
      $param = $param + 0;
    }

  }

  return $param;

}

// Read a request parameter and normalize its type.
function get_param($name, $default = null) {
  return sanitize_param( $_REQUEST[$name] ?? $default );
}

// Convert an associative array to a comma-separated key:value string.
function csv($input = []) {
  $output = [];
  foreach( $input as $key => $val) {
    if ($val) {
      $output[] = "{$key}:{$val}";
    }
  }
  return join(',', $output);
}

// Check a date string against a specific format.
function validate_date($date, $format = 'Y-m-d') {
  $d = \DateTime::createFromFormat($format, $date);
  return $d && $d->format($format) === $date;
}

// Read a JSON file or return the input unchanged if it is already an array.
function import( $path ) {
  if ( is_array($path) ) return $path;

  if ( !is_string($path) ) return [];

  if ( !file_exists($path) ) return [];

  if ( strpos($path, '.json') !== false ) {
    return json_decode( file_get_contents($path), true );
  }

  return [];
}


// Map values to keyed pairs and optionally group them.
function map(array $list, callable $cb, $group_by = false) {

  $all = [];
  foreach (($list ?? []) as $key => $val) {

    // Prefer an explicit ID in the value, then fall back to the array key.
    $id = $val['id'] ?? $val['_id'] ?? $val['key'] ?? $val['_key'] ?? $key;

    // Get the pair from the callback.
    $pair = $cb( $id, $val ) ?? [ $key => $val ];
    if (!$pair) continue;
    $pair_key = array_key_first( $pair );

    // Optionally group pairs by one of the keys.
    if ($group_by) {
      $group = $val[$group_by];
      $all[$group] ??= [];
      $all[$group][$pair_key] = $pair[$pair_key];

    // Otherwise, return the pair keyed by its first element.
    } else {
      $all[$pair_key] = $pair[$pair_key];
    }

  }
  return $all;

}


// Recursively change all keys from camelCase to camel_case.
function underscore_keys( $old = [] ) {
  if ( ! is_array($old) ) return $old;
  $new = [];
  foreach( $old as $key => $val ) {
    $val = ( is_array($val) ) ? underscore_keys( $val ) : $val;
    $key = ( is_numeric($key) ) ? $key : underscore( $key );
    $new[$key] = $val;
  }
  return $new;
}


// Like empty(), but numeric values (including 0) count as non-empty.
function is_empty( $value = false ) {
  if ( is_numeric($value) ) return false;
  if ( empty($value) ) return true;
  return false;
}

// Invert is_empty() for clearer conditions.
function isnt_empty( $value = false ) {
  return (! is_empty($value) );
}

// Merge two JSON sources or arrays.
function merge( $array_or_file_1 = [], $array_or_file_2 = [] ) {
  return array_merge( import($array_or_file_1), import($array_or_file_2) );
}

// Convert an associative array into a CSS string for a style attribute.
function css($array) {
  return implode('; ', array_map(
    function($k, $v) { return $k . ': ' . $v; },
      array_keys($array),
      array_values($array)
    )
  );
}


// Return true when a string starts with the given prefix.
function starts_with( $haystack, $needle ) {
  $length = strlen( $needle );
  return substr( $haystack, 0, $length ) === $needle;
}

// Return true when a string ends with the given suffix.
function ends_with( $haystack, $needle ) {
  $length = strlen( $needle );
  if( !$length ) {
    return true;
  }
  return substr( $haystack, -$length ) === $needle;
}

// Return a relative URL when it points at the current site.
function make_link_relative($input) {

  // Will be comparing input to home url.
  $site_url = parse_url(network_home_url());

  // Get URL from input.
  $url = parse_url($input);
  $url['scheme'] = $url['scheme'] ?? $site_url['scheme'];

  // Leave feeds alone.
  if (is_feed()) return $input;

  // Ensure it's a valid url.
  if (!isset($url['host']) || !isset($url['path'])) return $input;

  // See if input url matches properly with home url.
  $hosts_match = $site_url['host'] === $url['host'];
  $schemes_match = $site_url['scheme'] === $url['scheme'];
  $ports_exist = isset($site_url['port']) && isset($url['port']);
  $ports_match = ($ports_exist) ? $site_url['port'] === $url['port'] : true;

  // If so, return the relative version.
  if ($hosts_match && $schemes_match && $ports_match) {
    return wp_make_link_relative($input);

    // If not, return as-is.
  } else {
    return $input;
  }

}


// Strip all whitespace from a string.
function nosp( $string ) {
  return preg_replace( '/\s+/', '', strval($string) );
}

// Display the value in QueryMonitor or to the screen.
function log($value, $var_dump = false) {
  do_action( 'qm/debug', $value );
  if ($var_dump) {
    echo "<br>";
    echo "<hr>";
    echo "<pre>";
    var_dump($value);
    echo "</pre>";
    echo "<hr>";
    echo "<br>";
  }
}

// Dump a raw value directly to the page.
function dump($value) {
  echo "<br>";
  echo "<hr>";
  echo "<pre>";
  var_dump($value);
  echo "</pre>";
  echo "<hr>";
  echo "<br>";
}

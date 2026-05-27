<?php

namespace Nonfiction\Theme\WordPress;

class BlockTypeRegistrar {

  private static $block_types = [];

  // Skip re-registering core blocks through the custom registry.
  public static function is_core_block_type( $name ) {
    return in_array( $name, self::$core_block_types, true );
  }

  // Register custom blocks once and ignore duplicates.
  public static function register_block_type( $name, array $args = [] ) {
    if ( empty( $name ) ) {
      return false;
    }

    if ( isset( self::$block_types[ $name ] ) ) {
      return false;
    }

    self::$block_types[ $name ] = $name;

    if ( self::is_core_block_type( $name ) ) {
      return true;
    }

    return \call_user_func( 'register_block_type', $name, $args );
  }

  private static $core_block_types = [

    /* [COMMON] */
    'core/image',
    'core/paragraph',
    'core/heading',
    'core/gallery',
    'core/list',
    'core/quote',
    'core/audio',
    'core/cover',
    'core/file',
    'core/video',

    /* [FORMATTING] */
    'core/code',
    'core/classic',
    'core/html',
    'core/preformatted',
    'core/pullquote',
    'core/table',
    'core/verse',

    /* [LAYOUT] */
    'core/page-break',
    'core/buttons',
    'core/columns',
    'core/group',
    'core/media-text',
    'core/more',
    'core/separator',
    'core/spacer',

    /* [WIDGETS] */
    'core/archives',
    'core/shortcode',
    'core/calendar',
    'core/categories',
    'core/latest-posts',
    'core/rss',
    'core/search',
    'core/social-icons',
    'core/tag-cloud',

    /* [EMBEDS] */
    'core/embed',
    'core-embed/twitter',
    'core-embed/youtube',
    'core-embed/facebook',
    'core-embed/instagram',
    'core-embed/wordpress',
    'core-embed/soundcloud',
    'core-embed/spotify',
    'core-embed/flickr',
    'core-embed/vimeo',
    'core-embed/animoto',
    'core-embed/cloudup',
    'core-embed/crowdsignal',
    'core-embed/dailymotion',
    'core-embed/hulu',
    'core-embed/imgur',
    'core-embed/issuu',
    'core-embed/kickstarter',
    'core-embed/meetup-com',
    'core-embed/mixcloud',
    'core-embed/reddit',
    'core-embed/reverbnation',
    'core-embed/screencast',
    'core-embed/scribd',
    'core-embed/slideshare',
    'core-embed/smugmug',
    'core-embed/speaker-deck',
    'core-embed/tiktok',
    'core-embed/ted',
    'core-embed/tumblr',
    'core-embed/videopress',
    'core-embed/wordpress-tv',
    'core-embed/amazon-kindle',
  ];

}

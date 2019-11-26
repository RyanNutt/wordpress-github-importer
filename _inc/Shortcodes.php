<?php

namespace Aelora\WordPress\GitHub;

Shortcodes::init();

class Shortcodes {

  public static function init() {
    add_shortcode( 'github_file', [ self::class, 'github_file' ] );
  }

  public static function github_file( $args, $content = '' ) {
    global $post;
    $cached_meta = get_post_meta( $post->ID, '_github_importer', true );

    if ( empty( $cached_meta ) ) {
      $cached_meta = [];
    }
    else {
      $cached_meta = json_decode( $cached_meta, true );
    }
    try {
      $gf = new GitHubFile( $content );
    }
    catch ( \Exception $ex ) {
      return 'Unable to parse GitHub URL';
    }

    if ( empty( $cached_meta[ $gf->api_url() ] ) ) {
      $cached_meta[ $gf->api_url() ] = [
          'updated' => false,
          'body' => false,
          'status' => false
      ];
    }



    //return 'done'; 
    $api_results = $gf->get( Import::get_option( 'github_token', '' ) );

    $body = json_decode( $api_results[ 'body' ], true );
    if ( $body === false ) {
      $body = $api_results[ 'body' ];
    }

    $cached_meta[ $gf->api_url() ] = [
        'updated' => current_time( 'timestamp' ),
        'status' => isset( $api_results[ 'response' ][ 'code' ] ) ? $api_results[ 'response' ][ 'code' ] : -1,
        'body' => $body
    ];
    
    return Output::to_html( $cached_meta[ $gf->api_url() ][ 'body' ], $gf );
  }

}

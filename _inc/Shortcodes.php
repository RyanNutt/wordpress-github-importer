<?php

namespace Aelora\WordPress\GitHub;

Shortcodes::init();

/**
 * Shortcodes and actions used by the plugin
 */
class Shortcodes {

  public static function init() {
    add_shortcode( 'github_file', [ self::class, 'github_file' ] );

    add_action( 'save_post', [ self::class, 'clear_cache' ], 99 );

    add_action( 'template_redirect', [ self::class, 'webhook' ], 2 );
  }

  /**
   * Look for POST requests and see if it's a webhook request from
   * GitHub. If it is, clear out the cache for either the current
   * post or all posts depending on settings. 
   */
  public static function webhook() {
    if ( is_singular() && $_SERVER[ 'REQUEST_METHOD' ] == 'POST' ) {
      global $post; 
      $headers = getallheaders();
      if ( array_key_exists( 'X-GitHub-Event', $headers ) && array_key_exists( 'X-GitHub-Delivery', $headers ) ) {
        /* Has the right headers, go ahead and process */
        $secret = Import::get_option( 'webhook_secret', '' );
        if ( array_key_exists( 'X-GitHub-Signature', $headers ) || ! empty( $secret ) ) {
          /* Check the signature if it exists or if it's expected */
          $sig_check = 'sha1=' . hash_hmac( 'sha1', file_get_contents( 'php://input' ), $secret );
          $github_signature = isset( $headers[ 'X-GitHub-Signature' ] ) ? $headers[ 'X-GitHub-Signature' ] : '';
          if ( $sig_check != $github_signature ) {
            header( 'HTTP/1.1 401' );
            die( 'Signature does not match' );
          }
        }
        
        /* Either no secret or the secret matched, go ahead and clear the cache */
        if (Import::get_option('push_clear_all', false)) {
          /* Clear all */
          Import::clear_cache();
          header('HTTP/1.1 200'); 
          die('All caches cleared'); 
        }
        else {
          /* Clear the active post only */
          Import::clear_cache($post->ID);
          header('HTTP/1.1 200');
          die('Cache cleared for post ' . $post->ID); 
        }
      }
    }
  }

  /**
   * Action callback to remove cached data from GitHub API
   * 
   * @param int $post_id
   */
  public static function clear_cache( $post_id ) {
    Import::clear_cache( $post_id );
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

<?php

namespace Aelora\WordPress\GitHub;

/**
 * Information about a single file from GitHub along with methods for pulling
 * the contents from the GitHub API
 */
class GitHubFile {

  private $type = 'github';
  private $branch = 'master';
  private $owner = false;
  private $repo = false;
  private $filename = false;
  private $hash = false; // gist hash

  public function __construct( $url = false ) {
    $this->set_url( $url ? $url : ''  );
  }

  public function set_url( $url ) {
    $this->type = false;
    $this->branch = false;
    $this->owner = false;
    $this->filename = false;
    $this->repo = false;
    $this->hash = false;

    if ( strpos( $url, 'github.com' ) === false ) {
      throw new \InvalidArgumentException( 'URL does not appear to be a GitHub address' );
    }

    if ( ! preg_match( '/^(http)?s?:?\/\//i', $url ) ) {
      $url = '//' . $url;
    }

    $url_parts = parse_url( $url );

    $url_parts[ 'host' ] = preg_replace( '/^www\./', '', strtolower( $url_parts[ 'host' ] ) );
    if ( $url_parts[ 'host' ] == 'github.com' ) {
      $this->type = 'github';
      $path_parts = explode( '/', ltrim( $url_parts[ 'path' ], '/' ) );

      $this->owner = $path_parts[ 0 ];
      $this->repo = $path_parts[ 1 ];

      $this->branch = isset( $path_parts[ 3 ] ) ? $path_parts[ 3 ] : 'master';
      $this->filename = isset( $path_parts[ 4 ] ) ? $path_parts[ 4 ] : 'readme.md';
    }
    else if ( $url_parts[ 'host' ] == 'gist.github.com' ) {
      $this->type = 'gist';
      $path_parts = explode( '/', ltrim( $url_parts[ 'path' ], '/' ) );
      if ( count( $path_parts ) == 1 ) {
        $this->owner = false;
        $this->hash = $path_parts[ 0 ];
      }
      else if ( count( $path_parts ) == 2 ) {
        $this->owner = $path_parts[ 0 ];
        $this->hash = $path_parts[ 1 ];
      }

      if ( ! empty( $url_parts[ 'fragment' ] ) && preg_match( '/^file-/', $url_parts[ 'fragment' ] ) ) {
        $this->filename = preg_replace( '/^file-/', '', $url_parts[ 'fragment' ] );
      }
    }
    else {
      throw new \InvalidArgumentException( 'URL does not appear to be a GitHub address' );
    }
  }

  public function get_type() {
    return $this->type;
  }

  public function get_branch() {
    return $this->branch;
  }

  public function get_owner() {
    return $this->owner;
  }

  public function get_repo() {
    return $this->repo;
  }

  public function get_filename() {
    return $this->filename;
  }

  public function is_github() {
    return $this->type == 'github';
  }

  public function is_gist() {
    return $this->type == 'gist';
  }

  public function get_hash() {
    return $this->hash;
  }

}

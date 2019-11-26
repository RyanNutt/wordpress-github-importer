<?php

namespace Aelora\WordPress\GitHub;

/**
 * Information about a single file from GitHub along with methods for pulling
 * the contents from the GitHub API.
 * 
 * It may actually be multiple files if the URL is a Gist and no file is
 * specified. In that case it'll pull all the files, but only include the
 * first one in the data. The filename isn't known until the API result
 * comes back. 
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
      $url_parts[ 'path' ] = ltrim( $url_parts[ 'path' ], '/' );
      if ( preg_match( '/^([A-Za-z0-9\-_]+?)\/([A-Za-z0-9\-_]+?)\/blob\/(.+?)\/(.*)$/', $url_parts[ 'path' ], $matches ) ) {
        $this->owner = $matches[ 1 ];
        $this->repo = $matches[ 2 ];
        $this->branch = $matches[ 3 ];
        $this->filename = $matches[ 4 ];
      }
      else if ( preg_match( '/^([A-Za-z0-9\-_]+?)\/([A-Za-z0-9\-_]+?)$/', $url_parts[ 'path' ], $matches ) ) {
        $this->owner = $matches[ 1 ];
        $this->repo = $matches[ 2 ];
        $this->branch = 'master';
        $this->filename = 'readme.md';
      }
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

  /**
   * Returns the URL for the API endpoint to get the data requested. 
   */
  public function api_url() {
    $url = false;
    if ( $this->is_github() ) {
      $url = 'https://api.github.com/repos/' . $this->get_owner() . '/' . $this->get_repo() . '/contents/' . $this->get_filename();
      if ( $this->branch != 'master' ) {
        $url .= '?ref=' . $this->branch;
      }
    }
    else if ( $this->is_gist() ) {
      $url = 'https://api.github.com/gists/' . $this->hash;
    }
    return $url;
  }

  public function get( $api_key = '' ) {
    $url = $this->api_url();
    if ( $url === false ) {
      throw new InvalidArgumentException( 'Cannot get API url' );
    }

    $headers = [];
    if ( ! empty( $api_key ) ) {
      $headers[ 'Authorization' ] = 'token ' . $api_key;
    }

    $result = wp_remote_get( $url, [ 'headers' => $headers ] );
    return $result;
  }

  public function is_markdown() {
    $ext = strtolower( pathinfo( $this->get_filename(), PATHINFO_EXTENSION ) );
    return in_array( $ext, [ 'md', 'mkdn', 'mkd', 'markdown', 'mdown' ] );
  }

}

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

  public function __construct( $url = false ) {
    $this->set_url( $url ? $url : '' );
  }

  public function set_url( $url ) {
    $this->type = false;
    $this->branch = false;
    $this->owner = false;
    $this->filename = false;
    $this->repo = false; 
    
    if (strpos($url, 'github.com') === false) {
      throw new \InvalidArgumentException('URL does not appear to be a GitHub address'); 
    }
    
    if (!preg_match('/^(http)?s?:?\/\//i', $url)) {
      $url = '//' . $url;
    }
           
    $url_parts = parse_url($url);
    
    $url_parts['host'] = preg_replace('/^www\./', '', strtolower($url_parts['host']));
    if ($url_parts['host'] == 'github.com') {
      $this->type = 'github'; 
    }
    else if ($url_parts['host'] == 'gist.github.com') {
      $this->type = 'gist'; 
    }
    else {
      throw new \InvalidArgumentException('URL does not appear to be a GitHub address'); 
    }
    
    $path_parts = explode('/', $url_parts['path']);
    print_r($path_parts);
    $this->owner = $path_parts[1];
    $this->repo = $path_parts[2];
    
    print_r($url_parts); 
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

}

<?php

namespace Aelora\WordPress\GitHub;

class Output {

  /**
   * Returns the file as HTML for display
   * 
   * @param type $api_results
   * @param \Aelora\WordPress\GitHub\GitHubFile $file_info
   */
  public static function to_html( $api_body, GitHubFile $file_info ) {
    if ( ! empty( $api_body[ 'message' ] ) ) {
      /* Probably an error */
      return '<div>' . $api_body[ 'message' ] . '</div>';
    }
    else if ( ! empty( $api_body[ 'content' ] ) ) {
      if ( ($file_info->is_github() && $file_info->is_markdown()) || ($file_info->is_gist() && strtolower( $api_body[ 'language' ] ) == 'markdown') ) {
        return self::from_markdown( $api_body[ 'content' ], $file_info );
      }
      else {
        return self::from_text( $api_body[ 'content' ], $file_info );
      }
    }
  }

  /**
   * Returns the content as HTML converted from markdown
   * 
   * @param string $contents
   * @param \Aelora\WordPress\GitHub\GitHubFile $file_info
   * @return string
   */
  private static function from_markdown( $contents, GitHubFile $file_info ) { 
    if ( $file_info->is_github() ) {
      $contents = base64_decode( $contents );
    }
    if ( ! class_exists( 'Parsedown' ) ) {
      require(__DIR__ . '/Parsedown.php');
    }
    $pd = new \Parsedown();

    $base_dir = dirname( $file_info->get_owner() . '/' . $file_info->get_repo() . '/blob/' . $file_info->get_branch() . '/' . $file_info->get_filename() ) . '/';

    /* Fix relative image paths to use full URLs */
    $contents = preg_replace_callback( '/!\[(.*?)\]\((.*?)\)/m', function($matches) use ($base_dir, $file_info) {
      $url = isset( $matches[ 2 ] ) ? $matches[ 2 ] : '';
      if ( empty( $url ) ) {
        /* Just return whatever it already was */
        return $matches[ 0 ];
      }
      else {
        if ( preg_match( '/^\/{1}/', $url ) ) {
          /* Leading slash, just append github.com or gist.github.com */
          return '![' . $matches[ 1 ] . '](https://' . ($file_info->is_gist() ? 'gist.' : '') . 'github.com/' . $matches[ 2 ] . ')';
        }
        else if ( preg_match( '/^\/\//', $matches[ 2 ] ) || preg_match( '/^https?:\/\//', $matches[ 2 ] ) ) {
          /* Starts with a scheme, just pass through */
          return $matches[ 0 ];
        }
        else {
          /* Append to base url */
          return '![' . $matches[ 1 ] . '](' . $file_info->base_url_dir() . $matches[ 2 ] . ')';
        }
      }
    }, $contents );


    $md = $pd->text( $contents );

    return $md;
  }

  /**
   * Return as plain text.
   * 
   * This is going to be a catch all now for everything that's not markdown. 
   * Eventually this class will need to split out depending on the file type
   * so it can output in such a way that source files can be syntax highlighted.
   * 
   * @param string $contents
   * @param \Aelora\WordPress\GitHub\GitHubFile $file_info
   * @return string
   */
  private static function from_text( $contents, GitHubFile $file_info ) {
    if ( $file_info->is_github() ) {
      $contents = base64_decode( $contents );
    }
    return '<pre class="gh-text">' . htmlentities( $contents ) . '</pre>';
  }

}

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
      if ( $file_info->is_markdown() ) {
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
    $contents = base64_decode( $contents );
    if ( ! class_exists( 'Parsedown' ) ) {
      require(__DIR__ . '/Parsedown.php');
    }
    $pd = new \Parsedown();
    return $pd->text( $contents );
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
    return '<pre>' . base64_decode( $contents ) . '</pre>';
  }

}

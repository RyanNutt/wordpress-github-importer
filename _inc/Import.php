<?php
namespace Aelora\WordPress\GitHub;

Import::init();

class Import {

  private static $settings = [];

  public static function init() {
    self::$settings = json_decode( get_option( 'github_importer', '[]' ), true );

    add_action( 'admin_menu', [self::class, 'add_menu']);
  }

  public static function add_menu() {
    add_options_page(
            __( 'GitHub Importer Options', 'github-importer' ),
            __( 'GitHub Importer', 'github-importer' ),
            'manage_options',
            'github-importer.php',
            '\Aelora\WordPress\GitHub\Import::options_page'
    );
  }

  public static function options_page() {
    require(__DIR__ . '/options-page.php');
  }

  /**
   * Pull an option
   * @param string $key
   * @param mixed $default
   * @param boolean $reload If true, reload from database first. Otherwise just
   *                        pull from the settings array. 
   * @return mixed
   */
  public static function get_option( $key, $default = false, $reload = false ) {
    if ( $reload ) {
      self::$settings = json_decode( get_option( 'github_importer', '[]' ), true );
    }
    return isset( self::$settings[ $key ] ) ? self::$settings[ $key ] : $default;
  }

  public static function save_options() {
    update_option( 'github_importer', json_encode( self::$settings ) );
  }

  /**
   * Update an option, and save it back to the database if not deferred
   * 
   * @param string $key
   * @param mixed $value
   * @param boolean $defer  If false then the database record is updated as well.
   */
  public static function set_option( $key, $value, $defer = false ) {
    self::$settings[ $key ] = $value;
    if ( ! $defer ) {
      self::save_options();
    }
  }

}

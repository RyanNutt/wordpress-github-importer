<?php

/**
 * Plugin Name:       GitHub Importer
 * Plugin URI:        https://www.nutt.net
 * Description:       Import content from GitHub into your WordPress posts and pages
 * Version:           0.1.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Ryan Nutt
 * Author URI:        https://www.nutt.net/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       github-importer
 */
require_once(__DIR__ . '/_inc/Import.php');
require_once(__DIR__ . '/_inc/Shortcodes.php');
require_once(__DIR__ . '/_inc/GitHubFile.php');
require_once(__DIR__ . '/_inc/Output.php');

if ( ! class_exists( '\Aelora\WordPress\GitHub\Smashing_Updater' ) ) {
  include_once( plugin_dir_path( __FILE__ ) . '_inc/SmashingUpdater.php' );
}
$updater = new \Aelora\WordPress\GitHub\Smashing_Updater( __FILE__ );
$updater->set_username( 'ryannutt' );
$updater->set_repository( 'wordpress-github-importer' );
$updater->initialize();

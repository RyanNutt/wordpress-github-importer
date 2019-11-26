<?php

/**
 * Layout for the settings page
 */
use Aelora\WordPress\GitHub\Import;

$updated = false;
if ( isset( $_POST[ 'github_importer' ] ) ) {
  foreach ( $_POST[ 'github_importer' ] as $k => $v ) {
    Import::set_option( $k, $v, true );
  }
  /* Checkbox, a little different */
  Import::set_option( 'push_clear_all',  ! empty( $_POST[ 'github_importer' ][ 'push_clear_all' ] ), true );
  
  /* They were deferred, need to save */
  Import::save_options();
  $updated = true;
  
}
?>
<div class="wrap">
  <h1><?php _e( 'GitHub Importer Options', 'githumb-importer' ); ?></h1>
  <?php if ( $updated ) { ?>
    <div class="notice notice-success is-dismissible"> 
      <p><strong>Settings saved.</strong></p>
      <button type="button" class="notice-dismiss">
        <span class="screen-reader-text">Dismiss this notice.</span>
      </button>
    </div>
  <?php } ?>
  <form method="POST">
    <table class="form-table">
      <tr>
        <th><?php _e( 'GitHub Token', 'github-importer' ); ?></th>
        <td>
          <input type="text" name="github_importer[github_token]" class="regular-text" value="<?php echo esc_attr( Import::get_option( 'github_token', '' ) ); ?>">
          <br>
          <em>
            <?php _e( 'Optional, but increases API limits', 'github-importer' ); ?>
          </em>
        </td>
      </tr>
      <tr>
        <th><?php _e( 'Webhook Secret', 'github-importer' ); ?></th>
        <td>
          <input type="text" name="github_importer[webhook_secret]" class="regular-text" value="<?php echo esc_attr( Import::get_option( 'webhook_secret', false ) ); ?>">
          <br>
          <em>
            <?php _e( 'Required if you are using webhooks to update', 'github-importer' ); ?>
          </em>
        </td>
      </tr>
      <tr>
        <th><?php _e('Clear all on webhook', 'github-importer'); ?></th>
        <td>
          <input type="checkbox" name="github_importer[push_clear_all]" <?php checked(Import::get_option('push_clear_all', false), true, true); ?>>
        </td>
      </tr>
      <tr>
        <th><?php _e( 'Gist Transient Expiration', 'github-importer' ); ?></th>
        <td>
          <input type="number" min="0" name="github_importer[gist_transient]" class="" value="<?php echo esc_attr( Import::get_option( 'gist_transient', 3600 ) ); ?>">
        </td>
      </tr>
      <tr>
        <th>
          <input type="submit" value="Save Changes" class="button button-primary button-large">
        </th>
      </tr>
    </table>
  </form>
</div>
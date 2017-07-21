<?php
/**
 * @package   	      Caldera Forms Signature Contract Addon
 * @contributors      Kevin Michael Gray (Approve Me), Abu Shoaib (Approve Me), Arafat Rahman (Approve Me)
 * @wordpress-plugin
 * Plugin Name:       Caldera Forms Signature Contract Addon
 * Plugin URI:        http://aprv.me/2llqcY2
 * Description:       This add-on makes it possible to automatically email a WP E-Signature contract (or redirect a user to a contract) after the user has successfully submitted a Caldera Forms. You can also insert data from the submitted Caldera Forms into the WP E-Signature contract.
 * Version:           1.5.0
 * Author:            Approve Me
 * Author URI:        http://aprv.me/2llqcY2
 * Text Domain:       esig-caldera
 * Domain Path:       /languages
 * License/Terms & Conditions: http://www.approveme.com/terms-conditions/
 * Privacy Policy: http://www.approveme.com/privacy-policy/
 * License:     GPLv2+
 * Domain Path: /languages
 */

/**
 * Define constants
 */
define( 'CF_WPESIGNATURE_VER', '1.5.0' );
define( 'CF_WPESIGNATURE_URL',     plugin_dir_url( __FILE__ ) );
define( 'CF_WPESIGNATURE_PATH',    dirname( __FILE__ ) . '/' );
define( 'CF_WPESIGNATURE_CORE',    dirname( __FILE__ )  );

/**
 * Default initialization for the plugin:
 * - Registers the default textdomain.
 */
add_action( 'init', 'cf_wpesignature_init_text_domain' );
function cf_wpesignature_init_text_domain() {
   
	load_plugin_textdomain( 'cf-wpesignature', FALSE, CF_WPESIGNATURE_PATH . 'languages' );
}

/*----------------------------------------------------------------------------*
 * Public-Facing Functionality
 *----------------------------------------------------------------------------*/
 require_once( plugin_dir_path( __FILE__ ) . 'admin/includes/esig-cf-settings.php' );
require_once( plugin_dir_path( __FILE__ ) . 'admin/includes/esig-cfds.php' );


/*
 * Register hooks that are fired when the plugin is activated or deactivated.
 * When the plugin is deleted, the uninstall.php file is loaded.
 */
 
register_activation_hook( __FILE__, array( 'ESIG_CFDS', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'ESIG_CFDS', 'deactivate' ) );


//if (is_admin()) {
         
	require_once( plugin_dir_path( __FILE__ ) . 'admin/esig-cfds-admin.php' );
        add_action( 'plugins_loaded', array( 'ESIG_CFDS_Admin', 'get_instance' ) );

    require_once( plugin_dir_path( __FILE__ ) . 'admin/includes/esig-caldera-document-view.php' );

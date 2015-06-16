<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * Dashboard. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://example.com
 * @since             1.0.0
 * @package           CC_Group_Related_Profile_Fields
 *
 * @wordpress-plugin
 * Plugin Name:       CC Group-Related Profile Fields
 * Plugin URI:        http://example.com/plugin-name-uri/
 * Description:       Create groupings of extended profile fields for specific groups.
 * Version:           1.0.0
 * Author:            David Cavins
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       cc-grpf
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The code that runs during plugin activation.
 */
// require_once plugin_dir_path( __FILE__ ) . 'includes/class-cc-group-pages-activator.php';

/**
 * The code that runs during plugin deactivation.
 */
// require_once plugin_dir_path( __FILE__ ) . 'includes/class-cc-group-pages-deactivator.php';

/** This action is documented in includes/class-plugin-name-activator.php */
// register_activation_hook( __FILE__, array( 'Plugin_Name_Activator', 'activate' ) );

/** This action is documented in includes/class-plugin-name-deactivator.php */
// register_deactivation_hook( __FILE__, array( 'Plugin_Name_Deactivator', 'deactivate' ) );

/**
 * The core plugin class that is used to define internationalization,
 * dashboard-specific hooks, and public-facing site hooks.
 */

/**
 * Load the main class, after BuddyPress has had a chance to load.
 *
 * @since    1.0.0
 */
function cc_grpf_main_class_init() {
	require_once( plugin_dir_path( __FILE__ ) . 'includes/class-cc-grpf.php' );
	add_action( 'bp_include', array( 'CC_Group_Member_Profile_Fields', 'get_instance' ), 21 );
}
add_action( 'bp_include', 'cc_grpf_main_class_init', 16 );
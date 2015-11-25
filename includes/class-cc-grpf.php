<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the dashboard.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    CC_Group_Related_Profile_Fields
 * @subpackage CC_Group_Related_Profile_Fields/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, dashboard-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    CC_Group_Related_Profile_Fields
 * @subpackage CC_Group_Related_Profile_Fields/includes
 * @author     Your Name <email@example.com>
 */
class CC_Group_Member_Profile_Fields {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Plugin_Name_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;


	/**
	 * The plugin's slug.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_slug    The string that is the plugin's slug.
	 */
	protected $plugin_slug;

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the Dashboard and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		$this->plugin_name = 'cc-grpf';
		$this->plugin_slug = 'cc-grpf';
		$this->version = '1.0.0';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Plugin_Name_Loader. Orchestrates the hooks of the plugin.
	 * - Plugin_Name_i18n. Defines internationalization functionality.
	 * - Plugin_Name_Admin. Defines all hooks for the dashboard.
	 * - Plugin_Name_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for setting up the custom post type and taxonomy.
		 */
		// require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-cc-mrad-cpt-tax.php';

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		// require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-cc-group-pages-loader.php';

		/**
		 * The BuddyPress group extension class.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-grpf-group-extension.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-cc-grpf-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the Dashboard.
		 */
		// require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-cc-mrad-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-cc-grpf-public.php';

		/**
		 * Random functions.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/cc-grpf-functions.php';
		/**
		 * The templates file.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/partials/cc-grpf-public-display.php';
		/**
		 * Extended profile field visibility options.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-grpf-xprofile-visibility.php';


		// $this->loader = new CC_Group_Pages_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Plugin_Name_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new CC_GRPF_i18n();
		$plugin_i18n->set_domain( $this->get_plugin_name() );

		add_action( 'plugins_loaded', array( $plugin_i18n, 'load_plugin_textdomain' ) );

	}

	/**
	 * Register all of the hooks related to the dashboard functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		// $plugin_admin = new CC_MRAD_Admin( $this->get_plugin_name(), $this->get_version() );
		// add_action( 'admin_menu', array( $plugin_admin, 'setup_menus' ) );
		// add_action( 'admin_menu', array( $plugin_admin, 'setup_settings' ) );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new CC_GRPF_Public( $this->get_plugin_name(), $this->get_version() );

		/* DISPLAY ***********************************************************/
		// On a user's profile, only show the profile field groups to members of the associated groups.
		// In wp-admin, add the "associated groups" string to the end of the profile group's description.
		add_filter( 'bp_xprofile_get_groups', array( $plugin_public, 'filter_fieldgroups' ), 10, 2 );
		// Within a group, add the extra profile info to the group's members list.
		add_action( 'bp_group_members_list_item', array( $plugin_public, 'add_fieldgroups_to_group_member_list' ) );
		add_action( 'bp_init', array( $plugin_public, 'modify_profile_search_links_on_group_member_dir' ) );

		add_action( 'wp_enqueue_scripts', array( $plugin_public, 'enqueue_styles') );
		// add_action( 'wp_enqueue_scripts', array( $plugin_public, 'enqueue_scripts') );

		/* ASSOCIATE PROFILE GROUPS WITH HUBS ********************************/
		// Add a meta area to the group's "admin>settings" tab.
   		// We're also using BP_Group_Extension's admin_screen method to add this meta box to the WP-admin group edit
        add_filter( 'groups_custom_group_fields_editable', array( $plugin_public, 'meta_form_markup' ) );
        // Catch the saving of the meta form, fired when create>settings pane is saved or admin>settings is saved
        add_action( 'groups_group_details_edited', array( $plugin_public, 'meta_form_save') );
		add_action( 'groups_created_group', array( $plugin_public, 'meta_form_save' ) );

		/* NOTIFICATIONS *****************************************************/
		// Format the output of the "please fill out your profile" notifications.
		// Note that this requires a change to BP that will be included in 2.4.
		add_filter( 'bp_groups_complete_group_profile_notification', array( $plugin_public, 'group_profile_notification_description' ), 10, 5 );

		// When a member joins a public group that requires extra profile info, notify that user.
		add_action( 'groups_join_group', array( $plugin_public, 'join_group_profile_notification_handler' ), 10, 2 );
		// When a member accepts an invite, or a request is accepted, and the group requires more info, notify the user.
		add_action( 'groups_accept_invite', array( $plugin_public, 'invite_request_profile_notification_handler' ), 10, 2 );
		add_action( 'groups_membership_accepted', array( $plugin_public, 'invite_request_profile_notification_handler' ), 10, 2 );

		// When a member fills out the proper form, mark the notification as read.
		add_action( 'xprofile_updated_profile', array( $plugin_public, 'maybe_mark_read_profile_notification' ), 10, 5 );

		/* MEMBERSHIP REQUESTS ***********************************************/
		// Ask membership-request users to fill out related forms on the request form.
		// Add the group profile form to the request form
		add_action( 'bp_group_request_membership_content', array( $plugin_public, 'add_profile_field_group_form' ) );

		// Process the responses of the form on the group requests page.
		add_action( 'groups_membership_requested', array( $plugin_public, 'process_group_requests_profile_form' ), 10, 4 );

		add_action( 'bp_group_membership_requests_admin_item', array( $plugin_public, 'add_profile_field_data_to_member_requests_display' ) );

		// @TODO: BP may add a filter that we could use to stop requests. Doesn't exist currently.
		// add_filter( 'groups_membership_request_is_allowed', array( $plugin_public, 'pre_membership_request_check' ), 10, 2 );

		/* BP XPROFILE FIELD VISIBILITY **************************************/
		$plugin_xprofile_vis = new CC_GRPF_Xprofile_Visibility( $this->get_plugin_name(), $this->get_version() );

		// Add "groupadmins" field visibility. Useful for group-related fields.
		add_filter( 'bp_xprofile_get_visibility_levels', array( $plugin_xprofile_vis, 'filter_xprofile_visibility_levels' ) );
		// Remove the groupadmins visibility setting for fields that are not
	    // part of group-related field groups.
		add_filter( 'bp_profile_get_visibility_radio_buttons', array( $plugin_xprofile_vis, 'filter_get_visibility_radio_buttons' ), 17, 3 );

		// We have to provide some logic for our new visibility setting.
		// Mostly, fields with the visibility "groupadmins" are hidden.
		add_filter( 'bp_xprofile_get_hidden_field_types_for_user', array( $plugin_xprofile_vis, 'filter_get_hidden_field_types_for_user' ), 18, 3 );

		// Show fields with the vis "groupadmins" when appropriate.
		add_filter('bp_xprofile_get_hidden_fields_for_user', array( $plugin_xprofile_vis, 'filter_get_hidden_fields_for_user' ), 10, 3);

		// @ TODO: Change vis labels to reflect group relation, like in non-public groups, "All members" -> "All Hub members"

	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The slug of the plugin is the portion of the uri after the group name.
	 *
	 * @since     1.0.0
	 * @return    string    The slug used.
	 */
	public function get_plugin_slug() {
		return $this->plugin_slug;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Plugin_Name_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
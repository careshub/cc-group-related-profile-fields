<?php
/**
 * CC_Group_Related_Profile_Fields
 *
 * @package   CC_Group_Related_Profile_Fields
 * @author    David Cavins
 * @license   GPL-2.0+
 * @copyright 2014 Community Commons
 */

/**
 * The class_exists() check is recommended, to prevent problems during upgrade
 * or when the Groups component is disabled
 */
if ( class_exists( 'BP_Group_Extension' ) ) :

// We're going to use BP_Group_Extension to add an admin metabox on the wp-admin>group edit screen
class CC_GRPF_Group_Extension extends BP_Group_Extension {

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $grpf_plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $grpf_version;
    /**
     * Your __construct() method will contain configuration options for
     * your extension, and will pass them to parent::init()
     */

    function __construct() {
        $main_class = CC_Group_Member_Profile_Fields::get_instance();

        $this->grpf_plugin_name = $main_class->get_plugin_name();
        $this->grpf_version = $main_class->get_version();

        $args = array(
            'slug' => $this->grpf_plugin_name,
            'name' => 'Related Profile Fields',
            'enable_nav_item' => false, // We don't need a display tab
            'screens' => array(
                'edit' => array(
                	'enabled' => false, // We do not need an edit tab, we're putting these options on the settings pane
                ),
                'create' => array(
                    'enabled' => false, // We do not need a create tab, we're putting these options on the settings pane
                ),
                'admin' => array(
                	'metabox_context' => 'side',
                    // 'name' => 'Community Commons Group Meta',
                ),
            ),
        );
        parent::init( $args );
    }

    /**
     * admin_screen() is the method for displaying the content
     * of the Dashboard admin panels
     */
    public function admin_screen( $group_id = null ) {
        $plugin_public = new CC_GRPF_Public( $this->grpf_plugin_name, $this->grpf_version );

        // Use our vanilla meta form markup
        return $plugin_public->meta_form_markup( $group_id );
    }

    /**
     * settings_screen_save() contains the logic for saving
     * settings from the Dashboard admin panels
     */
    public function admin_screen_save( $group_id = null ) {
        $plugin_public = new CC_GRPF_Public( $this->grpf_plugin_name, $this->grpf_version );

       	// Use our all-purpose saving function
    	return $plugin_public->meta_form_save( $group_id );
    }

}
bp_register_group_extension( 'CC_GRPF_Group_Extension' );

endif; // if ( class_exists( 'BP_Group_Extension' ) )
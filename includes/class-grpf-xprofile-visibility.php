<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    CC_Group_Related_Profile_Fields
 * @subpackage CC_Group_Related_Profile_Fields/includes
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the dashboard-specific stylesheet and JavaScript.
 *
 * @package    CC_Group_Related_Profile_Fields
 * @subpackage CC_Group_Related_Profile_Fields/includes
 * @author     David Cavins
 */
class CC_GRPF_Xprofile_Visibility {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @var      string    $plugin_name       The name of the plugin.
	 * @var      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/* BP Xprofile field visibility **************************************/

	/**
	 *  Add "groupadmins" field visibility. Useful for group-related fields.
	 *
 	 *  @since  1.0.0
	 *
	 *  @param  array $visibility_levels Array of visibility levels.
	 *  @return array Extended visibility level settings.
	 */
	public function filter_xprofile_visibility_levels( $visibility_levels ) {
		$visibility_levels['groupadmins'] = array(
			'id' => 'groupadmins',
            'label' => __( 'Hub Admins', $this->plugin_name ),
        );

		return $visibility_levels;
	}

	/**
	 *  Remove the groupadmins visibility setting for fields that are not
	 * 	part of group-related field groups.
	 *
 	 *  @since  1.0.0
 	 *
	 *  @param string $retval         HTML output for the visibility radio buttons.
	 *  @param array  $parsed_args    Parsed arguments to be used with display.
	 *  @param array  $original_args Original passed in arguments to be used with display.
	 *  @return string HTML output for the visibility radio buttons.
	 */
	public function filter_get_visibility_radio_buttons( $retval, $parsed_args, $original_args ) {
		// Is this field part of a group that's associated with a group?
		$field = xprofile_get_field( $parsed_args['field_id'] );

		// If not, return the options, which include our group-related visibility option.
		if ( ! empty( grpf_get_associated_groups_for_field_group( $field->group_id ) ) ) {
			return $retval;
		}

		// OK, it isn't associated with a group, so we need to remove our group-related options.
		# Create a DOM parser object
		$dom = new DOMDocument();

		# Parse the HTML, looking for the li we want to remove.
		# The @ before the method call suppresses any warnings that
		# loadHTML might throw because of invalid HTML in the page.
		@$dom->loadHTML( $retval );
		$xpath = new DOMXPath($dom);
		foreach( $xpath->query('//li[contains(attribute::class, "groupadmins")]') as $e ) {
			    // Delete this node
			    $e->parentNode->removeChild( $e );
		}

		$retval = '';
		foreach ( $xpath->query('//ul') as $ul  ) {
			$retval .= $dom->saveHTML( $ul );
		}

		return $retval;
	}

	/**
	 *  Add group-admins-only field visibility for group-related fields.
	 *
	 *  @since  1.0.0
	 *
	 *  @param array $hidden_fields     Array of hidden fields for the displayed/logged-in user pair.
	 *  @param int   $displayed_user_id ID of the displayed user.
	 *  @param int   $current_user_id   ID of the current user.
	 *  @return array
	 */
	public function filter_get_hidden_field_types_for_user( $hidden_levels, $displayed_user_id, $current_user_id ) {

		// Current user is logged in
		if ( ! empty( $current_user_id ) ) {

			// Nothing's private when viewing your own profile, or when the
			// current user is an admin.
			if ( $displayed_user_id == $current_user_id || bp_current_user_can( 'bp_moderate' ) ) {
				// We don't need to add any restrictions.
			} elseif ( $group_id = bp_get_current_group_id() ) {
				// If we're in a group, is the current user a group admin?
				if ( ! groups_is_user_admin( $current_user_id, $group_id ) ) {
					$hidden_levels[] = 'groupadmins';
				}
			} else {
				// We're not in a group--we're most likely on a user profile page.
				// We hide all 'groupadmins' level items, then we'll optionally
				// show them on a per-field basis.
				$hidden_levels[] = 'groupadmins';
			}

		// Current user is not logged in, so exclude groupadmins.
		} else {
			$hidden_levels[] = 'groupadmins';
		}

		return $hidden_levels;
	}

	/**
	 *  Show fields with the vis "groupadmins" when appropriate.
	 *
	 *  @since  1.0.0
	 *
	 *  @param array $hidden_fields     Array of hidden fields for the displayed/logged in user.
	 *  @param int   $displayed_user_id ID of the displayed user.
	 *  @param int   $current_user_id   ID of the current user.
	 *  @return array Array of hidden fields for the displayed/logged in user.
	 */
	public function filter_get_hidden_fields_for_user( $hidden_fields, $displayed_user_id, $current_user_id ) {

		// Only optionally show fields if current user is logged in.
		if ( ! empty( $current_user_id ) ) {
			foreach ( $hidden_fields as $k => $field_id ) {
				// Is this field part of a field group that's associated with a group?
				$field = xprofile_get_field( $field_id );
				$group_ids = grpf_get_associated_groups_for_field_group( $field->group_id );

				// If so, is the current user one of the related groups' admins?
				if ( ! empty( $group_ids ) ) {
					foreach ( $group_ids as $group_id ) {
						if ( groups_is_user_admin( $current_user_id, $group_id ) ) {
							unset( $hidden_fields[ $k ] );
							break;
						}
					}
				}
			}
		}

		return $hidden_fields;
	}

} // End class
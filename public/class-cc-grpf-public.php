<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    CC_Group_Related_Profile_Fields
 * @subpackage CC_Group_Related_Profile_Fields/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the dashboard-specific stylesheet and JavaScript.
 *
 * @package    CC_Group_Related_Profile_Fields
 * @subpackage CC_Group_Related_Profile_Fields/public
 * @author     David Cavins
 */
class CC_GRPF_Public {

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

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		if ( bp_is_group_members() ) {
			wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/cc-grpf-public.css', array(), $this->version, 'all' );
		}
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		// wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/cc-mrad-public.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_edit_scripts() {

		// wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/cc-group-pages-edit.js', array( 'jquery' ), $this->version, false );

	}

	/* DISPLAY ***************************************************************/

	/**
	 * On front end, return the fieldgroups filtered by the current user's group membership.
	 * In wp-admin, filter the field group descriptions where necessary
	 *
	 * First, the displayed user must be a member to show the fieldgroup. Second,
	 * the loggedin user must be a member to show the fieldgroup. If either one
	 * fails the fieldgroup's user group membership, it's removed.
	 *
	 * @since 1.0.0
	 *
	 * @uses CC_GRPF_Public::is_user_fieldgroup_member()
	 * @uses get_current_user_id()
	 *
	 * @param array $field_groups Fieldgroup objects
	 * @param array $args Query arguments
	 * @return array Field groups
	 */
	public function filter_fieldgroups( $field_groups, $args ) {

		if ( is_admin() ) {
			// In wp-admin, we want to add some helpful data on the field group management screen.
			foreach ( $field_groups as $k => $field_group ) {
				$field_groups[$k]->description = wptexturize( stripslashes( $field_group->description . ' | ' . grpf_human_readable_associated_group_markup( $field_group->id ) ) );
			}
		} elseif ( bp_is_user_profile() ) {
			// We also filter the results on the user profile.

			// Site admins can see everything.
			if ( bp_current_user_can( 'bp_moderate' ) ) {
				return $field_groups;
			}

			// Loop all groups
			foreach ( $field_groups as $k => $field_group ) {

				// Keep the primary fieldgroup
				if ( 1 == $field_group->id )
					continue;

				// Is the visiting user not a member? Then don't show them the info.
				if ( ! $this->is_user_fieldgroup_member( $field_group->id, get_current_user_id() ) ) {
					unset( $field_groups[$k] );
					continue;
				}

			}

			// Reorder nicely
			$field_groups = array_values( $field_groups );
		}

		return $field_groups;
	}

	/**
	 *  Show group-related profile data on the group membership requests admin pane.
	 *
	 *  @return html The form field data.
	 *  @since  1.0.0
	 */
	public function add_profile_field_data_to_member_requests_display() {
		global $requests_template;
		// echo '<pre>'; print_r($requests_template); echo '</pre>';
		$user_id = $requests_template->request->user_id;
		$group_id = $requests_template->request->group_id;
		$field_group_ids = grpf_get_associated_field_groups( $group_id );

		if ( empty( $user_id ) || empty( $field_group_ids ) ) {
			return;
		}

		foreach ( $field_group_ids as $field_group_id ) {
			grpf_output_profile_group_form_field_entries( $field_group_id, $user_id );
		}
	}

	/**
	 *  Show group-related profile data on the group member list.
	 *
	 *  @return html The form field data.
	 *  @since  1.0.0
	 */
	public function add_fieldgroups_to_group_member_list() {
		$user_id = bp_get_group_member_id();
		$group_id = bp_get_current_group_id();
		$field_group_ids = grpf_get_associated_field_groups( $group_id );

		if ( empty( $user_id ) || empty( $field_group_ids ) ) {
			return;
		}

		foreach ( $field_group_ids as $field_group_id ) {
			grpf_output_profile_group_form_field_entries_no_table( $field_group_id, $user_id );
		}
	}

	/**
	 *  Profile links on group member directory lists should link back to the group members directory.
	 *
	 *  @return html The form field data.
	 *  @since  1.0.0
	 */
	public function modify_profile_search_links_on_group_member_dir() {
		if ( bp_is_group_members() ) {
			remove_filter( 'bp_get_the_profile_field_value', 'xprofile_filter_link_profile_data', 9, 2 );
			add_filter( 'bp_get_the_profile_field_value', array( $this, 'group_members_filter_link_profile_data' ), 9, 2 );
		}

	}

	/**
	 * Filter an Extended Profile field value, and attempt to make clickable links
	 * to group members search results out of them. Taken from xprofile_filter_link_profile_data().
	 *
	 * - Not run on datebox field types
	 * - Not run on values without commas with less than 5 words
	 * - URL's are made clickable
	 *
	 * @since 1.0.0
	 *
	 * @param string $field_value
	 * @param string  $field_type
	 * @return string
	 */
	public function group_members_filter_link_profile_data( $field_value, $field_type = 'textbox' ) {

		if ( 'datebox' === $field_type ) {
			return $field_value;
		}

		if ( !strpos( $field_value, ',' ) && ( count( explode( ' ', $field_value ) ) > 5 ) ) {
			return $field_value;
		}

		$values = explode( ',', $field_value );

		if ( !empty( $values ) ) {
			foreach ( (array) $values as $value ) {
				$value = trim( $value );

				// If the value is a URL, skip it and just make it clickable.
				if ( preg_match( '@(https?://([-\w\.]+)+(:\d+)?(/([\w/_\.]*(\?\S+)?)?)?)@', $value ) ) {
					$new_values[] = make_clickable( $value );

				// Is not clickable
				} else {

					// More than 5 spaces
					if ( count( explode( ' ', $value ) ) > 5 ) {
						$new_values[] = $value;

					// Less than 5 spaces
					} else {
						$search_url   = add_query_arg( array( 's' => urlencode( $value ) ), bp_get_group_all_members_permalink() );
						$new_values[] = '<a href="' . esc_url( $search_url ) . '" rel="nofollow">' . $value . '</a>';
					}
				}
			}

			$values = implode( ', ', $new_values );
		}

		return $values;
	}

	/* PROFILE GROUP META/HUB ASSOCIATION FORM ******************************/

	/**
	 *  Renders extra fields on form when creating a group and when editing group details
	 *
	 *  @param  	int $group_id
	 *  @return 	string html markup
	 *  @since    	0.1.0
	 */
	public function meta_form_markup( $group_id = 0 ) {

		// Only site admins can set this up.
		if ( ! current_user_can( 'delete_others_pages' ) ) {
			return;
		}

		$group_id = $group_id ? $group_id : bp_get_current_group_id();

		if ( ! is_admin() ) : ?>
			<div class="checkbox content-row">
				<h4>Related Profile Field Groups</h4>
		<?php endif; ?>

			<?php
			$args = array(
				'profile_group_id'       => false,
				'user_id'                => 0,
				'hide_empty_groups'      => false,
				'hide_empty_fields'      => false,
				'fetch_fields'           => false,
				'fetch_field_data'       => false,
				'fetch_visibility_level' => false,
				'exclude_groups'         => 1, // Don't include the base group.
				'exclude_fields'         => false,
				'update_meta_cache'      => false,
			);
			$field_groups = bp_xprofile_get_groups( $args );
			$selected_field_groups = grpf_get_associated_field_groups( $group_id );

            if ( ! empty( $field_groups ) ) :
            ?>
        		<p class="description">Select a profile field group to use with this hub.</p>

	            <ul class="no-bullets">
					<?php
					    foreach ( $field_groups as $field_group ) {
			            	$selected = in_array( $field_group->id, $selected_field_groups) ? true : false;
						?>
						<li id="profile-field-group-<?php echo $field_group->id; ?>"><label class="selectit"><input value="<?php echo $field_group->id; ?>" type="checkbox" name="grpf-profile-field-group[]" id="in-category-<?php echo $category->term_id; ?>" <?php checked( $selected ); ?>> <?php echo $field_group->name; ?>
							<?php if ( ! is_admin() ) {
									if ( ! empty( $field_group->description ) ) { ?>
										<br /><span class="description"><?php echo wptexturize( stripslashes( $field_group->description ) ); ?></span>
									<?php }
									echo grpf_human_readable_associated_group_markup( $field_group->id, true );
								} // end if ( ! is_admin() )
							?>
							</label></li>
						<?php
					}
					?>
	            </ul>
	            <p class="description">Need to create a new profile group to use? Visit the <a href="/wp-admin/users.php?page=bp-profile-setup">profile fields management screen</a>.</p>
            <?php
            endif; //if ( ! empty( $field_groups ) )
		if ( ! is_admin() ) : ?>
			<hr />
			</div>
		<?php endif;
	}

	/**
	 *  Saves the input from our extra meta fields
 	 * 	Used by CC_Custom_Meta_Group_Extension::admin_screen_save()
 	 *  @param  	int $group_id
	 *  @return 	void
	 *  @since    	1.0.0
	 */
	public function meta_form_save( $group_id = 0 ) {
		$group_id = $group_id ? $group_id : bp_get_current_group_id();
		$members = array();

		// $meta = array(
		// 	// Checkboxes
		// 	'cc_group_is_featured' => isset( $_POST['cc_featured_group'] ),
		// 	'group_is_prime_group' => isset( $_POST['group_is_prime_group'] ),
		// );

		// foreach ( $meta as $meta_key => $new_meta_value ) {

		// 	/* Get the meta value of the custom field key. */
		// 	$meta_value = groups_get_groupmeta( $group_id, $meta_key, true );

		// 	/* If there is no new meta value but an old value exists, delete it. */
		// 	if ( '' == $new_meta_value && $meta_value )
		// 		groups_delete_groupmeta( $group_id, $meta_key, $meta_value );

		// 	/* If a new meta value was added and there was no previous value, add it. */
		// 	elseif ( $new_meta_value && '' == $meta_value )
		// 		groups_add_groupmeta( $group_id, $meta_key, $new_meta_value, true );

		// 	/* If the new meta value does not match the old value, update it. */
		// 	elseif ( $new_meta_value && $new_meta_value != $meta_value )
		// 		groups_update_groupmeta( $group_id, $meta_key, $new_meta_value );
		// }

		// I don't think we'll want to store the category relationship as a serialized array, but as multiple meta items.
		// Not as efficient storage-wise, but it'll greatly simplify finding groups by category.
		$field_groups = (array) $_POST['grpf-profile-field-group']; // This is OK if empty, an empty array works for us.
		// Fetch existing values
		$old_field_groups = grpf_get_associated_field_groups( $group_id );
		// Values in the db but not in the POST should be removed
		$field_groups_to_delete = array_diff( $old_field_groups, $field_groups );
		// Values not in the db but in the POST should be added
		$field_groups_to_add = array_diff( $field_groups, $old_field_groups );

		// We'll need the members list for either operation.
		if ( ! empty( $field_groups_to_add ) || ! empty( $field_groups_to_delete ) ) {
			$members = groups_get_group_members(
				array(
					'group_id'            => $group_id,
					'exclude_admins_mods' => false,
				)
			);
		}


		if ( ! empty( $field_groups_to_add ) ) {
			// Add a notification that each group member should update his profile.
			foreach ( $field_groups_to_add as $add_id ) {
				$this->associate_hub_with_field_group( $add_id, $group_id );
				if ( $members['count'] > 0 ) {
					foreach ( $members['members'] as $member) {
						// Add notifications for this group.
						$this->create_group_profile_nag_notification( $member->ID, $group_id, $add_id );
					}
				}
			}
		}

		if ( ! empty( $field_groups_to_delete ) ) {
			foreach ( $field_groups_to_delete as $delete_id ) {

				$this->disassociate_hub_from_field_group( $delete_id, $group_id );
				// Delete notifications for this group.
				if ( $members['count'] > 0 ) {
					foreach ( $members['members'] as $member) {
						$this->delete_group_profile_nag_notification( $member->ID, $group_id, $delete_id );
					}
				}

				// If the profile field group is no longer associated with any group,
				// we need to unset the visibility setting "groupadmins" because it makes no sense.
				$associated_group_ids = grpf_get_associated_groups_for_field_group( $delete_id );
				if ( empty( $associated_group_ids ) ) {
					// Loop through each of the profile field group's fields and check the vis setting.
					$field_group = current( bp_xprofile_get_groups( array(
						'profile_group_id'       => $delete_id,
						'fetch_fields'           => true,
						'fetch_visibility_level' => true,
						'update_meta_cache'      => false,
						) ) );

					foreach ( $field_group->fields as $field ) {
						// Change the field's default visibility.
						if ( 'groupadmins' == $field->visibility_level  ) {
							bp_xprofile_update_meta( $field->id, 'field', 'default_visibility', 'adminsonly' );
						}

						// Update all user-selected visibility for this field.
						// Find every user who has answered this question.
						$user_ids = grpf_get_users_with_entry_for_field( $field->id );
						foreach ( $user_ids as $user_id) {
							// If they've selected "groupadmins" make it private to be safe.
							if ( 'groupadmins' == xprofile_get_field_visibility_level( $field->id, $user_id ) ) {
								xprofile_set_field_visibility_level( $field->id, $user_id, 'adminsonly' );
							}
						}
					}
				}
			}
		}
	}

	/* NOTIFICATIONS *********************************************************/

	/**
	 *  Send a notification if a member needs to complete a group profile.
	 *
 	 *  @param  	int $user_id
 	 *  @param  	int $group_id
	 *  @return 	int|bool ID of the newly created notification on success, false
 	 *              on failure.
	 *  @since    	1.0.0
	 */
	public function create_group_profile_nag_notification( $user_id, $group_id, $field_group_id ) {
		if ( empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}
		if ( empty( $group_id ) ) {
			$group_id = bp_get_current_group_id();
		}

		// If the form has already been completed, don't annoy the user.
		$field_group = bp_xprofile_get_groups( array(
				'profile_group_id'       => $field_group_id,
				'user_id'                => $user_id,
				'hide_empty_groups'      => false,
				'hide_empty_fields'      => false,
				'fetch_fields'           => true,
				'fetch_field_data'       => true,
			)
		);

		$must_notify = false;
		if ( !empty( $field_group ) ) {
			$fields = current( $field_group )->fields;
			foreach ( $fields as $field ) {
				$towrite .= PHP_EOL . '$field: ' . print_r( $field, TRUE );

				if ( empty( $field->data->value ) ) {
					$must_notify = true;
					break;
				}
			}
		}

		if ( $must_notify ) {
			return bp_notifications_add_notification( array(
				'user_id'           => $user_id,
				'item_id'           => $group_id,
				'secondary_item_id' => $field_group_id,
				'component_name'    => 'groups',
				'component_action'  => 'complete_group_profile'
				)
			);
		} else {
			return true;
		}
	}

	/**
	 *  Remove notifications if the group profile association is lifted.
	 *
 	 *  @param  	int $user_id
 	 *  @param  	int $group_id
 	 *  @param  	int $field_group_id
	 *  @return 	int|bool ID of the newly created notification on success, false
 	 *              on failure.
	 *  @since    	1.0.0
	 */
	public function delete_group_profile_nag_notification( $user_id, $group_id, $field_group_id ) {
		if ( empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}
		if ( empty( $group_id ) ) {
			$group_id = bp_get_current_group_id();
		}

		// Bail out if we don't know who shot what in the what-now!
		if ( empty( $group_id ) || empty( $group_id ) ) {
			return false;
		}

		return bp_notifications_delete_notifications_by_item_id( $user_id, $group_id, 'groups', 'complete_group_profile', $field_group_id );
	}

	/**
	 *  Mark notifications as read.
	 *
 	 *  @param  	int $user_id
 	 *  @param  	int $group_id
 	 *  @param  	int $field_group_id
	 *  @return 	bool True on success, false on failure.
	 *  @since    	1.0.0
	 */
	public function mark_group_profile_nag_notification( $user_id, $group_id, $field_group_id ) {
		if ( empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}
		if ( empty( $group_id ) ) {
			$group_id = bp_get_current_group_id();
		}

		// Bail out if we don't know who shot what in the what-now!
		if ( empty( $user_id ) || empty( $group_id ) ) {
			return false;
		}

		return bp_notifications_mark_notifications_by_item_id( $user_id, $group_id, 'groups', 'complete_group_profile', $field_group_id, false );
	}

	/**
	 *  Create the body text of our notifications.
	 *
 	 *  @param  	object $notification
 	 *  @param  	int $item_id
 	 *  @param  	int $secondary_item_id
  	 *  @param  	int $total_items
  	 *  @param  	string $format Format to return the notification as.
	 *  @return 	array|string The notification
	 *  @since    	1.0.0
	 */
	public function group_profile_notification_description( $notification, $item_id, $secondary_item_id, $total_items, $format = 'string' ) {

			if ( $total_items > 1 ) {
				$text = __( 'Please complete your Hub profiles', $this->plugin_name );
				// @TODO: Switch back to this.
				// $notification_link = trailingslashit( bp_loggedin_user_domain() . bp_get_profile_slug() . '/edit' );
				$notification_link = trailingslashit( bp_loggedin_user_domain() . buddypress()->profile->slug . '/edit' );

				if ( 'string' == $format ) {
					return '<a href="' . $notification_link . '" title="Complete your Hub profile.">' . $text . '</a>';
				} else {
					return array(
						'link' => $notification_link,
						'text' => $text
					);
				}

			} else {
				$group_id = $item_id;
				$fieldgroup_id = $secondary_item_id;
				// @TODO: Specify the profile group name if more than one if attached to a single hub.
				$group = groups_get_group( array( 'group_id' => $group_id ) );
				// $group_link = bp_get_group_permalink( $group );
				$text = sprintf( __( 'Please complete your profile for the Hub %s', $this->plugin_name ), $group->name );
				// $notification_link = $group_link . 'admin/membership-requests/?n=1';
				// @TODO: Switch back to this.
				// $notification_link = trailingslashit( bp_loggedin_user_domain() . bp_get_profile_slug() . '/edit/group/' . $fieldgroup_id );
				$notification_link = trailingslashit( bp_loggedin_user_domain() . buddypress()->profile->slug . '/edit/group/' . $fieldgroup_id );


				if ( 'string' == $format ) {
					return '<a href="' . $notification_link . '" title="Complete your Hub profile.">' . $text . '</a>';
				} else {
					return array(
						'link' => $notification_link,
						'text' => $text
					);
				}
			}
	}

	/**
	 *  When a member joins a group that requires extra profile info, notify that user.
	 *
 	 *  @param  	int $user_id
 	 *  @param  	int $group_id
	 *  @return 	void
	 *  @since    	1.0.0
	 */
	public function join_group_profile_notification_handler( $group_id, $user_id ) {
		// Does the group require extra profile info?
		$field_group_ids = grpf_get_associated_field_groups( $group_id );

		foreach ( $field_group_ids as $add_id ) {
			$this->create_group_profile_nag_notification( $user_id, $group_id, $add_id );
		}
	}

	/**
	 *  When a member joins a group via a request or invitation, and that group
	 *  requests more info, notify the user.
	 *
  	 *  @param  	int $user_id
 	 *  @param  	int $group_id
	 *  @return 	void
	 *  @since    	1.0.0
	 */
	public function invite_request_profile_notification_handler( $user_id, $group_id ) {
		// Does the group require extra profile info?
		$field_group_ids = grpf_get_associated_field_groups( $group_id );

		foreach ( $field_group_ids as $add_id ) {
			$this->create_group_profile_nag_notification( $user_id, $group_id, $add_id );
		}
	}

	/**
	 *  When a member fills out the required extra profile info, mark the notification as read.
	 *
	 *  @param int   $user_id          Displayed user ID.
	 *  @param array $posted_field_ids Array of field IDs that were edited.
	 *  @param bool  $errors           Whether or not any errors occurred.
	 *  @param array $old_values       Array of original values before updated.
	 *  @param array $new_values       Array of newly saved values after update.
	 *  @return 	void
	 *  @since    	1.0.0
	 */
	public function maybe_mark_read_profile_notification( $user_id, $posted_field_ids, $errors, $old_values, $new_values ) {
		$field_group_id = bp_action_variable( 1 );
		$group_ids = grpf_get_associated_groups_for_field_group( $field_group_id );

		foreach ($group_ids as $group_id) {
			$this->mark_group_profile_nag_notification( $user_id, $group_id, $field_group_id );
		}

	}

	/* PROFILE FORM DISPLAY AND SAVE HANDLING ********************************/

	/**
	 *  Output the required profile field form for a specific group.
	 *
	 *  @param 		int   $group_id         Group ID to base our work on.
	 *  @return 	html - form fields
	 *  @since    	1.0.0
	 */
	public function add_profile_field_group_form( $group_id = 0 ) {
		if ( empty( $group_id ) ) {
			$group_id = bp_get_current_group_id();
		}

		if ( empty( $group_id ) ) {
			return;
		}

		$field_group_ids = grpf_get_associated_field_groups( $group_id );

		foreach ( $field_group_ids as $field_group_id ) {
			grpf_output_profile_group_form_fields( $field_group_id );
		}
	}

	/**
	 *  Do something with the extra data submitted along with group membership requests.
	 *  This comes almost verbatim from bp-xprofile-screens.php.
	 *
	 *  @param 		int   $group_id         Group ID to base our work on.
	 *  @return 	html - form fields
	 *  @since    	1.0.0
	 */
	public function process_group_requests_profile_form( $requesting_user_id, $admins, $group_id, $membership_id ) {
		// Maybe @TODO: Do we need to check that this user can fill out the form? Seems unlikely?
		$this->save_profile_form_from_post( $requesting_user_id );
	}

	/**
	 *  Do something with the extra data submitted along with group membership requests.
	 *  This comes almost verbatim from bp-xprofile-screens.php.
	 *
	 *  @param 		int   $group_id         Group ID to base our work on.
	 *  @return 	html - form fields
	 *  @since    	1.0.0
	 */
	public function save_profile_form_from_post( $user_id ) {
		// Check to see if any new information has been submitted
		if ( ! empty( $_POST['field_ids'] ) ) {

			// Check the nonce
			if ( ! wp_verify_nonce( $_POST[ 'grpf_profile_fields_nonce' ], 'grpf_profile_fields' ) ) {
				return;
			}

			// Explode the posted field IDs into an array so we know which
			// fields have been submitted
			$posted_field_ids = wp_parse_id_list( $_POST['field_ids'] );
			$is_required      = array();

			// Loop through the posted fields formatting any datebox values
			// then validate the field
			foreach ( (array) $posted_field_ids as $field_id ) {
				if ( !isset( $_POST['field_' . $field_id] ) ) {

					if ( !empty( $_POST['field_' . $field_id . '_day'] ) && !empty( $_POST['field_' . $field_id . '_month'] ) && !empty( $_POST['field_' . $field_id . '_year'] ) ) {
						// Concatenate the values
						$date_value =   $_POST['field_' . $field_id . '_day'] . ' ' . $_POST['field_' . $field_id . '_month'] . ' ' . $_POST['field_' . $field_id . '_year'];

						// Turn the concatenated value into a timestamp
						$_POST['field_' . $field_id] = date( 'Y-m-d H:i:s', strtotime( $date_value ) );
					}

				}

				$is_required[$field_id] = xprofile_check_is_required_field( $field_id );
				if ( $is_required[$field_id] && empty( $_POST['field_' . $field_id] ) ) {
					$errors = true;
				}
			}

			// There are errors
			if ( ! empty( $errors ) ) {
				bp_core_add_message( __( 'Please make sure you fill in all required fields in this profile field group before saving.', 'buddypress' ), 'error' );

			// No errors
			} else {

				// Reset the errors var
				$errors = false;

				// Now we've checked for required fields, let's save the values.
				$old_values = $new_values = array();
				foreach ( (array) $posted_field_ids as $field_id ) {

					// Certain types of fields (checkboxes, multiselects) may come through empty. Save them as an empty array so that they don't get overwritten by the default on the next edit.
					$value = isset( $_POST['field_' . $field_id] ) ? $_POST['field_' . $field_id] : '';

					$visibility_level = !empty( $_POST['field_' . $field_id . '_visibility'] ) ? $_POST['field_' . $field_id . '_visibility'] : 'public';

					// Save the old and new values. They will be
					// passed to the filter and used to determine
					// whether an activity item should be posted
					$old_values[ $field_id ] = array(
						'value'      => xprofile_get_field_data( $field_id, $user_id ),
						'visibility' => xprofile_get_field_visibility_level( $field_id, $user_id ),
					);

					// Update the field data and visibility level
					xprofile_set_field_visibility_level( $field_id, $user_id, $visibility_level );
					$field_updated = xprofile_set_field_data( $field_id, $user_id, $value, $is_required[ $field_id ] );
					$value         = xprofile_get_field_data( $field_id, $user_id );

					$new_values[ $field_id ] = array(
						'value'      => $value,
						'visibility' => xprofile_get_field_visibility_level( $field_id, $user_id ),
					);

					if ( ! $field_updated ) {
						$errors = true;
					} else {

						/**
						 * Fires on each iteration of an XProfile field being saved with no error.
						 *
						 * @since BuddyPress (1.1.0)
						 *
						 * @param int    $field_id ID of the field that was saved.
						 * @param string $value    Value that was saved to the field.
						 */
						do_action( 'xprofile_profile_field_data_updated', $field_id, $value );
					}
				}

				/**
				 * Fires after all XProfile fields have been saved for the current profile.
				 *
				 * @since BuddyPress (1.0.0)
				 *
				 * @param int   $value            Displayed user ID.
				 * @param array $posted_field_ids Array of field IDs that were edited.
				 * @param bool  $errors           Whether or not any errors occurred.
				 * @param array $old_values       Array of original values before updated.
				 * @param array $new_values       Array of newly saved values after update.
				 */
				do_action( 'xprofile_updated_profile', $user_id, $posted_field_ids, $errors, $old_values, $new_values );

				// Set the feedback messages
				// if ( !empty( $errors ) ) {
				// 	bp_core_add_message( __( 'There was a problem updating some of your profile information. Please try again.', 'buddypress' ), 'error' );
				// } else {
				// 	bp_core_add_message( __( 'Changes saved.', 'buddypress' ) );
				// }
			}
		}

	}

	/* SAVE/DELETE XPROFILE META *********************************************/

	/**
	 *  Add groupmeta associating a profile field group with a hub.
	 *
	 *  @param  $field_group_id  The ID of the field_group we're associating.
	 *  @param  $hub_id          The ID of the hub.
	 *  @return bool|int
	 *  @since  1.0.0
	 */
	public function associate_hub_with_field_group( $field_group_id, $hub_id ) {
		bp_xprofile_add_meta( $field_group_id, 'group', 'associated_with_hub', $hub_id, false );
	}

	/**
	 *  Remove groupmeta associating a profile field group with a hub.
	 *
	 *  @param  $field_group_id  The ID of the field_group we're associating.
	 *  @param  $hub_id          The ID of the hub.
	 *  @return bool|int
	 *  @since  1.0.0
	 */
	public function disassociate_hub_from_field_group( $field_group_id, $hub_id ) {
		bp_xprofile_delete_meta( $field_group_id, 'group', 'associated_with_hub', $hub_id, false );
	}

	/* STATUS CHECKS *********************************************************/

	/**
	 * Return whether the user is member of one of the fieldgroup's related
	 * user groups.
	 *
	 * @since 1.0.0
	 *
	 * @uses get_current_user_id()
	 * @uses groups_get_groups()
	 *
	 * @param int $fieldgroup_id Field group ID
	 * @param int $user_id Optional. User ID. Defaults to the current.
	 * @return bool User is field group's user group member
	 */
	public function is_user_fieldgroup_member( $field_group_id, $user_id = 0 ) {

		// Bail if this is the primary fieldgroup
		if ( 1 == $field_group_id ) {
			return true;
		}

		// Default to logged in user
		if ( empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}

		// Get the hubs that are associated with the field group.
		$assoc_group_ids = grpf_get_associated_groups_for_field_group( $field_group_id );

		// Some fields are associated with the profile field group.
		if ( ! empty( $assoc_group_ids ) ) {

			// Is the visiting user a member of any of the groups?
			// Get the user's group memberships
			$user_groups = array();
			$user_groups_result = groups_get_user_groups( $user_id );
			if ( ! empty( $user_groups_result['groups'] )  ) {
				$user_groups = $user_groups_result['groups'];
			}

			if ( array_intersect( $assoc_group_ids, $user_groups ) ) {
				return true;
			}

			// If the user is looking at his own profile, then allow him to see/edit
			// profile groups for groups they have requested membership in.
			if ( bp_is_my_profile() ) {
				foreach ( $assoc_group_ids as $assoc_group_id ) {
					if ( groups_check_for_membership_request( $user_id, $assoc_group_id ) ) {
						return true;
					}
				}
			}

			// If we've made it this far, none of those things are true, so return false.
			return false;

		// No groups were assigned, so user has access
		} else {
			return true;
		}
	}

	public function filter_xprofile_screen_edit_profile_success_message( $message, $field_group_id ) {
		if ( ! empty( $field_group_id ) ) {
			$group_ids = grpf_get_associated_groups_for_field_group( $field_group_id );
			if ( ! empty( $group_ids ) ) {
				$counter = 1;
				$message .= ' Visit a related Hub: ';
				foreach ( $group_ids as $group_id ) {
					$group_obj = groups_get_group( array( 'group_id' => $group_id ) );
					if ( $counter > 1 ) {
						$message .= ', ';
					}
					$message .= '<a href="' . bp_get_group_permalink( $group_obj ) . '">' . bp_get_group_name( $group_obj ). '</a>';
					$counter++;
				}
			}
		}

		return $message;
	}

	/* BULLPEN ***************************************************************/

	/**
	 *  Don't save a group membership request if more data is required.
	 *
 	 *  @param  bool  $continue            May the request proceed?
	 *  @param  obj   $requesting_user     Requesting_user membership object.
	 *  @return bool True allows the request, false stops it.
	 *  @since  Not currently used.
	 */
	public function pre_membership_request_check( $continue, $requesting_user ) {
		// Explode the posted field IDs into an array so we know which
		// fields have been submitted
		if ( isset( $_POST['field_ids'] ) ) {
			$posted_field_ids = wp_parse_id_list( $_POST['field_ids'] );
			$is_required      = array();

			// Loop through the posted fields formatting any datebox values
			// then validate the field
			foreach ( (array) $posted_field_ids as $field_id ) {
				if ( ! isset( $_POST['field_' . $field_id] ) ) {

					if ( !empty( $_POST['field_' . $field_id . '_day'] ) && !empty( $_POST['field_' . $field_id . '_month'] ) && !empty( $_POST['field_' . $field_id . '_year'] ) ) {
						// Concatenate the values
						$date_value =   $_POST['field_' . $field_id . '_day'] . ' ' . $_POST['field_' . $field_id . '_month'] . ' ' . $_POST['field_' . $field_id . '_year'];

						// Turn the concatenated value into a timestamp
						$_POST['field_' . $field_id] = date( 'Y-m-d H:i:s', strtotime( $date_value ) );
					}

				}

				$is_required[$field_id] = xprofile_check_is_required_field( $field_id );
				if ( $is_required[$field_id] && empty( $_POST['field_' . $field_id] ) ) {
					$continue = false;
					break;
				}
			}
		}
		return $continue;
	}
} // End class
<?php

/**
 * Provide a public-facing view for the plugin
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    CCC_Group_Related_Profile_Fields
 * @subpackage CC_Group_Related_Profile_Fields/public/partials
 */

/**
 *  Output a specific profile field group's edit fields.
 *  This assumes that it'll be added to another form and the input will be
 *  handled as part of the parsing of the other form!
 *
 *  @param  	int $field_group
 *  @return 	html form fields for inclusion in another form
 *  @since    	1.0.0
 */
function grpf_output_profile_group_form_fields( $field_group_id ) {

	if ( bp_has_profile( array( 'profile_group_id' => $field_group_id, 'fetch_visibility_level' => true ) ) ) :
		while ( bp_profile_groups() ) : bp_the_profile_group(); ?>

		<?php
			/** This action is documented in bp-templates/bp-legacy/buddypress/members/single/profile/profile-wp.php */
			do_action( 'bp_before_profile_field_content' ); ?>

			<h4><?php printf( __( "More information: %s", "cc-grpf" ), bp_get_the_profile_group_name() ); ?></h4>

			<?php while ( bp_profile_fields() ) : bp_the_profile_field(); ?>

				<div<?php bp_field_css_class( 'editfield' ); ?>>

					<?php
					$field_type = bp_xprofile_create_field_type( bp_get_the_profile_field_type() );
					$field_type->edit_field_html();

					/**
					 * Fires before the display of visibility options for the field.
					 *
					 * @since BuddyPress (1.7.0)
					 */
					do_action( 'bp_custom_profile_edit_fields_pre_visibility' );
					?>

					<?php if ( bp_current_user_can( 'bp_xprofile_change_field_visibility' ) ) : ?>
						<p class="field-visibility-settings-toggle" id="field-visibility-settings-toggle-<?php bp_the_profile_field_id() ?>">
							<?php printf( __( 'This field can be seen by: <span class="current-visibility-level">%s</span>', 'buddypress' ), bp_get_the_profile_field_visibility_level_label() ) ?> <a href="#" class="visibility-toggle-link"><?php _e( 'Change', 'buddypress' ); ?></a>
						</p>

						<div class="field-visibility-settings" id="field-visibility-settings-<?php bp_the_profile_field_id() ?>">
							<fieldset>
								<legend><?php _e( 'Who can see this field?', 'buddypress' ) ?></legend>

								<?php bp_profile_visibility_radio_buttons() ?>

							</fieldset>
							<a class="field-visibility-settings-close" href="#"><?php _e( 'Close', 'buddypress' ) ?></a>
						</div>
					<?php else : ?>
						<div class="field-visibility-settings-notoggle" id="field-visibility-settings-toggle-<?php bp_the_profile_field_id() ?>">
							<?php printf( __( 'This field can be seen by: <span class="current-visibility-level">%s</span>', 'buddypress' ), bp_get_the_profile_field_visibility_level_label() ) ?>
						</div>
					<?php endif ?>

					<?php

					/**
					 * Fires after the visibility options for a field.
					 *
					 * @since BuddyPress (1.1.0)
					 */
					do_action( 'bp_custom_profile_edit_fields' ); ?>

					<p class="description"><?php bp_the_profile_field_description(); ?></p>
				</div>

			<?php endwhile; ?>

		<?php

		/** This action is documented in bp-templates/bp-legacy/buddypress/members/single/profile/profile-wp.php */
		do_action( 'bp_after_profile_field_content' ); ?>

		<input type="hidden" name="field_ids" id="field_ids" value="<?php bp_the_profile_field_ids(); ?>" />

		<?php wp_nonce_field( 'grpf_profile_fields', 'grpf_profile_fields_nonce' ); ?>

	<?php endwhile; endif;
}

/**
 *  Display a field group's field data for a specified user.
 *
 *  @param  	int $field_group_id
 *  @param  	int $user_id
 *  @return 	html
 *  @since    	1.0.0
 */
function grpf_output_profile_group_form_field_entries( $field_group_id, $user_id ) {
	$args = array(
		'user_id' => $user_id,
		'profile_group_id' => $field_group_id,
		);
	if ( bp_has_profile( $args ) ) : ?>

	<?php while ( bp_profile_groups() ) : bp_the_profile_group(); ?>

		<?php if ( bp_profile_group_has_fields() ) : ?>

			<div class="bp-widget <?php bp_the_profile_group_slug(); ?>">

				<table class="profile-fields">

					<?php while ( bp_profile_fields() ) : bp_the_profile_field(); ?>

						<?php if ( bp_field_has_data() ) : ?>

							<tr<?php bp_field_css_class(); ?>>

								<td class="label"><?php bp_the_profile_field_name(); ?></td>

								<td class="data"><?php bp_the_profile_field_value(); ?></td>

							</tr>

						<?php endif; ?>

						<?php

						/**
						 * Fires after the display of a field table row for profile data.
						 *
						 * @since BuddyPress (1.1.0)
						 */
						do_action( 'bp_profile_field_item' ); ?>

					<?php endwhile; ?>

				</table>
			</div>

			<?php

			/** This action is documented in bp-templates/bp-legacy/buddypress/members/single/profile/profile-wp.php */
			do_action( 'bp_after_profile_field_content' ); ?>

		<?php endif; ?>

	<?php endwhile; ?>

	<?php

	/** This action is documented in bp-templates/bp-legacy/buddypress/members/single/profile/profile-wp.php */
	do_action( 'bp_profile_field_buttons' ); ?>

<?php endif;
}

/**
 *  Display a field group's field data for a specified user in an unordered list.
 *
 *  @param  	int $field_group_id
 *  @param  	int $user_id
 *  @return 	html
 *  @since    	1.0.0
 */
function grpf_output_profile_group_form_field_entries_no_table( $field_group_id, $user_id ) {
	$args = array(
		'user_id' => $user_id,
		'profile_group_id' => $field_group_id,
		);
	if ( bp_has_profile( $args ) ) : ?>

	<?php while ( bp_profile_groups() ) : bp_the_profile_group(); ?>

		<?php if ( bp_profile_group_has_fields() ) : ?>

			<div class="profile-field-list <?php bp_the_profile_group_slug(); ?>">

				<ul class="profile-fields">

					<?php while ( bp_profile_fields() ) : bp_the_profile_field(); ?>

						<?php if ( bp_field_has_data() ) : ?>

							<li<?php bp_field_css_class(); ?>>

								<span class="label"><?php bp_the_profile_field_name(); ?>:</span>

								<span class="data"><?php bp_the_profile_field_value(); ?></span>

							</li>

						<?php endif; ?>

						<?php

						/**
						 * Fires after the display of a field table row for profile data.
						 *
						 * @since BuddyPress (1.1.0)
						 */
						do_action( 'bp_profile_field_item' ); ?>

					<?php endwhile; ?>

				</ul>
			</div>

			<?php

			/** This action is documented in bp-templates/bp-legacy/buddypress/members/single/profile/profile-wp.php */
			do_action( 'bp_after_profile_field_content' ); ?>

		<?php endif; ?>

	<?php endwhile; ?>

	<?php

	/** This action is documented in bp-templates/bp-legacy/buddypress/members/single/profile/profile-wp.php */
	do_action( 'bp_profile_field_buttons' ); ?>

<?php endif;
}

/**
 *  Create an html string of the hubs that are associated with a field group.
 *
 *  @param  	int $field_group_id
 *  @return 	array of related group ids
 *  @since    	1.0.0
 */
function grpf_human_readable_associated_group_markup( $field_group_id, $html = false ) {
	$used_with_groups = grpf_get_associated_groups_for_field_group( $field_group_id );
	if ( ! empty( $used_with_groups ) ) {
		$associated_groups = groups_get_groups( array( 'include' => $used_with_groups ) );
		$group_names = wp_list_pluck( $associated_groups['groups'], 'name' );
		$retval =  'Currently associated with: ' . implode( ', ', $group_names );
		if ( $html ) {
			$retval =  '<br /><span class="description">' . $retval . '</span>';
		}
		$retval = ' ' . $retval;
		return $retval;
	}
}
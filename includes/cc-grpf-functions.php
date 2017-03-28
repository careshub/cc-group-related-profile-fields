<?php
/**
 * General-purpose functions
 *
 * A class definition that includes attributes and functions used on both the
 * public-facing side of the site and the dashboard.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    CC_Group_Related_Profile_Fields
 * @subpackage CC_Group_Related_Profile_Fields/includes
 */
function grpf_user_groups_with_profile_groups( $user_id = 0 ) {
	if ( empty( $user_id ) ) {
		if ( bp_is_user() ) {
			$user_id = bp_displayed_user_id();
		} else {
			$user_id = get_current_user_id();
		}
	}

	echo "user_id: " . $user_id;

}

/**
 *  Get the field groups that are associated with a hub.
 *
 *  @param  	int $hub_id
 *  @return 	array of related profile field group ids
 *  @since    	1.0.0
 */
function grpf_get_associated_field_groups( $hub_id ) {
	global $wpdb;
	$bp = buddypress();

	// @TODO: Seems like there should be a better way, but maybe not.
	// Maybe use a profile meta query

	$field_group_ids = $wpdb->get_col( $wpdb->prepare(
			"
			SELECT      object_id
			FROM        {$bp->profile->table_name_meta}
			WHERE       object_type = 'group'
						AND meta_key = 'associated_with_hub'
			            AND meta_value = %s
			ORDER BY    object_id
			",
			$hub_id
		)
	);

	return array_map( 'intval', $field_group_ids );
}

/**
 *  Get the hubs that are associated with a field group.
 *
 *  @param  	int $hub_id
 *  @return 	array of related group ids
 *  @since    	1.0.0
 */
function grpf_get_associated_groups_for_field_group( $field_group_id ) {

	$group_ids = bp_xprofile_get_meta( $field_group_id, 'group', 'associated_with_hub', false );

	if ( $group_ids === false ) {
		$group_ids = array();
	}

	return array_map( 'intval', $group_ids );
}
/**
 *  Get users who have an entry for a specific profile field.
 *
 *  @param  	int $hub_id
 *  @return 	array of related group ids
 *  @since    	1.0.0
 */
function grpf_get_users_with_entry_for_field( $field_id ) {
	global $wpdb;
	$bp = buddypress();

	// @TODO: Seems like there should be a better way, but maybe not.
	// Maybe use a profile meta query

	$user_ids = $wpdb->get_col( $wpdb->prepare(
			"
			SELECT      user_id
			FROM        {$bp->profile->table_name_data}
			WHERE       field_id = %d
			",
			$field_id
		)
	);

	return array_map( 'intval', $user_ids );
}
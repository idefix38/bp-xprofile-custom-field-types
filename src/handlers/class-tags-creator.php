<?php
/**
 * Handles the tags creation/validation for tags field.
 *
 * @package    BuddyPress Xprofile Custom Field Types Reloaded
 * @subpackage Handlers
 * @copyright  Copyright (c) 2018, Brajesh Singh
 * @license    https://www.gnu.org/licenses/gpl.html GNU Public License
 * @author     Brajesh Singh
 * @since      1.0.0
 */

namespace BPXProfileCFTR\Handlers;

use BPXProfileCFTR\Field_Types\Field_Type_Tags;

// Do not allow direct access over web.
defined( 'ABSPATH' ) || exit;

/**
 * Tags creator.
 */
class Tags_Creator {

	/**
	 * Setup the bootstrapper.
	 */
	public static function boot() {
		$self = new self();
		$self->setup();
	}

	/**
	 * Bind hooks
	 */
	private function setup() {
		// Pre validate tags.
		add_filter( 'bp_xprofile_set_field_data_pre_validate', array( $this, 'sanitize' ), 10, 2 );
	}

	/**
	 * Sanitize Value.
	 *
	 * @param mixed              $value value.
	 * @param \BP_XProfile_Field $field field object.
	 *
	 * @return array|string
	 */
	public function sanitize( $value, $field ) {
		// store Field's reference to allow us fetch it when validating.
		bpxcftr_set_current_field( $field );

		if ( 'tags' !== $field->type || empty( $value ) ) {
			return $value;
		}

		$allow_new_tags = Field_Type_Tags::allow_new_tags( $field->id );

		// Add new tags if needed.
		$sanitized = array();

		$field_options = (array) $field->get_children( true );

		foreach ( $value as $tag ) {

			if ( in_array( $tag, $field_options ) ) {
				$sanitized[] = $tag;
			} elseif ( $allow_new_tags ) {
				$field_id = xprofile_insert_field(
					array(
						'field_group_id' => $field->group_id,
						'parent_id'      => $field->id,
						'type'           => 'option',
						'name'           => $tag,
					)
				);

				if ( $field_id ) {
					$field       = xprofile_get_field( $field_id );
					$sanitized[] = $field->name;
				}
			}
		}

		return $sanitized;
	}

}

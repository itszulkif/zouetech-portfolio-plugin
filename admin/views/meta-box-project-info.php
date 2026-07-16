<?php
/**
 * Project Information meta section.
 *
 * @package Zouetech_Portfolio
 * @var WP_Post $post
 */

defined( 'ABSPATH' ) || exit;

$ztp_fields  = Zouetech_Portfolio_Meta_Fields::get_fields_by_group( 'project_info' );
$ztp_status  = Zouetech_Portfolio_Helpers::get_meta( $post->ID, '_ztp_project_status', 'completed' );
$ztp_choices = Zouetech_Portfolio_Meta_Fields::get_status_choices();
?>
<div class="ztp-field-grid">
	<?php foreach ( $ztp_fields as $ztp_key => $ztp_field ) : ?>
		<?php
		if ( '_ztp_project_status' === $ztp_key ) {
			continue;
		}
		$ztp_value = Zouetech_Portfolio_Helpers::get_meta( $post->ID, $ztp_key, '' );
		$ztp_type  = ( 'date' === $ztp_field['type'] ) ? 'date' : 'text';
		?>
		<div class="ztp-field">
			<label class="ztp-field__label" for="<?php echo esc_attr( $ztp_key ); ?>"><?php echo esc_html( $ztp_field['label'] ); ?></label>
			<input class="ztp-field__input" type="<?php echo esc_attr( $ztp_type ); ?>" id="<?php echo esc_attr( $ztp_key ); ?>" name="<?php echo esc_attr( $ztp_key ); ?>" value="<?php echo esc_attr( (string) $ztp_value ); ?>" />
		</div>
	<?php endforeach; ?>

	<div class="ztp-field">
		<label class="ztp-field__label" for="_ztp_project_status"><?php esc_html_e( 'Project Status', 'zouetech-portfolio' ); ?></label>
		<select class="ztp-field__input" id="_ztp_project_status" name="_ztp_project_status">
			<?php foreach ( $ztp_choices as $ztp_val => $ztp_label ) : ?>
				<option value="<?php echo esc_attr( $ztp_val ); ?>" <?php selected( $ztp_status, $ztp_val ); ?>><?php echo esc_html( $ztp_label ); ?></option>
			<?php endforeach; ?>
		</select>
	</div>
</div>

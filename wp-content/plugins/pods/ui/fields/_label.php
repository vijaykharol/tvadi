<?php
// Don't load directly.
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}
?>
<label<?php PodsForm::attributes( $attributes, $name, 'label' ); ?>>
	<?php
	echo pods_kses_exclude_p( $label );

	if ( 1 == pods_var( 'required', $options, pods_var( 'options', $options, $options ) ) ) {
		echo ' <abbr title="required" class="required">*</abbr>';
	}

	if ( 0 == pods_var( 'grouped', $options, 0, null, true ) && ! empty( $help ) && __( 'help', 'pods' ) !== $help ) {
		pods_help( $help );
	}
	?>
</label>

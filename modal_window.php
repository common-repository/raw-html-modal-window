<?php
/**
 *  Plugin Name: Raw HTML Modal Window
 *  Plugin URI: 
 *  Description: Show Modal Window on any page or post by specifying post id. This plugin is intended for users that feel comfortable editing HTML and CSS code for ultimate control of a pop-up window.
 *  Version: 1.1
 *  Author: electric-fire
 *  Author URI: https://f-guitar.com/office
 *  
 *  License: GNU General Public License v2.0 (or later)
 *  License URI: http://www.opensource.org/licenses/gpl-license.php
 */
 

// Register function to be called when plugin is activated
register_activation_hook( __FILE__, function() {
	efmw_get_options();
});


function efmw_get_options() {

	$options = get_site_option( 'efmw_options', array() );

	$new_options['only_on_pages'] = ''; 
	$new_options['item_disabled'] = '0'; 
	$new_options['item_content'] = '<h2>Your Message</h2>';
	$new_options['extra_styles'] = '';

	$stylesheet_location = plugin_dir_path( __FILE__ ) . 'includes/css/default-user-styles.css';
	$new_options['extra_styles'] = file_get_contents( $stylesheet_location );
 
	$new_options['mask_opacity'] = 80; /* Default: 80 */
	$new_options['z_index'] = 1000; /* Common values: 1000 along with 9999 */
	$new_options['delay'] = 1; 
	$new_options['fade_in_duration'] = 500; 
	## $new_options['fade_out_duration'] = 1000; 


	/* *************************************************** */
	/*
	 * For each array element that is not found 
	 * in the existing options, wp_parse_args will 
	 * merge it into the resulting array.
	 */
    $merged_options = wp_parse_args( $options, $new_options ); 
	/* *************************************************** */

	
	/* *************************************************** */
	/* Check whether the previous option array was empty 
	 * or whether any new keys were added to the new array:
	 */
	$compare_options = array_diff_key( $new_options, $options );   

	if ( empty( $options ) || !empty( $compare_options ) ) {
		update_site_option( 'efmw_options', $merged_options );
	}
	/* *************************************************** */

	return $merged_options; /* Currently not used */

} // End of efmw_get_options() function.


define( 'EFMW', 1 );

// Check if user is visiting administration pages 
// and load external code file if that is the case:
if ( is_admin() ) {
	include( plugin_dir_path( __FILE__ ) . 'admin.php' );
}


// *********************************************** //
// ************ DISPLAYING THE RESULT ************ //

function efmw_display_modal_window( $opt ) {

	echo '<script>
		var efmw_obj = {};
		efmw_obj.delay_time = parseInt( ' . $opt['delay'] . ', 10 );
		efmw_obj.fade_out_duration = parseInt( ' . $opt['fade_in_duration'] . ', 10 );
	</script>';

	echo '<script>' . file_get_contents( plugin_dir_path( __FILE__ ) . 'includes/js/modal_window.js' ) . '</script>';


				/* Note:
				 * Four <div> structure for modal window is used 
				 * becaue three <div> gets partially covered by 
				 * the header with some themes.
				 */
?>
		 <div id="efmw_modal" style="transition-duration:<?php echo $opt['fade_in_duration']; ?>ms;">
		 <div id="efmw_modalMask" <?php

				echo 'style="opacity:' . 
					( (int) $opt['mask_opacity'] / 100 ) . 
';filter:alpha(opacity=' . 
$opt['mask_opacity'] . 
');z-index:' .
$opt['z_index'] . 
'"></div>';
?>
	<div id="efmw_modalFixedDiv" style="z-index:<?php 

echo ( (int) $opt['z_index'] + 8999 ); 

?>">
				<div id="efmw_modalContent">
					<a alt="Close Modal Window" title="Close Modal Window" id="efmw_closeModal" style="/* transition-duration:<?php /* echo $opt['fade_out_duration']; */ ?>ms; */">×</a>
					<?php echo $opt['item_content']; ?>
				</div>
			</div>
		</div>
<?php

} // End of efmw_display_modal_window() function.


function efmw_check_page( $opt ) {
	$flag = false;

	if ( ( empty( $opt['only_on_pages'] ) )  && is_front_page() ) {

		$flag = true;

	} elseif (  empty( $opt['only_on_pages'] )   ) {

		$flag = false;

	} else {

		$arr = explode( ' ', $opt['only_on_pages'] );

		foreach ( $arr as $v ) {

			if ( (int) $v === (int) get_the_ID() ) {
				$flag = true;
				break;
			}
		} // End of FOREACH loop.

	} // End of IF-ELSE.

	return $flag;

} // End of efmw_check_page() function.




// Register function to add output to page footer:
add_action( 'init', function() {

	// For debugging:
	/*
	add_action( 'wp_head', function() {

		$options = efmw_get_options();
		if ( efmw_check_page( $options ) ) {
			echo 'true';
		} else {
			echo 'false';
		}
		exit;
	} ); // End of wp_head.
	 */


	$options = efmw_get_options();

	if ( isset( $options['item_disabled'] ) && ( (int) $options['item_disabled'] === 1 ) ) {
		return;
	} // End of item_disabled IF.


	// DO NOT CHANGE INDENTATION:
	add_action( 'wp_head', function() {

		$options = efmw_get_options();
		if ( !efmw_check_page( $options ) ) {
			return;
		}
		// Output base styles:
		echo '<style>
			' . file_get_contents( plugin_dir_path( __FILE__ ) . 'includes/css/styles.css' ) . '
			</style>
';

		// Output styles entered by user:
		if ( !empty( $options['extra_styles'] ) ) {
?>
						<style type="text/css">
						<?php echo $options['extra_styles']; ?>
						</style>
<?php
		}

	} ); // End of wp_head add_action.



		add_action( 'wp_footer', function() { // Need this extra add_action() function()to solve the checkbox auto checking and page id not available issue.

			$options = efmw_get_options();

			if ( efmw_check_page( $options ) ) {
					efmw_display_modal_window( $options );
			}

		} ); // End of wp_footer.


} ); // End add_action init.


// ************ DISPLAYING THE RESULT ************ //
// *********************************************** //

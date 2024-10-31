<?php # Script - admin.php

/**
 * Creation date: November 11, 2018
 * Last modified: Wed 14-11-2018  04:34
 * Created by: electric-fire
 * Contact: f-guitar.com/office
 */


if ( !defined( 'EFMW' ) ) {
	exit;
}


// *********************************************** //
// ************ DISPLAYING ADMIN MENU ************ //

// Create top-level menu item
add_action( 'admin_menu', function() {

	$options_page = add_menu_page( 
		'Modal Window Plugin Configuration Page',
		'Modal Window', 
		'manage_options',
		'efmw-main-menu',
		'render_efmw_config_page',
		plugins_url( 'includes/images/modal_window_icon.png', __FILE__ ) );

	/* ------------------------------------ */
	// Add help:
	if ( !empty( $options_page ) ) {
		add_action( 'load-' . $options_page, 'efmw_help_tabs' );
	}
	/* ------------------------------------ */
} );


function render_efmw_config_page() {

	 // check user capabilities
	 if ( ! current_user_can( 'manage_options' ) ) {
		 return;
	 }

	# --------------------------------------------------- #
	# ------------ ADD ERROR/UPDATE MESSAGES ------------ #
	
	/* IMPORTANT
	 * Should be used with the add_menu_page() function,
	 * the add_options_page() function displays messages on its own.
	*/
	
	 // check if the user have submitted the settings
	 // wordpress will add the "settings-updated" $_GET parameter to the url:

	 if ( isset( $_GET['settings-updated'] ) ) {

		 // add settings saved message with the class of "updated"
		 add_settings_error(
			 'efmw_messages',         // Slug title of the settings to which this error applies.
			 'efmw_message',          // Part of id attribute (HTML) (settings-error-...)
			 __( 'Settings Saved', 'efmw' ), // Message.
			 'updated'                        // HTML class.
		 );
	 }

	 // show error/update messages
	 settings_errors( 'efmw_messages' );

	# ------------ ADD ERROR/UPDATE MESSAGES ------------ #
	# --------------------------------------------------- #
	 


	// Retrieve plugin configuration options from database
	$options = efmw_get_options();
	?>

	<div id="efmw-general" class="wrap">
		<h2>Modal Window Configuration Page</h2>

		<form method="post" action="options.php"><?php /* 

		IMPORTANT
			When using the Settings API the action 
			attribute must be set to "options.php".

		 */ ?>

		<?php settings_fields( 'efmw_settings' ); ?>
		<?php do_settings_sections( 'efmw_settings_page_identifier' ); ?>

		<input type="submit" value="Submit" class="button-primary" />

		</form>
	</div>

<?php

} // End of render_efmw_config_page() function.


// ************ DISPLAYING ADMIN MENU ************ //
// *********************************************** //


// ************************************************* //
// ************ CREATING SETTINGS GROUP ************ //

// Register action hook function to be called when 
// the admin pages are starting to be prepared for display:

add_action( 'admin_init', function() {

	// Register a setting group with a validation function
	// so that post data handling is done automatically for us
	register_setting(
		'efmw_settings',        // Unique identifier for the settings group.
		'efmw_options',         // Name of the options array.
		'efmw_validate_options' // Name of a callback function that will receive user input for validation.
	);

	// Add a new settings section within the group
	add_settings_section(
		'efmw_main_section',                  // Unique identifier for the section.
		'The title of the section',            // Title string.
		'efmw_main_setting_section_callback', // Displays a description for the section.
		'efmw_settings_page_identifier'               // Page identifier.
	);

	// Add the fields with the names and function to use for our new
	// settings, put them in our new section
	add_settings_field(
		'only_on_pages',               // Unique identifier for the field.
	 	'Page ID(s)',         // Label.
		'efmw_display_text_field',    // Outputs the HTML code to display the field.
		'efmw_settings_page_identifier',      // Indicates the page that the field belongs to.
		'efmw_main_section',          // Indicates the section it is contained in.
		array( 'name' => 'only_on_pages' ) // An optional array of additional data to be sent to the callback function.
	);

	add_settings_field(
		'item_disabled',
		'Disabled',
		'efmw_display_check_box',
		'efmw_settings_page_identifier',
		'efmw_main_section',
		array( 'name' => 'item_disabled' )
	);

	add_settings_field(
		'item_content',
		'Window Content',
		'efmw_display_text_area',
		'efmw_settings_page_identifier',
		'efmw_main_section',
		array( 'name' => 'item_content', 'extra_attr' => 'rows="6" cols="80"'  )
	);

	add_settings_field(
		'exta_styles',
		'Custom Styles',
		'efmw_display_text_area',
		'efmw_settings_page_identifier',
		'efmw_main_section',
		array( 'name' => 'extra_styles', 'extra_attr' => 'rows="12" cols="80"'  )
	);

	add_settings_field(
		'mask_opacity',
	 	'Opacity Mask<br /><small>(from 1 to 100)</small>',
		'efmw_display_text_field',
		'efmw_settings_page_identifier',
		'efmw_main_section',
		array( 'name' => 'mask_opacity' )
	);

	add_settings_field(
		'z_index',
	 	'z-index',
		'efmw_display_text_field',
		'efmw_settings_page_identifier',
		'efmw_main_section',
		array( 'name' => 'z_index' )
	);

	add_settings_field(
		'delay',
	 	'Delay (in milliseconds)',
		'efmw_display_text_field',
		'efmw_settings_page_identifier',
		'efmw_main_section',
		array( 'name' => 'delay' )
	);

	add_settings_field(
		'fade_in_duration',
	 	'Fading Time (in milliseconds)',
		'efmw_display_text_field',
		'efmw_settings_page_identifier',
		'efmw_main_section',
		array( 'name' => 'fade_in_duration' )
	);

	/*
	add_settings_field(
		'fade_out_duration',
	 	'Fade Out Time (in milliseconds)',
		'efmw_display_text_field',
		'efmw_settings_page_identifier',
		'efmw_main_section',
		array( 'name' => 'fade_out_duration' )
	);
	 */

} );


// Validation function to be called when data is posted by user
function efmw_validate_options( $input ) {

	/* -------------------------------------------------------- */
	/* Cycle through all text form fields and store their values
	 * in the options array:
	 */

	$i = array( 
		'only_on_pages',
		'opacity',
		'z_index',
		'delay',
		'fade_in_duration',
		## 'fade_out_duration',
		## 'item_content'
		## 'extra_styles' // validation erases new lines
	);

	foreach ( $i as $option_name ) { 
		if ( isset( $input[$option_name] ) ) { 
			$input[$option_name] = sanitize_text_field( $input[$option_name] ); 
		} 
	} 

	/* -------------------------------------------------------- */
	/* Cycle through all check box form fields and set the options
	 * array to true or false values based on presence of
	 * variables:
	 */

	$i = array(
		'item_disabled'
	);

	foreach ( $i as $option_name ) {
		if ( isset( $input[$option_name] ) ) { 
			$input[$option_name] = true; 
		} else { 
			$input[$option_name] = false; 
		} 
	}

	# ------------------------------------------ #
	# ------------ RESETTING STYLES ------------ #

	if ( isset( $_POST['reset_styles'] ) ) {
		$stylesheet_location = plugin_dir_path( __FILE__ ) . 'includes/css/default-user-styles.css';
		$input['extra_styles'] = file_get_contents( $stylesheet_location );
	}
	
	# ------------ RESETTING STYLES ------------ #
	# ------------------------------------------ #


	return $input;

} // End of efmw_validate_options() function.


// Function to display text at the beginning of the main section
function efmw_main_setting_section_callback() {

	echo '<p>This is the main configuration section.</p>';

} // End of efmw_main_setting_section_callback() function.


// Function to render a text input field
function efmw_display_text_field( $data = array() ) {

	extract( $data );
	$options = efmw_get_options(); 

	echo '<input type="text" name="efmw_options[' . $name . ']" value="' . esc_html( $options[$name] ) . '"/><br />';

} // End of efmw_display_text_field() function.


// Function to render a check box
function efmw_display_check_box( $data = array() ) {

	extract ( $data );
	$options = efmw_get_options(); 
	
	echo '<input type="checkbox" name="efmw_options[' . $name . ']"';

	if ( $options[$name] ) {
		echo ' checked="checked"';
	}

	echo ' />';

} // End of efmw_display_check_box() function.


function efmw_display_text_area( $data = array() ) {

	extract ( $data );
	$options = efmw_get_options(); 
	
	echo '<textarea name="efmw_options[' . $name . ']"'
		. ( isset( $extra_attr ) ? ' ' . $extra_attr : '' ) .
		'>' . esc_html( $options[$name] ) . '</textarea>';

} // End of efmw_display_text_area() function.


function efmw_display_select_list( $data = array() ) {

	extract ( $data );
	$options = efmw_get_options(); 

	echo '<select name="efmw_options[' . $name . ']">';

	foreach( $choices as $item ) {
		echo '<option value="' . $item . '"';

		selected( $options[$name] == $item );

		echo '>' . $item . '</options>';
	}
	echo '</select>';

} // End of efmw_display_select_list() function.

// ************ CREATING SETTINGS GROUP ************ //
// ************************************************* //


// ************************************* //
// ************ ADDING HELP ************ //

function efmw_help_tabs() {
	$screen = get_current_screen();
	$screen->add_help_tab( array(
		'id'       => 'efmw-plugin-help-instructions',
		'title'    => 'Notes',
		'callback' => function() {

			// Help text:
			echo  file_get_contents( plugin_dir_path( __FILE__ ) . 'includes/html/main-help.inc.html' );
		}

	) );

	$screen = get_current_screen();
	$screen->add_help_tab( array(
		'id'       => 'efmw-default-styles',
		'title'    => 'Example CSS Styles',
		'callback' => function() {

			// Help text:
			echo nl2br( file_get_contents( plugin_dir_path( __FILE__ ) . 'includes/css/default-user-styles.css' ), true );

		}

	) );

	// Display text in the sidebar:
	## $screen->set_help_sidebar( '<p>This is the sidebar content</p>' );
	
} // End of efmw_help_tabs() function.


// ************ ADDING HELP ************ //
// ************************************* //

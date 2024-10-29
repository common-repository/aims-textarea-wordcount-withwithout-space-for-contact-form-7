<?php
/*
@copyright  2020 Aims Infosoft

Plugin Name: Aims Textarea Wordcount with/without Space For Contact Form 7
Text Domain: wpcf7awc
Plugin URI: wordpress.org/plugins/aims-textarea-wordcount-withwithout-space-for-contact-form-7/
Description: Adds an option to Contact Form 7's Textarea field for a Max Wordcount, and limits input to max wordcount with space or without space on the front end
Author: Aims Infosoft
Version: 1.0.9
Requires at least: 5.0
Author URI: https://aimsinfosoft.com/
License: GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

/**
 * overwrite Contact Form 7's base module for [textarea] and [textarea*]
 */

if( !defined('ATWC_REQUIRED_CF7_VERSION') )
	define ( 'ATWC_REQUIRED_CF7_VERSION', '4.6.0' );

if ( !defined( 'ATWC_VERSION' ))
	define( 'ATWC_VERSION', '1.0.2' );

if ( !defined( 'ATWC_PLUGIN_URL' ))
    define( 'ATWC_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

if( !defined('ATWC_PLUGIN_NAME') )
	define ( 'ATWC_PLUGIN_NAME', 'Aims Textarea Wordcount with/without Space For Contact Form 7' );

if ( !defined( 'ABSPATH' ) )
	exit; // Exit if accessed directly

if ( !in_array( 'contact-form-7/wp-contact-form-7.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	
	/*$error_message = '<p style="font-family:-apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Oxygen-Sans,Ubuntu,Cantarell,\'Helvetica Neue\',sans-serif;font-size: 13px;line-height: 1.5;color:#444;">' . esc_html__( 'This plugin requires ', 'wpcf7awc' ) . '<a href="' . esc_url( 'https://wordpress.org/plugins/contact-form-7/' ) . '" target="_blank">Contact Form 7</a>' . esc_html__( ' plugin to be active.', 'wpcf7awc' ) . '</p>';
	die( $error_message );*/ 
	/*return; // Exit if contact-form-7 plugin is not active*/
	add_action( 'admin_notices', 'sample_admin_notice__error' );
}

function sample_admin_notice__error() {
    $class = 'notice notice-error';
    $message = __( ATWC_PLUGIN_NAME .' requires ', 'wpcf7awc');
 
    printf( '<div class="%1$s"><p style="font-family:-apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Oxygen-Sans,Ubuntu,Cantarell,\'Helvetica Neue\',sans-serif;font-size: 13px;line-height: 1.5;color:#444;">%2$s</p></div>', esc_attr( $class ), $message ."<a href='". esc_url( 'https://wordpress.org/plugins/contact-form-7/' ) ."' target='_blank'>Contact Form 7</a> " . __(' plugin to be active.', 'wpcf7awc' )); 
}

register_activation_hook( __FILE__, 'atwc_addon_activate');

// this function used for when activate the plugin
if( !function_exists('atwc_addon_activate') ) {
	function atwc_addon_activate() {
		if ( !function_exists( 'is_plugin_active_for_network' ) ) {
		    require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
		}
		
		// require minimum cf7 version 4.6.1
		$awc_cf7_data = get_plugin_data( plugin_dir_path( __DIR__ ) .'/contact-form-7/wp-contact-form-7.php', false, false );

		$cmp = version_compare( $awc_cf7_data['Version'], ATWC_REQUIRED_CF7_VERSION, '<' ) ? 'true' : 'false';
		if('true' == $cmp) {
			// Deactivate the plugin.
			deactivate_plugins( plugin_basename( __FILE__ ) );
			// Throw an error in the WordPress admin console.
			$error_message = '<p style="font-family:-apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Oxygen-Sans,Ubuntu,Cantarell,\'Helvetica Neue\',sans-serif;font-size: 13px;line-height: 1.5;color:#444;">' . esc_html__( 'This plugin require minimum 4.6.1 version of ', 'wpcf7awc' ) . '<a href="' . esc_url( 'https://wordpress.org/plugins/contact-form-7/' ) . '" target="_blank">Contact Form 7</a>' . esc_html__( ' plugin to be active.', 'wpcf7awc' ) . '</p>';
	    	die( $error_message );
		} 
		if ( current_user_can( 'activate_plugins' ) && ! is_plugin_active( 'contact-form-7/wp-contact-form-7.php' ) ) {
		    // Deactivate the plugin.
		    deactivate_plugins( plugin_basename( __FILE__ ) );
		    // Throw an error in the WordPress admin console.
		    $error_message = '<p style="font-family:-apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Oxygen-Sans,Ubuntu,Cantarell,\'Helvetica Neue\',sans-serif;font-size: 13px;line-height: 1.5;color:#444;">' . esc_html__( 'This plugin requires ', 'wpcf7awc' ) . '<a href="' . esc_url( 'https://wordpress.org/plugins/contact-form-7/' ) . '" target="_blank">Contact Form 7</a>' . esc_html__( ' plugin to be active.', 'wpcf7awc' ) . '</p>';
	    	die( $error_message ); 
		}
	}	
}

if(!function_exists( 'atwc_textarea_tag_handler' )) {
	// tag handler, overwriting the default cf7 version
	function atwc_textarea_tag_handler( $tag ) {
		
		if ( empty( $tag->name ) ) {
			return '';
		}

		$validation_error = wpcf7_get_validation_error( $tag->name );

		$class = wpcf7_form_controls_class( $tag->type );

		if ( $validation_error )
			$class .= ' wpcf7-not-valid';

		$atts = array();

		$atts['cols']      = $tag->get_cols_option( '40' );
		$atts['rows']      = $tag->get_rows_option( '10' );
		$atts['maxlength'] = $tag->get_maxlength_option();
		$atts['minlength'] = $tag->get_minlength_option();

		if ( $atts['maxlength'] && $atts['minlength'] && $atts['maxlength'] < $atts['minlength'] ) {
			unset( $atts['maxlength'], $atts['minlength'] );
		}

		$atts['class']    = $tag->get_class_option( $class );
		$atts['id']       = $tag->get_id_option();
		$atts['tabindex'] = $tag->get_option( 'tabindex', 'int', true );
	  
	  	// add our maxawc
	 	$atts['data-maxawc'] = $tag->get_option( 'maxawc', 'int', true );
		$atts['values'] = $tag->get_option( 'string' ,'int', true);

		if ( $tag->has_option( 'readonly' ) ) {
			$atts['readonly'] = 'readonly';
		}

		if ( $tag->is_required() ) {
			$atts['aria-required'] = 'true';
		}

		$atts['aria-invalid'] = $validation_error ? 'true' : 'false';

		$value = empty( $tag->content )
			? (string) reset( $tag->values )
			: $tag->content;

		if ( $tag->has_option( 'placeholder' ) || $tag->has_option( 'watermark' ) ) {
			$atts['placeholder'] = $value;
			$value = '';
		}

		// if checked without space then value is true
	  	if ( $tag->has_option( 'without-space' ) ) {
			$atts['without-space'] = 'true';
		} else {
			$atts['without-space'] = 'false';
		}

		$value = $tag->get_default_option( $value );

		$value = wpcf7_get_hangover( $tag->name, $value );

		$atts['name'] = $tag->name;	  
	  	
		if('true' == $atts['without-space']) {
			$withoutspace = "Without Space";
		} else {
			$withoutspace = "With Space";
		}
		$message = $tag->values[0];

		if($message[0] != ''){
			$message_text = $tag->values[0];
			$replacement = $atts['data-maxawc'];
			$main_ws = ["{withoutspace}", "{maxword}"];
			$replace_ws = [$withoutspace , $replacement];			
			$aimswordval = str_replace($main_ws, $replace_ws, $message_text);	
		}

	  	// inject our word counter
		if( $atts['data-maxawc'] ) {
			if($aimswordval != ''){
				$validation_error .= '<span class="wpcf7awc-msg word-count-space" style="display:flex;padding-top: 5px;vertical-align: middle;justify-content: unset;"><br/><input type="text" name="wcount_'. $atts['name'] .'" id="wcount_'. $atts['name'] .'" size="3" maxlength="'. ( $atts['data-maxawc'] % 10 ) .'" style="text-align:center; width: 20px;padding:0px;border:none;font-size:15px;margin-bottom: 2px;" value="" readonly="readonly" />'.$aimswordval.' </span>';
			}else{
				$validation_error .= '<span class="wpcf7awc-msg word-count-space" style="display:flex;padding-top: 5px;vertical-align: middle;justify-content: unset;"><br/><input type="text" name="wcount_'. $atts['name'] .'" id="wcount_'. $atts['name'] .'" size="3" maxlength="'. ( $atts['data-maxawc'] % 10 ) .'" style="text-align:center; width: 20px;padding:0px;border:none;font-size:15px;margin-bottom: 2px;" value="" readonly="readonly" /> words. Please limit to '. $atts['data-maxawc'] .' words or less '.$withoutspace.' </span>';
			}
	  	}	  
		$atts = wpcf7_format_atts( $atts ); 
		$html = sprintf(
			'<span class="wpcf7-form-control-wrap %1$s"><textarea %2$s></textarea>%3$s</span>',
			sanitize_html_class( $tag->name ), 
			$atts,
			$validation_error );
	  
		return $html;
	}
} 

/**  check if it doesn't already exist Validation filter re adjusting hooks */

if(!function_exists( 'atwc_undo_hooks' )) {
	function atwc_undo_hooks( $length ) {
		remove_action( 'wpcf7_admin_init', 'wpcf7_add_tag_generator_textarea', 20 );
	}
}
add_action( 'wpcf7_admin_init', 'atwc_undo_hooks', 1 );

//check if it doesn't already exist
if(!function_exists( 'atwc_add_tag_textarea' )) {
	function atwc_add_tag_textarea() {
		if ( function_exists('wpcf7_remove_form_tag') ) {
			wpcf7_remove_form_tag( 'textarea' );
			wpcf7_remove_form_tag( 'textarea*' );
		}
		wpcf7_add_form_tag( array( 'textarea', 'textarea*' ),
			'atwc_textarea_tag_handler', true );
	}	
}

// add our remove+add, but with priority After the "textarea" one
add_action( 'wpcf7_init', 'atwc_add_tag_textarea', 20 );

//check if it doesn't already exist
if(!function_exists( 'atwc_enqueue_scripts' )) {
	function atwc_enqueue_scripts() {
		//$plugin_dir = trailingslashit(plugins_url(basename(dirname(__FILE__)))); 
		wp_enqueue_script( 'wpcf7awc', ATWC_PLUGIN_URL .'js/wpcf7awc.js',
			array( 'jquery', 'contact-form-7' ), '1.1', true );
	}	
}
add_action( 'wpcf7_enqueue_scripts', 'atwc_enqueue_scripts' );

// Tag generator
add_action( 'wpcf7_admin_init', 'atwc_add_tag_generator_textarea', 20 );

if(!function_exists( 'atwc_add_tag_generator_textarea' )) {
	function atwc_add_tag_generator_textarea() {
		if ( class_exists( 'WPCF7_TagGenerator' ) ) {
			$tag_generator = WPCF7_TagGenerator::get_instance();
			$tag_generator->add( 'textarea', __( 'text area', 'contact-form-7' ),
			  'atwc_tag_generator_textarea' );
		}
	}	
}

if(!function_exists( 'atwc_tag_generator_textarea' )) {
	function atwc_tag_generator_textarea( $contact_form, $args = '' ) {
		$args = wp_parse_args( $args, array() );
		$type = 'textarea';

		$description = __( "Generate a form-tag for a multi-line text input field with optional Max Wordcount.", 'wpcf7wc' ) ." ". __("For more details on Text Fields in Contact Form 7, see %s.", 'wpcf7wc' );

		$desc_link = wpcf7_link( __( 'http://contactform7.com/text-fields/', 'contact-form-7' ), __( 'Text Fields', 'contact-form-7' ) );
	?>
	<div class="control-box">
	<fieldset>
	<legend><?php echo sprintf( esc_html( $description ), $desc_link ); ?></legend>

	<table class="form-table">
	<tbody>
		<tr>
		<th scope="row"><?php echo esc_html( __( 'Field type', 'contact-form-7' ) ); ?></th>
		<td>
			<fieldset>
			<legend class="screen-reader-text"><?php echo esc_html( __( 'Field type', 'contact-form-7' ) ); ?></legend>
			<label><input type="checkbox" name="required" /> <?php echo esc_html( __( 'Required field', 'contact-form-7' ) ); ?></label>
			</fieldset>
		</td>
		</tr>

		<tr>
		<th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-name' ); ?>"><?php echo esc_html( __( 'Name', 'contact-form-7' ) ); ?></label></th>
		<td><input type="text" name="name" class="tg-name oneline" id="<?php echo esc_attr( $args['content'] . '-name' ); ?>" /></td>
		</tr>

		<tr>
		<th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-id' ); ?>"><?php echo esc_html( __( 'Id attribute', 'contact-form-7' ) ); ?></label></th>
		<td><input type="text" name="id" class="idvalue oneline option" id="<?php echo esc_attr( $args['content'] . '-id' ); ?>" /></td>
		</tr>

		<tr>
		<th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-class' ); ?>"><?php echo esc_html( __( 'Class attribute', 'contact-form-7' ) ); ?></label></th>
		<td><input type="text" name="class" class="classvalue oneline option" id="<?php echo esc_attr( $args['content'] . '-class' ); ?>" /></td>
		</tr>

	    <tr>
	    <th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-maxawc' ); ?>"><?php echo esc_html( __( 'Max Wordcount', 'wpcf7awc' ) ); ?></label></th>
	    <td><input type="text" name="maxawc" class="numeric oneline option" id="<?php echo esc_attr( $args['content'] . '-maxawc' ); ?>" /><label><?php echo esc_html( __( 'By Default Wordcount with space', 'wpcf7awc' ) ); ?>
	    </label><br/>
	  	<label><input type="checkbox" name="without-space" class="option" /> <?php echo esc_html( __( 'Use this for Wordcount without space', 'wpcf7awc' ) ); ?></label>	
	    </td>
	    </tr>

		<tr>
		<th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-message' ); ?>"><?php echo esc_html( __( 'Message', 'contact-form-7' ) ); ?></label></th>
		<td><textarea name="values" class="values message oneline option" id="<?php echo esc_attr( $args['content'] . '-values' ); ?>">words. Please limit to {maxword} words or less {withoutspace}.</textarea>	
		<label><?php echo '</br>'.esc_html( __( 'Use {maxword} placeholder as the wordcount number and {withoutspace} placeholder for the without/with space text.', 'contact-form-7' ) ); ?></label></td>	
		</tr>	

	</tbody>
	</table>
	</fieldset>
	</div>

	<div class="insert-box">
		<input type="text" name="<?php echo $type; ?>" class="tag code" readonly="readonly" onfocus="this.select()" />

		<div class="submitbox">
		<input type="button" class="button button-primary insert-tag" value="<?php echo esc_attr( __( 'Insert Tag', 'contact-form-7' ) ); ?>" />
		</div>

		<br class="clear" />

		<p class="description mail-tag"><label for="<?php echo esc_attr( $args['content'] . '-mailtag' ); ?>"><?php echo sprintf( esc_html( __( "To use the value input through this field in a mail field, you need to insert the corresponding mail-tag (%s) into the field on the Mail tab.", 'contact-form-7' ) ), '<strong><span class="mail-tag"></span></strong>' ); ?><input type="text" class="mail-tag code hidden" readonly="readonly" id="<?php echo esc_attr( $args['content'] . '-mailtag' ); ?>" /></label></p>
	</div>
	<?php
	}
}
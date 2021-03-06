<?php if ( ! defined('EVENT_ESPRESSO_VERSION')) exit('No direct script access allowed');
/**
 * Event Espresso
 *
 * Event Registration and Management Plugin for WordPress
 *
 * @ package			Event Espresso
 * @ author			Seth Shoultes
 * @ copyright		(c) 2008-2011 Event Espresso  All Rights Reserved.
 * @ license			http://eventespresso.com/support/terms-conditions/   * see Plugin Licensing *
 * @ link					http://www.eventespresso.com
 * @ version		 	4.0
 *
 * ------------------------------------------------------------------------
 *
 * EES_Espresso_Checkout
 *
 * @package			Event Espresso
 * @subpackage	/shortcodes/
 * @author				Brent Christensen 
 *
 * ------------------------------------------------------------------------
 */
class EES_Espresso_Checkout  extends EES_Shortcode {

	/**
	 * 	set_hooks - for hooking into EE Core, modules, etc
	 *
	 *  @access 	public
	 *  @return 	void
	 */
	public static function set_hooks() {
	}

	/**
	 * 	set_hooks_admin - for hooking into EE Admin Core, modules, etc
	 *
	 *  @access 	public
	 *  @return 	void
	 */
	public static function set_hooks_admin() {
	}

	/**
	 * 	run - initial shortcode module setup called during "wp_loaded" hook
	 * 	this method is primarily used for loading resources that will be required by the shortcode when it is actually processed
	 *
	 *  @access 	public
	 *  @return 	void
	 */
	public function run( WP $WP ) {
		// SPCO is large and resource intensive, so it's better to do a double check before loading it up, so let's grab the post_content for the requested post
		global $wpdb;
		$SQL = 'SELECT post_content from ' . $wpdb->posts . ' WHERE post_type="page" AND post_status="publish" AND post_name=%s';
		if( $post_content = $wpdb->get_var( $wpdb->prepare( $SQL, EE_Registry::instance()->REQ->get( 'post_name' )))) {
			// generate shortcode to search for
			$EES_Shortcode = '[' . str_replace( 'EES_', '', strtoupper( get_class( $this )));
			// now check for this shortcode
			if ( strpos( $post_content, $EES_Shortcode ) !== FALSE ) {	
				EE_Registry::instance()->REQ->set( 'ee', '_register' );
			}					
		}
	}

	/**
	 * 	process_shortcode - ESPRESSO_CHECKOUT 
	 * 
	 *  @access 	public
	 *  @param		array 	$attributes
	 *  @return 	void
	 */
	public function process_shortcode( $attributes ) {
		return EE_Registry::instance()->REQ->get_output();		
	}

}
// End of file EES_Espresso_Checkout.shortcode.php
// Location: /shortcodes/EES_Espresso_Checkout.shortcode.php
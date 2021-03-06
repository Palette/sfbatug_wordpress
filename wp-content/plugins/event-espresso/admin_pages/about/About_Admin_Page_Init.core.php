<?php if ( ! defined('EVENT_ESPRESSO_VERSION')) exit('No direct script access allowed');
/**
 * Event Espresso
 *
 * Event Registration and Management Plugin for WordPress
 *
 * @ package			Event Espresso
 * @ author				Seth Shoultes
 * @ copyright		(c) 2008-2011 Event Espresso  All Rights Reserved.
 * @ license				{@link http://eventespresso.com/support/terms-conditions/}   * see Plugin Licensing *
 * @ link					{@link http://www.eventespresso.com}
 * @ since		 		4.0
 *
 * ------------------------------------------------------------------------   
 */

/**
 * EE_About_Admin_Page_Init
 * 
 * This is the admin page route to learn more about EE (and the first page users are taken to after new activation).
 *
 * @package			Event Espresso
 * @abstract
 * @subpackage		includes/admin_pages/about/EE_About_Admin_Page_Init.core.php
 * @author			Darren Ethier 
 *
 * ------------------------------------------------------------------------
 */
class About_Admin_Page_Init extends EE_Admin_Page_Init {

	public function __construct() {
		//define some events related constants
		define( 'EE_ABOUT_PG_SLUG', 'espresso_about' );	
		define( 'EE_ABOUT_LABEL', __('About', 'event_espresso'));	
		define( 'EE_ABOUT_ADMIN', EE_ADMIN_PAGES . 'about' . DS );	
		define( 'EE_ABOUT_ADMIN_URL', admin_url( 'admin.php?page=' . EE_ABOUT_PG_SLUG ));	
		define( 'EE_ABOUT_TEMPLATE_PATH', EE_ABOUT_ADMIN . 'templates' . DS );	
		define( 'EE_ABOUT_ASSETS_URL', EE_ADMIN_PAGES_URL . 'about/assets/' );	
		parent::__construct();
	}

	protected function _set_init_properties() {
		$this->label = __('About Event Espresso', 'event_espresso');
		$this->menu_label = EE_ABOUT_LABEL;
		$this->menu_slug = 'espresso_about';
	}

	public function get_menu_map() {
		$map = array(
			'group' => 'extras',
			'menu_order' => 40,
			'show_on_menu' => TRUE,
			'parent_slug' => 'espresso_about'
			);
		return $map;
	}

} //end class Events_Admin_Page_Init
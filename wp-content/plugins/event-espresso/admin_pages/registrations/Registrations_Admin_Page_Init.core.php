<?php if ( ! defined('EVENT_ESPRESSO_VERSION')) exit('No direct script access allowed');
/**
 * Event Espresso
 *
 * Event Registration and Management Plugin for WordPress
 *
 * @ package			Event Espresso
 * @ author				Seth Shoultes
 * @ copyright		(c) 2008-2011 Event Espresso  All Rights Reserved.
 * @ license			{@link http://eventespresso.com/support/terms-conditions/}   * see Plugin Licensing *
 * @ link					{@link http://www.eventespresso.com}
 * @ since		 		4.0
 *
 * ------------------------------------------------------------------------
 *
 * Registrations_Admin_Page_Init class
 *
 * @package			Event Espresso
 * @subpackage	includes/core/admin/registrations/Registrations_Admin_Page_Init.core.php
 * @author				Brent Christensen 
 *
 * ------------------------------------------------------------------------
 */
class Registrations_Admin_Page_Init extends EE_Admin_Page_CPT_Init  {





	/**
	 * 		constructor
	 * 		@Constructor
	 * 		@access public
	 * 		@return void
	 */
	public function __construct() { 
		do_action( 'AHEE_log', __FILE__, __FUNCTION__, '' );
		
		define( 'REG_PG_SLUG', 'espresso_registrations' );	
		define( 'REG_PG_NAME', ucwords( str_replace( '_', '', REG_PG_SLUG )));	
		define( 'REG_ADMIN', EE_ADMIN_PAGES . 'registrations' . DS );	
		define( 'REG_ADMIN_URL', admin_url( 'admin.php?page=' . REG_PG_SLUG ));	
		define( 'REG_ASSETS_PATH', REG_ADMIN . 'assets' . DS );		
		define( 'REG_ASSETS_URL', EE_ADMIN_PAGES_URL . 'registrations/assets/' );	
		define( 'REG_TEMPLATE_PATH', REG_ADMIN . 'templates' . DS );	
		define( 'REG_TEMPLATE_URL', EE_ADMIN_PAGES_URL . 'registrations/templates/' );
		
		parent::__construct();
	}




	protected function _set_init_properties() {
		$this->label = __('Registrations Overview', 'event_espresso');
		$this->menu_label = __('Registrations', 'event_espresso');
		$this->menu_slug = REG_PG_SLUG;
		$this->capability = 'administrator';
	}






	public function get_menu_map() {
		$map = array(
			'group' => 'main',
			'menu_order' => 40,
			'show_on_menu' => TRUE,
			'parent_slug' => 'espresso_events'
			);
		return $map;
	}
	


}


	
// end of file:	includes/core/admin/registrations/Registrations_Admin_Page_Init.core.php
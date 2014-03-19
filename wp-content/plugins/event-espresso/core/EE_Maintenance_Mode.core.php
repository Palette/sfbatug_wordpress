<?php if ( ! defined('EVENT_ESPRESSO_VERSION')) { exit('No direct script access allowed'); }
/**
 * 
 * Event Espresso
 *
 * Event Registration and Ticketing Management Plugin for WordPress
 *
 * @ package			Event Espresso
 * @ author			Seth Shoultes
 * @ copyright		(c) 2008-2011 Event Espresso  All Rights Reserved.
 * @ license			http://eventespresso.com/support/terms-conditions/   * see Plugin Licensing *
 * @ link					http://www.eventespresso.com
 * @ version		 	$VID:$
 *
 * ------------------------------------------------------------------------
 *
 * EE_Maintenance_Mode Class
 *
 * Super Duper Class Description
 *
 * @package			Event Espresso
 * @subpackage		core
 * @author				Michael Nelson
 *
 * ------------------------------------------------------------------------
 */
class EE_Maintenance_Mode {

	/**
	 * constants available to client code for interpreting the values of EE_Maintenance_Mode::level().
	 * level_0_not_in_maintenance means the site is NOT in maintenance mode (so everything's normal)
	 */
	const level_0_not_in_maintenance = 0;
	/**
	 * level_1_frontend_only_maintenance means that the site's frontend EE code should be completely disabled
	 * but the admin backend should be running as normal. Maybe an admin can view the frontend though
	 */
	const level_1_frontend_only_maintenance = 1;
	/**
	 * level_2_complete_maintenance means the frontend AND EE backend code are disabled. The only system running
	 * is the maintenance mode stuff, which will require users to update all addons, and then finish running all
	 * migration scripts before taking the site out of maintenance mode
	 */
	const level_2_complete_maintenance = 2;
	
	/**
	 * the nameof the option which stores the current level of maintenance mode
	 */
	const option_name_maintenance_mode = 'ee_maintenance_mode';
   /**
     * 	EE_Maintenance_Mode Object
     * 	@var EE_Maintenance_Mode $_instance
	 * 	@access 	private 	
     */
	private static $_instance = NULL;

	/**
	 * 	EE_Registry Object
	 *	@var 	EE_Registry	$EE	
	 * 	@access 	protected
	 */
	protected $EE = NULL;






	/**
	 *@singleton method used to instantiate class object
	 *@access public
	 *@return EE_Maintenance_Mode instance
	 */	
	public static function instance() {
		// check if class object is instantiated
		if ( self::$_instance === NULL  or ! is_object( self::$_instance ) or ! ( self::$_instance instanceof EE_Maintenance_Mode )) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}	



	/**
	 *private constructor to prevent direct creation
	 *@Constructor
	 *@access private
	 *@return void
	 */	
	private function __construct() {
	}




	/**
	 * Determines whether or not we're in maintenance mode and what level. 
	 * EE_Maintenance_Mode::level_0_not_in_maintenance => not in maintenance mode (in normal mode)
	 * EE_Maintenance_Mode::level_1_frontend_only_maintenance=> frontend-only mainteannce mode
	 * EE_Maintenance_Mode::level_2_complete_maintenance => frontend and backend mainteancne mode
	 * @return int
	 */
	public function level(){
		$real_maintenance_mode_level = get_option(self::option_name_maintenance_mode,0);
		//if this is an admin request, we'll be honest... except if it's ajax, because that might be from the frontend
		if( ( ! is_admin() || (defined('DOING_AJAX') && DOING_AJAX)) && //only on frontend or ajax requests
			current_user_can('administrator') && //when the user is an admin
			$real_maintenance_mode_level == EE_Maintenance_Mode::level_1_frontend_only_maintenance){//and we're in level 1
			$maintenance_mode_level = EE_Maintenance_Mode::level_0_not_in_maintenance;
		}else{
			$maintenance_mode_level = $real_maintenance_mode_level;
		}
		return $maintenance_mode_level;
	}
	
	/**
	 * Determines if we need to put EE in maintenance mode because teh database needs updating
	 * @return boolean true if DB is old and maintenance mode was triggered; false otherwise
	 */
	public function set_maintenance_mode_if_db_old(){
		if( EE_Data_Migration_Manager::instance()->check_for_applicable_data_migration_scripts()){
			update_option(self::option_name_maintenance_mode, self::level_2_complete_maintenance);
			return true;
		}else{
			return false;
		}
	}
	
	/**
	 * Updates the maintenance level on the site
	 * @param int $level
	 * @return void
	 */
	public function set_maintenance_level($level){
		update_option(self::option_name_maintenance_mode, intval($level));
	}




	/**
	 * 	template_include
	 * 
	 * 	replacement EE CPT template that displays message notifying site visitors that EE has been temporarily placed into maintenace mode
	 *
	 *  @access 	public
	 *  @return 	string
	 */
	function template_include() {
		if ( file_exists( EVENT_ESPRESSO_TEMPLATE_DIR . 'maintenance_mode.template.php' )) {
			return EVENT_ESPRESSO_TEMPLATE_DIR . 'maintenance_mode.template.php';
		} else if ( file_exists( EE_PLUGIN_DIR_PATH . 'templates/maintenance_mode.template.php' )) {
			return EE_PLUGIN_DIR_PATH . 'templates/maintenance_mode.template.php';
		}
	}



	/**
	 * 	the_content
	 * 
	 * 	displays message notifying site visitors that EE has been temporarily placed into maintenace mode when post_type != EE CPT
	 *
	 *  @access 	public
	 *  @return 	void
	 */
	public static function the_content( $the_content ) {
		// check for EE shortcode
		if ( strpos( $the_content, '[ESPRESSO_' )) {
			// this can eventually be moved to a template, or edited via admin. But for now...
			$the_content = __( ' 
			<h2>Maintenance Mode</h2>
			<p>Event Registration has been temporarily closed while system maintenance is being performed. We\'re sorry for any inconveniences this may have caused. Please try back again later.</p>
			', 'event_espresso' );
		}
		return $the_content;
	}








	/**
	 *		@ override magic methods
	 *		@ return void
	 */
	final function __destruct() {}
	final function __call($a,$b) {}
	final function __get($a) {}
	final function __set($a,$b) {}
	final function __isset($a) {}
	final function __unset($a) {}
	final function __sleep() {
		return array();
	}
	final function __wakeup() {}
	final function __toString() {}
	final function __invoke() {}
	final function __set_state() {}
	final function __clone() {}
	final static function __callStatic($a,$b) {}
 
}
// End of file EE_Maintenance_Mode.core.php
// Location: ./core/EE_Maintenance_Mode.core.php
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
 * Event List
 *
 * @package		Event Espresso
 * @subpackage	/modules/events_archive/
 * @author		Brent Christensen 
 *
 * ------------------------------------------------------------------------
 */
class EED_Events_Archive  extends EED_Module {


	/**
	 * 	Start Date
	 *	@var 	$_elf_month
	 * 	@access 	protected
	 */
	protected $_elf_month = NULL;


	/**
	 * 	Category
	 *	@var 	$_elf_category
	 * 	@access 	protected
	 */
	protected $_elf_category = NULL;


	/**
	 * 	whether to display expired events in the event list
	 *	@var 	$_show_expired
	 * 	@access 	protected
	 */
	protected $_show_expired = NULL;


	/**
	 * 	whether to display the event list as a grid or list
	 *	@var 	$_type
	 * 	@access 	protected
	 */
	protected static $_type = NULL;


	/**
	 * 	array of existing event list views
	 *	@var 	$_types
	 * 	@access 	protected
	 */
	protected static $_types = array( 'grid', 'text', 'dates' );


	public static $espresso_event_list_ID = 0;
	public static $espresso_grid_event_lists = array();

	

	/**
	 * 	set_hooks - for hooking into EE Core, other modules, etc
	 *
	 *  @access 	public
	 *  @return 	void
	 */
	public static function set_hooks() {
		EE_Config::register_route( __( 'events', 'event_espresso' ), 'Events_Archive', 'run' );
		EE_Config::register_route( 'event_list', 'Events_Archive', 'event_list' );		
		add_action( 'wp_loaded', array( 'EED_Events_Archive', 'set_definitions' ), 2 );
	}

	/**
	 * 	set_hooks_admin - for hooking into EE Admin Core, other modules, etc
	 *
	 *  @access 	public
	 *  @return 	void
	 */
	public static function set_hooks_admin() {
		add_filter('FHEE__Config__update_config__CFG', array( 'EED_Events_Archive', 'filter_config' ), 10 );
		add_filter( 'FHEE__EED_Events_Archive__template_settings_form__event_list_config', array( 'EED_Events_Archive', 'set_default_settings' ));
		add_action( 'AHEE__template_settings__template__before_settings_form', array( 'EED_Events_Archive', 'template_settings_form' ), 10 );
		add_action( 'wp_loaded', array( 'EED_Events_Archive', 'set_definitions' ), 2 );
		add_filter( 'FHEE__General_Settings_Admin_Page__update_template_settings__data', array( 'EED_Events_Archive', 'update_template_settings' ), 10, 2 );
	}




	/**
	 * 	set_definitions
	 *
	 *  @access 	public
	 *  @return 	void
	 */
	public static function set_definitions() {
		define( 'EVENTS_ARCHIVE_ASSETS_URL', plugin_dir_url( __FILE__ ) . 'assets' . DS );
		define( 'EVENTS_ARCHIVE_TEMPLATES_PATH', str_replace( '\\', DS, plugin_dir_path( __FILE__ )) . 'templates' . DS );
	}



	/**
	 * 	run - initial module setup
	 *
	 *  @access 	public
	 *  @return 	void
	 */
	public function run( $WP ) {
		do_action( 'AHEE__EED_Events_Archive__before_run' );
		// ensure valid EE_Events_Archive_Config() object exists
		EED_Events_Archive::set_config();
		// load other required components
		$this->_load_assests();
		// filter the WP posts_join, posts_where, and posts_orderby SQL clauses
		$this->_filter_query_parts();		
		// check what template is loaded
		add_filter( 'template_include',  array( 'EED_Events_Archive', 'template_include' ), 999, 1 );
		add_filter( 'FHEE__EED_Ticket_Selector__load_tckt_slctr_assets', '__return_true' );
	}



	/**
	 * 	event_list
	 *
	 *  @access 	public
	 *  @return 	void
	 */
	public function event_list() {	
		// ensure valid EE_Events_Archive_Config() object exists
		EED_Events_Archive::set_config();
		// load other required components
		$this->_load_assests();
	}







	/**
	 * 	set_config
	 *
	 *  @access 	private
	 *  @return 	void
	 */
	public static function set_config() { 
		// set config
		if ( ! isset( EE_Registry::instance()->CFG->template_settings->EED_Events_Archive ) || ! EE_Registry::instance()->CFG->template_settings->EED_Events_Archive instanceof EE_Events_Archive_Config ) {
			EE_Registry::instance()->CFG->template_settings->EED_Events_Archive = new EE_Events_Archive_Config();
		}
	}







	/**
	 * 	_filter_query_parts
	 *
	 *  @access 	private
	 *  @return 	void
	 */
	private function _filter_query_parts() {
		// build event list query
		add_filter( 'posts_join', array( $this, 'posts_join' ), 1, 2 );
		add_filter( 'posts_where', array( $this, 'posts_where' ), 1, 2 );
		add_filter( 'posts_orderby', array( $this, 'posts_orderby' ), 1, 2 );
	}

	/**
	 * 	_type - the type of event list : grid, text, dates
	 *
	 *  @access 	public
	 *  @return 	string
	 */
	public static function set_type() {
		do_action( 'AHEE__EED_Events_Archive__before_set_type' );
		EED_Events_Archive::$_types = apply_filters( 'EED_Events_Archive__set_type__types', EED_Events_Archive::$_types );
		$view = isset( EE_Registry::instance()->CFG->EED_Events_Archive['default_type'] ) ? EE_Registry::instance()->CFG->EED_Events_Archive['default_type'] : 'grid';
		$elf_type = EE_Registry::instance()->REQ->is_set( 'elf_type' ) ? sanitize_text_field( EE_Registry::instance()->REQ->get( 'elf_type' )) : '';
		$view = ! empty( $elf_type ) ? $elf_type : $view;
		$view = apply_filters( 'EED_Events_Archive__set_type__type', $view );
		if ( ! empty( $view ) && in_array( $view, EED_Events_Archive::$_types )) {
			self::$_type = $view;
		} 
	}

	/**
	 * 	_show_expired
	 *
	 *  @access 	private
	 *  @param	boolean	$req_only if TRUE, then ignore defaults and only return $_POST value
	 *  @return 	boolean
	 */
	private static function _show_expired( $req_only = FALSE ) {	
		// get default value for "display_expired_events" as set in the EE General Settings > Templates > Event Listings 
		$show_expired = ! $req_only && isset( EE_Registry::instance()->CFG->EED_Events_Archive['display_expired_events'] ) ? EE_Registry::instance()->CFG->EED_Events_Archive['display_expired_events'] : FALSE;
		// override default expired option if set via filter
		$show_expired = EE_Registry::instance()->REQ->is_set( 'elf_expired_chk' ) ? absint( EE_Registry::instance()->REQ->get( 'elf_expired_chk' )) : $show_expired;
		return $show_expired ? TRUE : FALSE;
	}

	/**
	 * 	_event_category_slug
	 *
	 *  @access 	private
	 *  @return 	string
	 */
	private static function _event_category_slug() {			
		return EE_Registry::instance()->REQ->is_set( 'elf_category_dd' ) ? sanitize_text_field( EE_Registry::instance()->REQ->get( 'elf_category_dd' )) : '';
	}

	/**
	 * 	_display_month - what month should the event list display events for?
	 *
	 *  @access 	private
	 *  @return 	string
	 */
	private static function _display_month() {			
		return EE_Registry::instance()->REQ->is_set( 'elf_month_dd' ) ? sanitize_text_field( EE_Registry::instance()->REQ->get( 'elf_month_dd' )) : '';
	}



	/**
	 * 	get_post_data
	 *
	 *  @access 	public
	 *  @return 	void
	 */
	public function get_post_data() {
		$this->_elf_month = EED_Events_Archive::_display_month();
		$this->_elf_category = EED_Events_Archive::_event_category_slug();
		$this->_show_expired = EED_Events_Archive::_show_expired( TRUE );
	}



	/**
	 * 	posts_join
	 *
	 *  @access 	public
	 *  @return 	void
	 */
	public function posts_join( $SQL, WP_Query $wp_query ) {
		if ( isset( $wp_query->query ) && isset( $wp_query->query['post_type'] ) && $wp_query->query['post_type'] == 'espresso_events' ) {
			// Category
			$SQL .= EED_Events_Archive::posts_join_sql_for_terms( EED_Events_Archive::_event_category_slug() );
		}
		return $SQL;
	}


	/**
	 * 	posts_join_sql_for_terms
	 *
	 *  @access 	public
	 *  @param	mixed boolean|string	$join_terms pass TRUE or term string, doesn't really matter since this value doesn't really get used for anything yet
	 *  @return 	string
	 */
	public static function posts_join_sql_for_terms( $join_terms = NULL ) {
		$SQL= '';
		if ( ! empty( $join_terms )) {
			global $wpdb;
			$SQL .= " LEFT JOIN $wpdb->term_relationships ON ($wpdb->posts.ID = $wpdb->term_relationships.object_id)";
			$SQL .= " LEFT JOIN $wpdb->term_taxonomy ON ($wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id)";
			$SQL .= " LEFT JOIN $wpdb->terms ON ($wpdb->terms.term_id = $wpdb->term_taxonomy.term_id) ";
		}
		return  $SQL;
	}


	/**
	 * 	posts_join_for_orderby
	 * 	usage:  $SQL .= EED_Events_Archive::posts_join_for_orderby( $orderby_params );
	 *
	 *  @access 	public
	 *  @param	array	$orderby_params 
	 *  @return 	string
	 */
	public static function posts_join_for_orderby( $orderby_params = array() ) {
		$SQL= '';
		foreach( (array)$orderby_params as $orderby ) {
			switch ( $orderby ) {
					
				case 'ticket_start' :
				case 'ticket_end' :
					$SQL .= ' LEFT JOIN ' . EEM_Datetime_Ticket::instance()->table() . ' ON (' . EEM_Datetime::instance()->table() . '.DTT_ID = ' . EEM_Datetime_Ticket::instance()->table() . '.DTT_ID )';
					$SQL .= ' LEFT JOIN ' . EEM_Ticket::instance()->table() . ' ON (' . EEM_Datetime_Ticket::instance()->table() . '.TKT_ID = ' . EEM_Ticket::instance()->table() . '.TKT_ID )';
					break;
				
				case 'venue_title' :
				case 'city' :
					$SQL .= ' LEFT JOIN ' . EEM_Event_Venue::instance()->table() . ' ON (' . $wpdb->posts . '.ID = ' . EEM_Event_Venue::instance()->table() . '.EVT_ID )';
					$SQL .= ' LEFT JOIN ' . EEM_Venue::instance()->table() . ' ON (' . EEM_Event_Venue::instance()->table() . '.VNU_ID = ' . EEM_Venue::instance()->table() . '.VNU_ID )';
					break;
				
				case 'state' :
					$SQL .= ' LEFT JOIN ' . EEM_Event_Venue::instance()->table() . ' ON (' . $wpdb->posts . '.ID = ' . EEM_Event_Venue::instance()->table() . '.EVT_ID )';
					$SQL .= ' LEFT JOIN ' . EEM_Event_Venue::instance()->second_table() . ' ON (' . EEM_Event_Venue::instance()->table() . '.VNU_ID = ' . EEM_Event_Venue::instance()->second_table() . '.VNU_ID )';
					break;
				
				break;
				
			}
		}

		return  $SQL;
	}


	/**
	 * 	posts_where
	 *
	 *  @access 	public
	 *  @return 	void
	 */
	public function posts_where( $SQL, WP_Query $wp_query ) {
		if ( isset( $wp_query->query_vars ) && isset( $wp_query->query_vars['post_type'] ) && $wp_query->query_vars['post_type'] == 'espresso_events'  ) {			
			// Show Expired ?
			$SQL .= EED_Events_Archive::posts_where_sql_for_show_expired( EED_Events_Archive::_show_expired() );
			// Category
			$SQL .=  EED_Events_Archive::posts_where_sql_for_event_category_slug( EED_Events_Archive::_event_category_slug() );
			// Start Date
			$SQL .= EED_Events_Archive::posts_where_sql_for_event_list_month( EED_Events_Archive::_display_month() );
		}
		return $SQL;
	}


	/**
	 * 	posts_where_sql_for_show_expired
	 *
	 *  @access 	public
	 *  @param	boolean	$show_expired if TRUE, then displayed past events
	 *  @return 	string
	 */
	public static function posts_where_sql_for_show_expired( $show_expired = FALSE ) {
		return  ! $show_expired ? ' AND ' . EEM_Datetime::instance()->table() . '.DTT_EVT_end > "' . date('Y-m-d H:s:i') . '" ' : '';
	}


	/**
	 * 	posts_where_sql_for_event_category_slug
	 *
	 *  @access 	public
	 *  @param	boolean	$event_category_slug
	 *  @return 	string
	 */
	public static function posts_where_sql_for_event_category_slug( $event_category_slug = NULL ) {
		global $wpdb;
		return  ! empty( $event_category_slug ) ? ' AND ' . $wpdb->terms . '.slug = "' . $event_category_slug . '" ' : '';
	}

	/**
	 * 	posts_where_sql_for_event_list_month
	 *
	 *  @access 	public
	 *  @param	boolean	$month
	 *  @return 	string
	 */
	public static function posts_where_sql_for_event_list_month( $month = NULL ) {
		$SQL= '';
		if ( ! empty( $month )) {
			// event start date is LESS than the end of the month ( so nothing that doesn't start until next month )
			$SQL = ' AND ' . EEM_Datetime::instance()->table() . '.DTT_EVT_start <= "' . date('Y-m-t 23:59:59', strtotime( $month )) . '"';
			// event end date is GREATER than the start of the month ( so nothing that ended before this month )
			$SQL .= ' AND ' . EEM_Datetime::instance()->table() . '.DTT_EVT_end >= "' . date('Y-m-d 0:0:00', strtotime( $month )) . '" ';
		}
		return $SQL;
	}


	/**
	 * 	posts_orderby
	 *
	 *  @access 	public
	 *  @return 	void
	 */
	public function posts_orderby( $SQL, WP_Query $wp_query ) {
		if ( isset( $wp_query->query ) && isset( $wp_query->query['post_type'] ) && $wp_query->query['post_type'] == 'espresso_events' ) {			
			$SQL = EED_Events_Archive::posts_orderby_sql( array( 'start_date' ));
		}
		return $SQL;
	}


	/**
	 * 	posts_orderby_sql
	 * 
	 * 	possible parameters:
	 * 	ID
	 * 	start_date
	 * 	end_date
	 * 	event_name
	 * 	category_slug
	 * 	ticket_start
	 * 	ticket_end
	 * 	venue_title 
	 * 	city
	 * 	state
	 * 
	 * 	**IMPORTANT**  
	 * 	make sure to also send the $orderby_params array to the posts_join_for_orderby() method
	 * 	or else some of the table references below will result in MySQL errors
	 *
	 *  @access 	public
	 *  @param	boolean	$orderby_params
	 *  @return 	string
	 */
	public static function posts_orderby_sql( $orderby_params = array(), $sort = 'ASC' ) {
		global $wpdb;
		$SQL = '';
		$cntr = 0;
		$orderby_params = is_array( $orderby_params ) ? $orderby_params : array( $orderby_params );
		foreach( $orderby_params as $orderby ) {
			$glue = $cntr == 0 || $cntr == count( $orderby_params ) ? ' ' : ', ';
			switch ( $orderby ) {
				
				case 'id' :
				case 'ID' :
					$SQL .= $glue . $wpdb->posts . '.ID ' . $sort;
					break;
				
				case 'end_date' :
					$SQL .= $glue . EEM_Datetime::instance()->table() . '.DTT_EVT_end ' . $sort;
					break;
				
				case 'event_name' :
					$SQL .= $glue . $wpdb->posts . '.post_title ' . $sort;
					break;
				
				case 'category_slug' :
					$SQL .= $glue . $wpdb->terms . '.slug ' . $sort;
					break;
				
				case 'ticket_start' :
					$SQL .= $glue . EEM_Ticket::instance()->table() . '.TKT_start_date ' . $sort;
					break;
				
				case 'ticket_end' :
					$SQL .= $glue . EEM_Ticket::instance()->table() . '.TKT_end_date ' . $sort;
					break;
				
				case 'venue_title' :
					$SQL .= $glue . 'venue_title ' . $sort;
					break;
				
				case 'city' :
					$SQL .= $glue . EEM_Venue::instance()->second_table() . '.VNU_city ' . $sort;
				break;
				
				case 'state' :
					$SQL .= $glue . EEM_State::instance()->table() . '.STA_name ' . $sort;
				break;

				case 'start_date' :
				default :
					$SQL .= $glue . EEM_Datetime::instance()->table() . '.DTT_EVT_start ' . $sort;
					break;
				
				
			}
			$cntr++;
		}
		return  $SQL;
	}



	/**
	 * 	template_include
	 *
	 *  	@access 	public
	 *  	@return 	void
	 */
	public static function template_include( $template ) {
		// ensure valid EE_Events_Archive_Config() object exists
		EED_Events_Archive::set_config();
		// don't add content filter for dedicated EE child themes or private posts
		if ( ! EEH_Template::is_espresso_theme() && ! post_password_required() ) {
			// add status banner ?
			if ( EE_Registry::instance()->CFG->template_settings->EED_Events_Archive->display_status_banner ) {
				add_filter( 'the_title', array( 'EED_Events_Archive', 'the_title' ), 100, 2 );
			}
			// if NOT a custom template
			if ( EE_Front_Controller::instance()->get_selected_template() != 'archive-espresso_events.php' ) {
				// load functions.php file for the theme (loaded by WP if using child theme)
				EEH_Template::load_espresso_theme_functions();			
				// because we don't know if the theme is using the_excerpt()
				add_filter( 'the_excerpt', array( 'EED_Events_Archive', 'event_details' ), 100, 1 );
				// or the_content
				add_filter( 'the_content', array( 'EED_Events_Archive', 'event_details' ), 100, 1 );
				// and just in case they are running get_the_excerpt() which DESTROYS things, you can copy the following to your functions.php file
//				add_filter( 'get_the_excerpt', array( 'EED_Events_Archive', 'get_the_excerpt' ), 1, 1 );
				// don't diplay entry meta because the existing theme will take care of that
				add_filter( 'FHEE__content_espresso_events_details_template__display_entry_meta', '__return_false' );
			} 
		} 

		return $template;
	}



	/**
	 * 	get_the_excerpt - kinda hacky, but if a theme is using get_the_excerpt(), then we need to remove our filters on the_content() 
	 *
	 *  	@access 	public
	 * 	@param		string 	$excerpt
	 *  	@return 		void
	 */
	public static function get_the_excerpt( $excerpt = '' ) {
		remove_filter( 'the_excerpt', array( 'EED_Events_Archive', 'event_details' ), 100, 1 );		
		remove_filter( 'the_content', array( 'EED_Events_Archive', 'event_details' ), 100, 1 );	
		return $excerpt;
	}



	/**
	 * 	the_title
	 *
	 *  	@access 	public
	 * 	@param		string 	$title
	 *  	@return 		void
	 */
	public static function the_title( $title = '', $id = '' ) {
		global $post;
		return in_the_loop() && $post->ID == $id ? espresso_event_status_banner( $post->ID  ) . $title :  $title;
	}



	/**
	 * 	event_details
	 *
	 *  	@access 	public
	 * 	@param		string 	$content
	 *  	@return 		void
	 */
	public static function event_details( $content ) {
		global $post;
		if ( $post->post_type == 'espresso_events' && ! apply_filters( 'FHEE__EES_Espresso_Events__process_shortcode__true', FALSE )) {
			// we need to first remove this callback from being applied to the_content() (otherwise it will recurse and blow up the interweb)
			remove_filter( 'the_excerpt', array( 'EED_Events_Archive', 'event_details' ), 100, 1 );		
			remove_filter( 'the_content', array( 'EED_Events_Archive', 'event_details' ), 100, 1 );		
			//now add additional content depending on whether event is using the_excerpt() or the_content()
			EED_Events_Archive::_add_additional_excerpt_filters();
			EED_Events_Archive::_add_additional_content_filters();
			// now load our template
			$template = EEH_Template::locate_template( 'content-espresso_events-details.php' );
			// re-add our main filters (or else the next event won't have them)
			add_filter( 'the_excerpt', array( 'EED_Events_Archive', 'event_details' ), 100, 1 );
			add_filter( 'the_content', array( 'EED_Events_Archive', 'event_details' ), 100, 1 );
			// but remove the other filters so that they don't get applied to the next post
			remove_filter( 'the_excerpt', array( 'EED_Events_Archive', 'event_tickets' ), 110, 1 );
			remove_filter( 'the_excerpt', array( 'EED_Events_Archive', 'event_datetimes' ), 120, 1 );
			remove_filter( 'the_excerpt', array( 'EED_Events_Archive', 'event_venues' ), 130, 1 );
			remove_filter( 'the_content', array( 'EED_Events_Archive', 'event_tickets' ), 110, 1 );
			remove_filter( 'the_content', array( 'EED_Events_Archive', 'event_datetimes' ), 120, 1 );
			remove_filter( 'the_content', array( 'EED_Events_Archive', 'event_venues' ), 130, 1 );
		}
		// we're not returning the $content directly because the template we are loading uses the_content (or the_excerpt)
		return ! empty( $template ) ? $template : $content;
	}



	/**
	 * 	_add_additional_content_filters
	 *
	 *  	@access 	private
	 *  	@return 		void
	 */
	private static function _add_additional_excerpt_filters() {
		add_filter( 'the_excerpt', array( 'EED_Events_Archive', 'event_tickets' ), 110, 1 );
		add_filter( 'the_excerpt', array( 'EED_Events_Archive', 'event_datetimes' ), 120, 1 );
		add_filter( 'the_excerpt', array( 'EED_Events_Archive', 'event_venues' ), 130, 1 );
	}



	/**
	 * 	_add_additional_content_filters
	 *
	 *  	@access 	private
	 *  	@return 		void
	 */
	private static function _add_additional_content_filters() {
		add_filter( 'the_content', array( 'EED_Events_Archive', 'event_tickets' ), 110, 1 );
		add_filter( 'the_content', array( 'EED_Events_Archive', 'event_datetimes' ), 120, 1 );
		add_filter( 'the_content', array( 'EED_Events_Archive', 'event_venues' ), 130, 1 );
	}



	/**
	 * 	event_tickets
	 *
	 *  	@access 	public
	 * 	@param		string 	$content
	 *  	@return 		void
	 */
	public static function event_tickets( $content ) {
		return $content . EEH_Template::locate_template( 'content-espresso_events-tickets.php' );
	}

	/**
	 * 	event_datetimes
	 *
	 *  	@access 	public
	 * 	@param		string 	$content
	 *  	@return 		void
	 */
	public static function event_datetimes( $content ) {
		return $content . EEH_Template::locate_template( 'content-espresso_events-datetimes.php' );
	}

	/**
	 * 	event_venues
	 *
	 *  	@access 	public
	 * 	@param		string 	$content
	 *  	@return 		void
	 */
	public static function event_venues( $content ) {
		return $content . EEH_Template::locate_template( 'content-espresso_events-venues.php' );
	}

	/**
	 * 	remove_all_events_archive_filters
	 *
	 *  	@access 	public
	 *  	@return 		void
	 */
	public static function remove_all_events_archive_filters() {
		remove_filter( 'the_title', array( 'EED_Events_Archive', 'the_title' ), 100, 2 );
		remove_filter( 'the_excerpt', array( 'EED_Events_Archive', 'event_details' ), 100, 1 );
		remove_filter( 'the_excerpt', array( 'EED_Events_Archive', 'event_tickets' ), 110, 1 );
		remove_filter( 'the_excerpt', array( 'EED_Events_Archive', 'event_datetimes' ), 120, 1 );
		remove_filter( 'the_excerpt', array( 'EED_Events_Archive', 'event_venues' ), 130, 1 );
		remove_filter( 'the_content', array( 'EED_Events_Archive', 'event_details' ), 100, 1 );
		remove_filter( 'the_content', array( 'EED_Events_Archive', 'event_tickets' ), 110, 1 );
		remove_filter( 'the_content', array( 'EED_Events_Archive', 'event_datetimes' ), 120, 1 );
		remove_filter( 'the_content', array( 'EED_Events_Archive', 'event_venues' ), 130, 1 );
		// don't diplay entry meta because the existing theme will take care of that
		remove_filter( 'FHEE__content_espresso_events_details_template__display_entry_meta', '__return_false' );
	}






	/**
	 * 	_initial_setup
	 *
	 *  @access 	public
	 *  @return 	void
	 */
	private function _load_assests() {
		do_action( 'AHEE__EED_Events_Archive__before_load_assests' );
		add_filter( 'FHEE_load_css', '__return_true' );
		add_filter( 'FHEE_load_EE_Session', '__return_true' );
		add_action('wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ), 10 );
		if ( EE_Registry::instance()->CFG->map_settings->use_google_maps ) {
			EE_Registry::instance()->load_helper( 'Maps' );
			add_action('wp_enqueue_scripts', array( 'EEH_Maps', 'espresso_google_map_js' ), 11 );
		}
		EE_Registry::instance()->load_helper( 'Event_View' );
	}





	/**
	 * 	_get_template
	 *
	 *  @access 	private
	 *  @return 	string
	 */
	private function _get_template( $which = 'part' ) {
		return EE_TEMPLATES . EE_Config::get_current_theme() . DS . 'archive-espresso_events.php';		
	}



	/**
	 * 	excerpt_length
	 *
	 *  @access 	public
	 *  @return 	void
	 */
	public function excerpt_length( $length ) {
		
		if ( self::$_type == 'grid' ) {
			return 36;
		}
		
		switch ( EE_Registry::instance()->CFG->template_settings->EED_Events_Archive->event_list_grid_size ) {
			case 'tiny' :
				return 12;
				break;
			case 'small' :
				return 24;
				break;
			case 'large' :
				return 48;
				break;
			case 'medium' :
			default :
				return 36;
		}		
	}



	/**
	 * 	excerpt_more
	 *
	 *  @access 	public
	 *  @return 	void
	 */
	public function excerpt_more( $more ) {
		return '&hellip;';
	}





	/**
	 * 	wp_enqueue_scripts
	 *
	 *  @access 	public
	 *  @return 	void
	 */
	public function wp_enqueue_scripts() {
		// get some style
		if ( apply_filters( 'FHEE_enable_default_espresso_css', FALSE ) ) {
			// first check uploads folder
			if ( file_exists( get_stylesheet_directory() . $this->theme . DS . 'style.css' )) {
				wp_register_style( $this->theme, get_stylesheet_directory_uri() . $this->theme . DS . 'style.css', array( 'dashicons', 'espresso_default' ));
			} else {
				wp_register_style( $this->theme, EE_TEMPLATES_URL . $this->theme . DS . 'style.css', array( 'dashicons', 'espresso_default' ));
			}
//			if ( file_exists( get_stylesheet_directory() . $this->theme . DS . 'archive-espresso_events.js' )) {
//				wp_register_script( $this->theme, get_stylesheet_directory_uri() . $this->theme . DS . 'archive-espresso_events.js', array( 'jquery-masonry' ), '1.0', TRUE  );
//			} else {
//				wp_register_script( $this->theme, EVENTS_ARCHIVE_ASSETS_URL . 'archive-espresso_events.js', array( 'jquery-masonry' ), '1.0', TRUE );
//			}
			wp_enqueue_style( $this->theme );
//			wp_enqueue_script( 'jquery-masonry' );
//			wp_enqueue_script( $this->theme );
//			add_action( 'wp_footer', array( 'EED_Events_Archive', 'localize_grid_event_lists' ), 1 );
		}
	}




	/**
	 * 	template_settings_form
	 *
	 *  @access 	public
	 *  @static
	 *  @return 	void
	 */
	public static function localize_grid_event_lists() {
//		wp_localize_script( 'archive-espresso_events', 'espresso_grid_event_lists', EED_Events_Archive::$espresso_grid_event_lists );
	}




	/**
	 * 	template_settings_form
	 *
	 *  @access 	public
	 *  @static
	 *  @return 	void
	 */
	public static function template_settings_form() {
		$EE = EE_Registry::instance();
		$EE->CFG->template_settings->EED_Events_Archive = isset( $EE->CFG->template_settings->EED_Events_Archive ) ? $EE->CFG->template_settings->EED_Events_Archive : new EE_Events_Archive_Config();
		$EE->CFG->template_settings->EED_Events_Archive = apply_filters( 'FHEE__EED_Events_Archive__template_settings_form__event_list_config', $EE->CFG->template_settings->EED_Events_Archive );
		EEH_Template::display_template( EVENTS_ARCHIVE_TEMPLATES_PATH . 'admin-event-list-settings.template.php', $EE->CFG->template_settings->EED_Events_Archive );
	}





	/**
	 * 	set_default_settings
	 *
	 *  @access 	public
	 *  @static
	 *  @return 	void
	 */
	public static function set_default_settings( $CFG ) {
		$CFG->display_description = isset( $CFG->display_description ) ? $CFG->display_description : 1;
		$CFG->display_address = isset( $CFG->display_address ) ? $CFG->display_address : TRUE;
		$CFG->display_venue_details = isset( $CFG->display_venue_details ) ? $CFG->display_venue_details : TRUE;
		$CFG->display_expired_events = isset( $CFG->display_expired_events ) ? $CFG->display_expired_events : FALSE;
		$CFG->display_status_banner = isset( $CFG->display_status_banner) ? $CFG->display_status_banner : FALSE;
		return $CFG;
	}



	/**
	 * 	filter_config
	 *
	 *  @access 	public
	 *  @return 	void
	 */
	public static function filter_config( $CFG ) {
		return $CFG;
	}




	/**
	 * 	filter_config
	 *
	 *  @access 	public
	 *  @return 	void
	 */
	public static function update_template_settings( $CFG, $REQ ) {
//		printr( $REQ, '$REQ  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span>', 'auto' );
//		printr( $CFG, '$CFG  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span>', 'auto' );
		//$CFG->template_settings->EED_Events_Archive = new stdClass();
		$CFG->EED_Events_Archive->display_description = isset( $REQ['display_description_in_event_list'] ) ? absint( $REQ['display_description_in_event_list'] ) : 1;
		$CFG->EED_Events_Archive->display_address = isset( $REQ['display_address_in_event_list'] ) ? absint( $REQ['display_address_in_event_list'] ) : TRUE;
		$CFG->EED_Events_Archive->display_venue_details = isset( $REQ['display_venue_details_in_event_list'] ) ? absint( $REQ['display_venue_details_in_event_list'] ) : TRUE;
		$CFG->EED_Events_Archive->display_expired_events = isset( $REQ['display_expired_events'] ) ? absint( $REQ['display_expired_events'] ) : FALSE;
//		$CFG->EED_Events_Archive->default_type = isset( $REQ['default_type'] ) ? sanitize_text_field( $REQ['default_type'] ) : 'grid';
//		$CFG->EED_Events_Archive->event_list_grid_size = isset( $REQ['event_list_grid_size'] ) ? sanitize_text_field( $REQ['event_list_grid_size'] ) : 'medium';
//		$CFG->EED_Events_Archive->templates = array(
//				'full'  => EE_TEMPLATES . EE_Config::get_current_theme() . DS . 'archive-espresso_events.php'
//			);
		
//		switch ( $CFG->EED_Events_Archive->default_type ) {
//			case 'dates' :
//					$CFG->EED_Events_Archive->templates['part'] = EE_TEMPLATES . EE_Config::get_current_theme() . DS . 'archive-espresso_events-dates-view.php';
//				break;
//			case 'text' :
//					$CFG->EED_Events_Archive->templates['part'] = EE_TEMPLATES . EE_Config::get_current_theme() . DS . 'archive-espresso_events-text-view.php';
//				break;
//			default :
//					$CFG->EED_Events_Archive->templates['part'] = EE_TEMPLATES . EE_Config::get_current_theme() . DS . 'archive-espresso_events.php';
//		}
		
		$CFG->EED_Events_Archive = isset( $REQ['reset_event_list_settings'] ) && absint( $REQ['reset_event_list_settings'] ) == 1 ? new EE_Events_Archive_Config() : $CFG->EED_Events_Archive;
		$CFG->EED_Events_Archive->display_status_banner = !empty( $REQ['display_status_banner'] ) && $REQ['display_status_banner'] ? TRUE : FALSE;
		return $CFG;
	}





	/**
	 * 	get_template_part
	 *
	 *  @access 	public
	 *  @return 	void
	 */
	public static function get_template_part() {
		return 'archive-espresso_events.php';
//		switch ( self::$_type ) {
//			case 'dates' :
//					return 'archive-espresso_events-dates-view.php';
//				break;
//			case 'text' :
//					return 'archive-espresso_events-text-view.php';
//				break;
//			default :
//					return 'archive-espresso_events-grid-view.php';
//		}
		
//		return EE_Registry::instance()->CFG->EED_Events_Archive['templates']['part'];
	}



	/**
	 * 	event_list_template_filters
	 *
	 *  @access 	public
	 *  @return 	void
	 */
	public function event_list_template_filters() {
		$args = array(
			'form_url' => get_post_type_archive_link( 'espresso_events' ), //add_query_arg( array( 'post_type' => 'espresso_events' ), home_url() ),
			'elf_month' => EED_Events_Archive::_display_month(),
			'elf_category' => EED_Events_Archive::_event_category_slug(),
			'elf_show_expired' => EED_Events_Archive::_show_expired(),
			'elf_type' => self::$_type
		);
		EEH_Template::display_template( EE_TEMPLATES . EE_Config::get_current_theme() . DS . 'archive-espresso_events-filters.php', $args );		
	}





	/**
	 * 	event_list_css
	 *
	 *  @access 	public
	 *  @return 	void
	 */
	public static function event_list_css( $extra_class = '' ) {
		$EE = EE_Registry::instance();
		$event_list_css = ! empty( $extra_class ) ? array( $extra_class ) : array();
		$event_list_css[] = 'espresso-event-list-event';
//		if ( self::$_type == 'grid' ) {
//			$event_list_grid_size = isset( $EE->CFG->template_settings->EED_Events_Archive->event_list_grid_size ) ? $EE->CFG->template_settings->EED_Events_Archive->event_list_grid_size : 'medium';
//			$event_list_css[] = $event_list_grid_size . '-event-list-grid';
//		}
		return implode( ' ', $event_list_css );
	}






	/**
	 * 	event_categories
	 *
	 *  @access 	public
	 *  @return 	void
	 */
	public static function event_categories() {
		return EE_Registry::instance()->load_model('Term')->get_all_ee_categories();
	}



	/**
	 * 	display_description
	 *
	 *  @access 	public
	 *  @return 	void
	 */
	public static function display_description( $value ) {
		$EE = EE_Registry::instance();
		$display_description= isset( $EE->CFG->template_settings->EED_Events_Archive->display_description ) ? $EE->CFG->template_settings->EED_Events_Archive->display_description : 1;
		return $display_description === $value ? TRUE : FALSE;
	}



	/**
	 * 	display_venue_details
	 *
	 *  @access 	public
	 *  @return 	void
	 */
	public static function display_venue_details() {
		$EE = EE_Registry::instance();
		$EE->load_helper( 'Venue_View' );
		$display_venue_details= isset( $EE->CFG->template_settings->EED_Events_Archive->display_venue_details ) ? $EE->CFG->template_settings->EED_Events_Archive->display_venue_details : TRUE;
		$venue_name = EEH_Venue_View::venue_name();
		return $display_venue_details && ! empty( $venue_name ) ? TRUE : FALSE;
	}


	/**
	 * 	display_address
	 *
	 *  @access 	public
	 *  @return 	void
	 */
	public static function display_address() {
		$EE = EE_Registry::instance();
		$EE->load_helper( 'Venue_View' );
		$display_address= isset( $EE->CFG->template_settings->EED_Events_Archive->display_address ) ? $EE->CFG->template_settings->EED_Events_Archive->display_address : FALSE;
		$venue_name = EEH_Venue_View::venue_name();
		return $display_address && ! empty( $venue_name ) ? TRUE : FALSE;
	}






	/**
	 * 	event_list_title
	 *
	 *  @access 	public
	 *  @return 	void
	 */
	public static function event_list_title() {
		return apply_filters( 'FHEE__archive_espresso_events_template__upcoming_events_h1', __( 'Upcoming Events', 'event_espresso' ));
	}	


}



function espresso_get_event_list_ID() {
	EED_Events_Archive::$espresso_event_list_ID++;
	EED_Events_Archive::$espresso_grid_event_lists[] = EED_Events_Archive::$espresso_event_list_ID;	
	return EED_Events_Archive::$espresso_event_list_ID;
}


//function espresso_grid_event_list( $ID ) {
//	
//	return $ID;
//}


function espresso_event_list_title() {
	return EED_Events_Archive::event_list_title();
}

function espresso_event_list_css( $extra_class = '' ) {
	return EED_Events_Archive::event_list_css( $extra_class );
}
 
function espresso_get_event_categories() {
	return EED_Events_Archive::event_categories();
}
 
function espresso_display_full_description_in_event_list() {
	return EED_Events_Archive::display_description( 2 );
}

function espresso_display_excerpt_in_event_list() {
	return EED_Events_Archive::display_description( 1 );
}

function espresso_event_list_template_part() {
	return EED_Events_Archive::get_template_part();
}

function espresso_display_venue_details_in_event_list() {
	return EED_Events_Archive::display_venue_details();
}

function espresso_display_venue_address_in_event_list() {
	return EED_Events_Archive::display_address();
}


function espresso_event_list_grid_size_btn() {
	switch( EE_Registry::instance()->CFG->template_settings->EED_Events_Archive->event_list_grid_size ) {
		case 'tiny' :
		case 'small' :
			return 'small';
			break;
		case 'medium' :
			return 'medium';
			break;
		case 'large' :
			return 'big';
			break;
		case 'huge' :
			return 'huge';
			break;
	}
}



class EE_Event_List_Query extends WP_Query {

	private $_title = NULL;
	private $_limit = 10;
	private $_css_class = NULL;
	private $_show_expired = FALSE;
	private $_month = NULL;
	private $_category_slug = NULL;
	private $_order_by = NULL;
	private $_sort = NULL;
//	private $_list_type ='text';	

	function __construct( $args = array() ) {
//		printr( $args, '$args  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span>', 'auto' );
		// incoming args could be a mix of WP query args + EE shortcode args
		foreach ( $args as $key =>$value ) {
			$property = '_' . $key;
			// if the arg is a property of this class, then it's an EE shortcode arg
			if ( EEH_Class_Tools::has_property( $this, $property )) {
				// set the property value
				$this->$property = $value;
				// then remove it from the array of args that will later be passed to WP_Query() 
				unset( $args[ $key ] );
			}
		}
		// parse orderby attribute
		if ( $this->_order_by !== NULL ) {
			$this->_order_by = explode( ',', $this->_order_by );
			$this->_order_by = array_map('trim', $this->_order_by);
		}
		$this->_sort = in_array( $this->_sort, array( 'ASC', 'asc', 'DESC', 'desc' )) ? strtoupper( $this->_sort ) : 'ASC';
		
		// first off, let's remove any filters from previous queries
//		remove_filter( 'EED_Events_Archive__set_type__type', array( $this, 'event_list_type' ));
		remove_filter( 'FHEE__archive_espresso_events_template__upcoming_events_h1', array( $this, 'event_list_title' )); 
		remove_all_filters( 'FHEE__content_espresso_events__event_class', array( $this, 'event_list_css' ));

		//  set view
//		add_filter( 'EED_Events_Archive__set_type__type', array( $this, 'event_list_type' ), 10, 1 );
		// have to call this in order to get the above filter applied
//		EED_Events_Archive::set_type();
		// Event List Title ?
		add_filter( 'FHEE__archive_espresso_events_template__upcoming_events_h1', array( $this, 'event_list_title' ), 10, 1 ); 
		// add the css class
		add_filter( 'FHEE__content_espresso_events__event_class', array( $this, 'event_list_css' ), 10, 1 );

		// Force these args
		$args = array_merge( $args, array(
			'post_type' => 'espresso_events',
			'posts_per_page' => $this->_limit,
			'update_post_term_cache' => FALSE,
			'update_post_meta_cache' => FALSE
		));
		// filter the query parts
		add_filter( 'posts_join', array( $this, 'posts_join' ), 10, 1 );
		add_filter( 'posts_where', array( $this, 'posts_where' ), 10, 1 );
		add_filter( 'posts_orderby', array( $this, 'posts_orderby' ), 10, 1 );
		
		// run the query
		parent::__construct( $args );
	}



	/**
	 * 	posts_join
	 *
	 *  @access 	public
	 *  @return 	string
	 */
	public function posts_join( $SQL ) {
		// first off, let's remove any filters from previous queries
		remove_filter( 'posts_join', array( $this, 'posts_join' ));
		// generate the SQL
		if ( $this->_category_slug !== NULL ) {
			$SQL .= EED_Events_Archive::posts_join_sql_for_terms( TRUE );
		}
		if ( $this->_order_by !== NULL ) {
			$SQL .= EED_Events_Archive::posts_join_for_orderby( $this->_order_by );
		}
		return $SQL;
	}


	/**
	 * 	posts_where
	 *
	 *  @access 	public
	 *  @return 	string
	 */
	public function posts_where( $SQL ) {
		// first off, let's remove any filters from previous queries
		remove_filter( 'posts_where', array( $this, 'posts_where' ));
		// Show Expired ?
		$this->_show_expired = $this->_show_expired ? TRUE : FALSE;
		$SQL .= EED_Events_Archive::posts_where_sql_for_show_expired( $this->_show_expired );
		// Category
		$SQL .=  EED_Events_Archive::posts_where_sql_for_event_category_slug( $this->_category_slug );
		// Start Date
		$SQL .= EED_Events_Archive::posts_where_sql_for_event_list_month( $this->_month );
		return $SQL;
	}


	/**
	 * 	posts_orderby
	 *
	 *  @access 	public
	 *  @return 	string
	 */
	public function posts_orderby( $SQL ) {
		// first off, let's remove any filters from previous queries
		remove_filter( 'posts_orderby', array( $this, 'posts_orderby' ) );
		// generate the SQL
		$SQL =  EED_Events_Archive::posts_orderby_sql( $this->_order_by, $this->_sort );
		return $SQL;
	}


	/**
	 * 	event_list_title
	 *
	 *  @access 	public
	 *  @return 	string
	 */
	public function event_list_type( $event_list_type ) {
		if ( ! empty( $this->_list_type )) {
			return $this->_list_type;
		}
		return $event_list_type;
	}


	/**
	 * 	event_list_title
	 *
	 *  @access 	public
	 *  @return 	string
	 */
	public function event_list_title( $event_list_title ) {
		if ( ! empty( $this->_title )) {
			return $this->_title;
		}
		return $event_list_title;
	}



	/**
	 * 	event_list_css
	 *
	 *  @access 	public
	 *  @return 	array
	 */
	public function event_list_css( $event_list_css ) {
		$event_list_css .=  ! empty( $event_list_css ) ? ' ' : '';
		$event_list_css .=  ! empty( $this->_css_class ) ? $this->_css_class : '';
		$event_list_css .=  ! empty( $event_list_css ) ? ' ' : '';
		$event_list_css .=  ! empty( $this->_category_slug ) ? $this->_category_slug : '';
		return $event_list_css;
	}






}





// End of file EED_Events_Archive.module.php
// Location: /modules/events_archive/EED_Events_Archive.module.php
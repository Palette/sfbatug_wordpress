<?php if (!defined('EVENT_ESPRESSO_VERSION') )
	exit('NO direct script access allowed');
/**
 * Event Espresso
 *
 * Event Registration and Management Plugin for Wordpress
 *
 * @package		Event Espresso
 * @author		Seth Shoultes
 * @copyright	(c)2009-2012 Event Espresso All Rights Reserved.
 * @license		@link http://eventespresso.com/support/terms-conditions/  ** see Plugin Licensing * *
 * @link		http://www.eventespresso.com
 * @version		4.0
 *
 * ------------------------------------------------------------------------
 *
 * EE_Message_Template class
 *
 *
 * @package		Event Espresso
 * @subpackage	includes/classes/EE_Message_Template.class.php
 * @author		Darren Ethier
 *
 * ------------------------------------------------------------------------
 */
class EE_Message_Template extends EE_Base_Class {

	/**
	 * Template ID
	 * primary key
	 * 
	 * @access protected
	 * @var int
	 */
	protected $_MTP_ID = FALSE;
	
	/**
	 * Group ID
	 * 
	 * @access protected
	 * @var int
	 */
	protected $_GRP_ID = FALSE;


	/**
	 * Template context
	 * 
	 * @access protected
	 * @var string
	 */
	protected $_MTP_context = NULL;

	/**
	 * Template field
	 * 
	 * @access protected
	 * @var string
	 */
	protected $_MTP_template_field = NULL;

	/**
	 * template content
	 * 
	 * @access protected
	 * @var string
	 */
	protected $_MTP_content = NULL;


	

	/**
	 * Related Message Template Group this template belongs to
	 * @var EE_Message_Template_Group
	 */
	protected $_Message_Template_Group = NULL;




	public static function new_instance( $props_n_values = array(), $timezone = NULL ) {
		$classname = __CLASS__;
		$has_object = parent::_check_for_object( $props_n_values, $classname, $timezone );
		return $has_object ? $has_object : new self( $props_n_values, FALSE, $timezone );
	}


	public static function new_instance_from_db ( $props_n_values = array(), $timezone = NULL ) {
		return new self( $props_n_values, TRUE, $timezone );
	}




	public function set_group_template_id ( $GRP_ID = FALSE ) {
		if ( !$GRP_ID ) {
			throw new EE_Error( __('Missing required value for the message template group id', 'event_espresso') );
		}

		$this->set('GRP_ID', $GRP_ID );
	}



	/**
	 * get Group ID
	 * @access public
	 * @return int
	 */
	public function GRP_ID() {
		return $this->get('GRP_ID');
	}




	/**
	 * get Event ID
	 * @access public
	 * @return int 
	 */
	public function event() {
		return $this->get_first_related('Message_Template_Group')->get('EVT_ID');
	}



	/**
	 * this returns the event_name for the event attached to the group
	 *
	 * @access public
	 * @return string
	 */
	public function event_name() {
		return $this->get_first_related('Message_Template_Group')->event_name();
	}



	/**
	 * get User ID
	 * @access public
	 * @return int
	 */
	public function user() {
		return $this->get_first_related('Message_Template_Group')->get('MTP_user_id');
	}



	/**
	 * get Message Messenger
	 * @access public
	 * @return string
	 */
	public function messenger() {
		return $this->get_first_related('Message_Template_Group')->messenger();
	}




	/**
	 * get Message Messenger OBJECT
	 *
	 * @access public
	 * @return object Messenger Object for the given messenger
	 */
	public function messenger_obj() {
		return $this->get_first_related('Message_Template_Group')->messenger_obj();
	}



	/**
	 * get Message Type
	 * 
	 * @access public
	 * @return string
	 */
	public function message_type() {
		return $this->get_first_related('Message_Template_Group')->message_type();
	}




	/**
	 * get Message type OBJECT
	 *
	 * @access public
	 * @return object  Message Type object for the given message type
	 */
	public function message_type_obj() {
		return $this->get_first_related('Message_Template_Group')->message_type_obj();
	}



	/**
	 * This returns the set context array configured in the message type object
	 *
	 * @access public
	 * @return array array of contexts and their configuration.
	 */
	public function contexts_config() {
		return $this->get_first_related('Message_Template_Group')->contexts_config();
	}



	/**
	 * This returns the context_label for contexts as set in the message type object
	 *
	 * @access public
	 * @return string label for "context"
	 */
	public function context_label() {
		return $this->get_first_related('Message_Template_Group')->context_label();
	}




	/**
	 * this returns if the template group this template belongs to is global
	 * @return boolean true if it is, false if it isn't
	 */
	public function is_global() {
		return $this->get_first_related('Message_Template_Group')->is_global();
	}



	/**
	 * this returns if the template group this template belongs to is active (i.e. turned "on" or not)
	 * @return boolean true if it is, false if it isn't
	 */
	public function is_active() {
		return $this->get_first_related('Message_Template_Group')->is_active();
	}





	/**
	 * This will return an array of shortcodes => labels from the messenger and message_type objecst associated with this template.
	 * 
	 * @access public
	 * @param string $context what context we're going to return shortcodes for
	 * @param array $fields what fields we're returning valid shortcodes for.  If empty then we assume all fields are to be merged and returned.
	 * @return mixed (array|bool) an array of shortcodes in the format array( '[shortcode] => 'label') OR FALSE if no shortcodes found.
	 */
	public function get_shortcodes( $context, $fields = array() ) {
		return $this->get_first_related('Message_Template_Group')->get_shortcodes($context, $fields);
	}


}
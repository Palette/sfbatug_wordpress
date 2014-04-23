<?php
/*
 Plugin Name: Buddypress profile view from admin
 Plugin URI: http://webavenue.com.au
 Description: This plugin allows admin to view buddypress profile.
 Requires at least: WordPress 2.9.1 / BuddyPress 1.2
 Tested up to: WordPress 2.9.1 / BuddyPress 1.2
 Author: Rameshwor Maharjan
 Version: 1.0
 Author URI: http://www.webavenue.com.au
 */

/**  Copyright 2013
 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */
 
 if ( ! class_exists( 'buddypress_profile_admin' ) ) {
 	class buddypress_profile_admin {
		
		var $plugin_id = 'buddypress_profile_admin';
		var $admin_page = 'buddypress_profile_admin_admin';
		var $user_status = array("approve", "deny");
		
		public function buddypress_profile_admin() {
			$this->__construct();
		}
		
		public function __construct() {
	
			register_activation_hook( __FILE__, array( $this, 'activation_check' ) );	
			
			add_action( 'init', array( $this, 'init' ) );
			
			/* register filters */
			add_filter( 'user_row_actions', array( $this, 'user_action_links' ), 10, 2 );
			add_action('admin_menu',  array( $this, 'buddypress_profile_admin_menu' ) );
		}
		
		/* check the wordpress version compatibility */
		public function activation_check() {
			
			global $wp_version;	
			$min_wp_version = '3.2.1';
			$exit_msg = sprintf( __( 'Buddypress profile view and edit from admin requires WordPress %s or newer.', $this->plugin_id ), $min_wp_version );
			if ( version_compare( $wp_version, $min_wp_version, '<=' ) ) {
				exit( $exit_msg );
			}
		}
		
		/* load any javascript and css needed for the plugin */
		public function init() {
			if ( is_admin() ) {

				wp_enqueue_style( 'buddypress_profile_admin_css', plugins_url( '/css/style.css', __FILE__ ) );	
			}
		}
		
		public function user_action_links($actions, $user_object) {
	
			$view_profile = 'users.php?page=view-edit-buddypress&user_id='. $user_object->ID;
			$actions['buddypress_user_profile_view'] = "<a href='" . admin_url( $view_profile ) . "'>" . __( "View Profile" , 'buddypress_profile_admin' ) . "</a>";
			return $actions;
			
		}
		
		function buddypress_profile_admin_menu() {
			
			 add_submenu_page( NULL , "View and Edit Buddypress Profile", "View Buddypress Profile", 1, "view-edit-buddypress", array( $this, 'view_edit_buddypress' ) );
		}
		
		function view_edit_buddypress() {
			
			include( 'view.php' );
		}
		
		
	}
 }
 
 // initialize class
if ( class_exists( 'buddypress_profile_admin' ) ) {
	$buddypress_profile_admin = new buddypress_profile_admin();
}
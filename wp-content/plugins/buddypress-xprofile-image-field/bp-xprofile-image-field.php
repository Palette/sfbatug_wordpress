<?php 
/* 
Plugin Name: BuddyPress XProfile Image Field
Plugin URI: http://nerdonia.co.ke/
Description: BuddyPress XProfile addon that adds an Image field type
Version: 1.1.0
Author: Alex Githatu
Author URI: http://nerdonia.co.ke/buddypress-xprofile-image-field
License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
Copyright: 2014  Alex Githatu  ( email : alex@nerdonia.co.ke )
*/



if( ! class_exists( 'BP_XProfile_Image_Field' ) ) {
    class BP_XProfile_Image_Field {
        
        private static $instance;
	
	public $file;
	
	public $plugin_path;
	
	public $plugin_url;
	
	public $version;
        
        
        public static function instance() {
            if ( ! isset( self::$instance ) && ! ( self::$instance instanceof BP_XProfile_Image_Field ) ) {
                self::$instance = new BP_XProfile_Image_Field(__FILE__);
            }
            return self::$instance;
	}
        
        private function __construct( $file ) {
		
		$this->version = '1.1.0';
		$this->file = $file;
		$this->plugin_url = trailingslashit( plugins_url( '', $plugin = $file ) );
		$this->plugin_path = trailingslashit( dirname( $file ) );
		
		                
		// Add new image field type
		add_filter( 'xprofile_field_types', array( $this, 'bpxp_image_field_add_field_type') );
                
                // Render the image field on the admin panel
                add_filter( 'xprofile_admin_field', array( $this, 'bpxp_image_field_admin_render_field_type') );
                
                // Render the image field on the front-end
                add_action( 'bp_custom_profile_edit_fields', array( $this, 'bpxp_image_field_edit_render_field') );
                
                // take over the rendering of the profile edit screen in order to handle the image field
                add_action( 'bp_actions', array( $this, 'bpxp_image_field_override_xprofile_screen_edit_profile'), 10 );
                
                // load javascript
                add_action( 'wp_print_scripts', array( $this, 'bpxp_image_field_load_js') );
		
	}
        
        
        function bpxp_image_field_load_js() {
            wp_enqueue_script( 'bpxp_image_field-js', $this->plugin_url . '/js/bp-xp-img-fld.js', array( 'jquery' ), '1.0.0' );
        }
        
        function bpxp_image_field_add_field_type($field_types){
            $image_field_type = array('image');
            $field_types = array_merge($field_types, $image_field_type);
            return $field_types;
        }

        
        function bpxp_image_field_admin_render_field_type($field, $echo = true){

            do_action('bpxp_image_field_before_admin_render');
            
            ob_start();
                switch ( $field->type ) {
                    case 'image':
                        ?>
                            <input type="file" name="<?php bp_the_profile_field_input_name() ?>" id="<?php bp_the_profile_field_input_name() ?>" value="" />
                        <?php
                        break;    
                    default :
                        ?>
                            <p>Field type unrecognized</p>
                        <?php
                }

                $output = ob_get_contents();
            ob_end_clean();
            
            do_action('bpxp_image_field_after_admin_render', $output);

            if($echo){
                echo $output;
                return;
            }
            else{
                return $output;
            }

        }

        


        function bpxp_image_field_edit_render_field($echo = true){

            if ( bp_get_the_profile_field_type() == 'image' ){
                
                if(empty ($echo)){
                    $echo = true;
                }
                
                do_action('bpxp_image_field_before_edit_render');

                ob_start();    
                    $image_field_input_name = bp_get_the_profile_field_input_name();
                    $field_name_hidden = 'field_' . bp_get_the_profile_field_id() . '_hidden';
                    $image = WP_CONTENT_URL . bp_get_the_profile_field_edit_value();

                ?>
                        <label for="<?php bp_the_profile_field_input_name(); ?>"><?php bp_the_profile_field_name(); ?> <?php if ( bp_get_the_profile_field_is_required() ) : ?><?php _e( '(required)', 'buddypress' ); ?><?php endif; ?></label>
                        <input type="file" name="<?php echo $image_field_input_name; ?>" id="<?php echo $image_field_input_name; ?>" value="" <?php if ( bp_get_the_profile_field_is_required() ) : ?>aria-required="true"<?php endif; ?>/>
                        <input type="hidden" name="<?php echo $field_name_hidden; ?>" id="<?php echo $field_name_hidden; ?>" value="<?php echo bp_get_the_profile_field_edit_value(); ?>" />
                        <!--<img src="<?php echo $image; ?>" alt="<?php bp_the_profile_field_name(); ?>" />-->

                <?php

                    $output = ob_get_contents();
                ob_end_clean();

                do_action('bpxp_image_field_after_edit_render', $output);
                
                
                if($echo){
                    echo $output;
                    return;
                }
                else{
                    return $output;
                }
                
            }

        }

        

        // Override default action hook in order to support images
        function bpxp_image_field_override_xprofile_screen_edit_profile(){
            $screen_edit_profile_priority = has_filter('bp_screens', 'xprofile_screen_edit_profile');

            if($screen_edit_profile_priority !== false){
                
                if ( isset( $_POST['field_ids'] ) ) { //only override during post
                    //Remove the default profile_edit handler
                    remove_action( 'bp_screens', 'xprofile_screen_edit_profile', $screen_edit_profile_priority );

                    //Install replalcement hook
                    add_action( 'bp_screens', array( $this, 'bpxp_image_field_edit_profile'), $screen_edit_profile_priority );
                }
            }
            
        }

        

        //Create profile_edit handler
        function bpxp_image_field_edit_profile($trigger_xprofile_edit = true){

            if ( isset( $_POST['field_ids'] ) ) {
                if($trigger_xprofile_edit === "") {
                    $trigger_xprofile_edit = true;
                }
                
                if(wp_verify_nonce( $_POST['_wpnonce'], 'bp_xprofile_edit' )){

                    $posted_field_ids = explode( ',', $_POST['field_ids'] );

                    $post_action_found = false;
                    $post_action = '';
                    if (isset($_POST['action'])){
                        $post_action_found = true;
                        
                        $post_action = apply_filters('bpxp_image_field_preserve_post_action', $_POST['action']);
                       
                    }

                    foreach ( (array)$posted_field_ids as $field_id ) {
                        $field_name = 'field_' . $field_id;

                        if ( isset( $_FILES[$field_name] ) ) {
                            if($_FILES[$field_name]['size'] > 0){
                                require_once( ABSPATH . '/wp-admin/includes/file.php' );
                                
                                $uploaded_file = $_FILES[$field_name]['tmp_name'];

                                // Filter the upload location
                                add_filter( 'upload_dir', array( $this, 'bpxp_image_field_profile_upload_dir'), 10, 1 );

                                //ensure WP accepts the upload job
                                $_POST['action'] = 'wp_handle_upload';

                                $wp_uploaded_file = wp_handle_upload( $_FILES[$field_name] );

                                $db_uploaded_file = str_replace(WP_CONTENT_URL, '', $wp_uploaded_file['url']) ;

                                $uploaded_file = apply_filters('bpxp_image_field_image_uploaded', $db_uploaded_file, $wp_uploaded_file);
                                
                                $_POST[$field_name] = $uploaded_file;
                            }
                            else{
                                $field_name_hidden = 'field_' . $field_id . '_hidden';
                                if ( isset( $_POST[$field_name_hidden] ) ) {
                                    $_POST[$field_name] = $_POST[$field_name_hidden];
                                }
                            }

                        }

                    }

                    if($post_action_found){
                        $_POST['action'] = apply_filters('bpxp_image_field_restore_post_action', $post_action);
                    }
                    else{
                        unset($_POST['action']);
                    }

                }
            }

            if($trigger_xprofile_edit){
                if(function_exists('xprofile_screen_edit_profile')){
                    xprofile_screen_edit_profile();
                }
            }

        }

        function bpxp_image_field_profile_upload_dir( $upload_dir ) {
            global $bp;

            $original_upload_dir = $upload_dir;
            $user_id = $bp->displayed_user->id;
            $profile_subdir = '/profiles/' . $user_id;

            $upload_dir['path'] = $upload_dir['basedir'] . $profile_subdir;
            $upload_dir['url'] = $upload_dir['baseurl'] . $profile_subdir;
            $upload_dir['subdir'] = $profile_subdir;

            $upload_dir = apply_filters('bpxp_image_field_upload_dir', $upload_dir, $original_upload_dir, $user_id);
            
            return $upload_dir;
        }
        
    } // end BP_XProfile_Image_Field Class
}


function bpxp_image_field_error_wordpress_version() {
    global $wp_version;
    
    echo '<div class="error"><p>' . __( "Please upgrade WordPress to version 3.2.1 or later. This plugin may not work properly on version {$wp_version}.", 'bpxp_image_field' ) . '</p></div>';
}

function bpxp_image_field_error_missing_xprofile() {
    
    echo '<div class="error"><p>' . sprintf( __( 'Please ensure you are running BuddyPress 1.5 or later and %sBuddyPress Extended Profiles Component%s is activated in order for this plugin to work.' ), '<a href="' . admin_url( 'options-general.php?page=bp-components' ) . '">', '</a>' ) . '</p></div>';
}


   /**
    * Creates the single BP_XProfile_Image_Field instance.
    *
    * 
    * 
    * @return BP_XProfile_Image_Field 
    * 
    */
function bpxp_image_field() {
    
    return BP_XProfile_Image_Field::instance();
}

function bpxp_image_field_init() {
    global $wp_version;

    if ( !version_compare( $wp_version, '3.2.1', '>=' ) ) {
        add_action( 'all_admin_notices', 'bpxp_image_field_error_wordpress_version' );
    } 
    elseif ( class_exists( 'BP_XProfile_Component') && version_compare( BP_VERSION, '1.5', '>=' ) ) {
        
        do_action('bpxp_image_field_before_load');
            bpxp_image_field(); 
        do_action('bpxp_image_field_after_load', bpxp_image_field());
        
        define( 'BPXP_IMAGE_FIELD_IS_LOADED', 1 );
        
    } 
    else {
        add_action( 'all_admin_notices', 'bpxp_image_field_error_missing_xprofile' );
    }
}

add_action( 'bp_xprofile_includes', 'bpxp_image_field_init' ); // Ensures it's only loaded if XProfile is active

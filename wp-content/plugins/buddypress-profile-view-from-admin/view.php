<?php
$user_id = $_REQUEST[ 'user_id' ];
$args = array( 'user_id' => $user_id);
$user_data = get_userdata( $user_id );
?>
<div id="profile-view">
    <div id="profile-view-avatar">
        <a href="<?php echo bp_core_get_user_domain( $user_data->ID ) ?>">
            <?php echo bp_core_fetch_avatar ( array( 'item_id' => $user_data->ID, 'type' => 'full', 'width' =>150, 'height' => 150 ) ) ?>
        </a>
    </div>
    <div id="profile-view-content">    
        <h2><a href="<?php echo bp_core_get_user_domain( $user_data->ID ) ?>"><?php echo $user_data->display_name;?></a></h2>
  
    	<span class="user-nicename">@<?php echo $user_data->user_nicename;?></span>
        <span class="activity"><span class="activity"><?php bp_last_activity( $user_data->ID ); ?></span></span>
        <p><label for="email"><strong>Email :</strong></label> <span><?php echo $user_data->user_email;?></span></p>
      
		<?php
       
        if ( bp_has_profile( $args ) ) :
        while ( bp_profile_groups() ) : bp_the_profile_group(); ?>
        
        <h3><?php echo bp_get_the_profile_group_name(); ?></h3>
        
        <?php while ( bp_profile_fields() ) : bp_the_profile_field(); ?>
        
      
			
        <div class="profile-info">
            <div class="profile-info-row">
                <label for="<?php bp_the_profile_field_input_name(); ?>"><?php bp_the_profile_field_name(); ?> :</label>
                <span class="profile-info-value">
			
                <?php bp_the_profile_field_edit_value(); ?>
               
                </span>
             </div>
        </div>
       
        <?php endwhile; ?>
        
        <?php endwhile; endif; ?>
        
    
    </div>
</div>
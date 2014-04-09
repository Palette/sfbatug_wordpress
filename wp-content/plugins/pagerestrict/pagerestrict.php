<?php
/*
Plugin Name: Page Restrict
Plugin URI: http://theandystratton.com/pagerestrict
Description: Restrict certain pages to logged in users
Author: Matt Martz & Andy Stratton
Author URI: http://theandystratton.com
Version: 2.1.2

	Copyright (c) 2008 Matt Martz (http://sivel.net)
        Page Restrict is released under the GNU Lesser General Public License (LGPL)
	http://www.gnu.org/licenses/lgpl-3.0.txt
*/

// ff we are in the admin load the admin functionality
if ( is_admin () )
	require_once( dirname ( __FILE__ ) . '/inc/admin.php' );

// get specific option
function pr_get_opt ( $option ) {
	$pr_options = get_option ( 'pr_options' );
	// clean up PHP warning for in_array() later when they have not been saved
	if ( $option == 'posts' || $option == 'pages' ) {
		if ( !is_array($pr_options[$option]) ) {
			$pr_options[$option] = array();
		}
	}
    return $pr_options[$option];
}

// Add headers to keep browser from caching the pages when user not logged in
// Resolves a problem where users see the login form after logging in and need 
// to refresh to see content
function pr_no_cache_headers () {
	if ( !is_user_logged_in() )
		nocache_headers();
}

// gets standard page content when page/post is restricted.
function pr_get_page_content() {
	$pr_page_content = '<h3 style="text-align:center;">' . pr_get_opt('message') . '</h3>';
		
	return $pr_page_content;
}

// Perform the restriction and if restricted replace the page content with a login form
function pr_page_restrict ( $pr_page_content ) {

	global $post;
	global $bp;

	$pr_check = pr_get_opt('method') == 'all';
	
	$pr_check = $pr_check || (
		( is_array(pr_get_opt('pages')) || is_array(pr_get_opt('posts')) ) 
		&& ( count(pr_get_opt('pages')) + count(pr_get_opt('posts')) > 0 )
	);
	$pr_check = $pr_check || ( pr_get_opt('pr_restrict_home') && is_home() );
	if ( !is_user_logged_in() && $pr_check ) {
		// Workaround to get post id in buddypress
		$post_id = get_page_by_title($post->post_name)->ID;
		// current post is in either page / post restriction array
		$is_restricted = ( in_array($post_id, pr_get_opt('pages')) || in_array($post_id, pr_get_opt('posts')) ) && pr_get_opt ( 'method' ) != 'none';
		// content is restricted OR everything is restricted
		if ( ($is_restricted || pr_get_opt('method') == 'all') ) {
			$pr_page_content = pr_get_page_content();
		}
    }
	return '<div class="page-restrict-output">' . $pr_page_content . '</div>';
}

function pr_comment_restrict ( $pr_comment_array ) {
	global $post;
    if ( !is_user_logged_in()  && is_array ( pr_get_opt ( 'pages' ) ) ) :
		$is_restricted = ( in_array($post->ID, pr_get_opt('pages')) || in_array($post->ID, pr_get_opt('posts')) ) && pr_get_opt ( 'method' ) != 'none';
       	if ( $is_restricted || pr_get_opt('method') == 'all' ):
			$pr_comment_array = array();
		endif;
	endif;
	return $pr_comment_array;
}

// Add Actions
add_action( 'send_headers' , 'pr_no_cache_headers' );

// Add Filters
add_filter ( 'the_content' , 'pr_page_restrict' , 100 );
add_filter ( 'the_excerpt' , 'pr_page_restrict' , 50 );
add_filter ( 'comments_array' , 'pr_comment_restrict' , 50 );

add_action( 'wp_login_failed', 'pr_login_failed', 50, 1 );
function pr_login_failed( $username = null )
{
	$referrer = $_SERVER['HTTP_REFERER'];

	if ( !empty( $referrer ) && !strstr( $referrer, 'wp-login.php' ) && !strstr( $referrer, 'wp-admin' ) )
	{
		$params = parse_url( $referrer );		
		$redirect = $params['scheme'] . '://' . $params['host'] . $params['path'];

		parse_str( $params['query'], $query );
		$query['login'] = 'failed';
		
		if ( is_wp_error( $username ) )
			$query['wp-error'] = $username->get_error_message();

		if ( isset( $username->user_login ) )
			$query['pr-user-login'] = $username->user_login;

		if ( count( $query ) > 0 )
		{
			$redirect .= '?' . http_build_query( $query );
		}

		wp_redirect( $redirect, 303 );
		die;
	}
}

if ( sizeof( $_POST ) )
	add_filter( 'authenticate', 'pr_authenticate', 50, 3 );

function pr_authenticate( $error, $user, $pass )
{
	if ( !empty( $user ) )
		$error->user_login = $_POST['log'];

	if ( is_wp_error( $error ) )
		do_action( 'wp_login_failed', $error );

	return $error;
}


<?php

/*
Plugin Name: Possibly Related Classroom Projects
Plugin URI: http://www.socialactions.com/labs/wordpress-donorschoose-plugin
Description: Possibly Related Classroom Projects recommends related classroom fundraising projects from DonorsChoose.org at the bottom of each blog post you publish. Related projects can be deactivated for a particular post by using the tag %NOCP% somewhere within its text. Possibly Related Classroom Projects is powered by Social Actions.
Version: 0.31
Author: Social Actions
Author URI: http://www.socialactions.com
*/

require_once( 'ra_request.php' );
require_once( 'ra_keywords.php' );
require_once( 'ra_body.php' );
require_once( 'ra_cache.php' );
require_once( 'ra_redirect.php' );

register_activation_hook( __FILE__, 'ra_activate' );

add_filter( 'the_content', 'ra_display', 999 );
add_action( 'wp_head',  'ra_get_style' );

?>

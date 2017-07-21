<?php

/* Possibly Related Classroom Projects Wordpress Plugin
 * Copyright 2008  Social Actions  (email : peter@socialactions.com)
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 * 
 * 
 * @author      Social Actions <peter[at]socialactions[dot]com>
 * @author      E. Cooper <smirkingsisyphus[at]gmail[dot]com>
 * @copyright   2008 Social Actions
 * @license     http://www.gnu.org/licenses/gpl-3.0.html
 * @link        http://www.socialactions.com/labs/wordpress-donorschoose-plugin
 * 
 */


/*
 * Function called by WP to display related actions at bottom of post.
 * Only displays if post is single.
 *
 * @params string $content Post body to add related actions output to bottom of
 * @returns string $content returns modified content
 */
function ra_display($content) {
	global $post;

 	if ( is_single() && !raIgnore( $content ) ) {
 		return $content . raGetRelated( $post );
 	} else if ( raIgnore( $content ) ) {
 		$content = raIgnore( $content );
 	}
 	
 	return $content;
}

/*
 * Function called by WP to add link to style sheet in head of document
 *
 * @returns bool 
 */
function ra_get_style() {

 echo '<link rel="stylesheet" href="' . get_bloginfo('wpurl') . '/wp-content/plugins/possibly-related-classroom-projects/ra_style.css" type="text/css" media="screen" />';

}

/*
 * Activates WP plugin
 *
 * @returns bool
 */
function ra_activate() {
	global $wpdb;
	

	$raOptions = array ( 'actionLimit' => 3,
							'keywordLimit' => 3,
							'postTitleWeight' => 1.2,
							'postTagWeight' => 1.4,
							'postContentWeight' => 1,
							'postHotWeight' => 1.3,
							'maxCacheAge' => 12,
							'includeTitle' => true,
							'includeTag' => true,
							'includeContent' => true );
	
	
	foreach ($raOptions as $option => $val) {
		if ( get_option( 'ra_'.$option ) ) {
			update_option( 'ra_'.$option, $val );
		} else {
			add_option( 'ra_'.$option, $val );
		}
	}
	
	if ( raInit() )
	 	return true;
	
	return false;	
}

/*
 * Creates or updates table used for caching results and wordlists
 *
 * @returns bool
 */
function raInit() {
	global $wpdb;
	
	$sql = 	"CREATE TABLE ra_cache (
			 	cache_id INT NOT NULL AUTO_INCREMENT ,
				post_id INT NOT NULL ,
				cached_result LONGTEXT NOT NULL ,
				last_update TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
				UNIQUE KEY cache_id (cache_id))";
	
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
   
   if ( dbDelta($sql) )
   	return true;
   	
   return false;

}


/*
 * Workhorse of plugin. Calls on various external frameworks to recall results 
 * from cache, or define keywords and request results from DC API
 *
 * @params object $wpPost WP post to get related content for
 * @returns string $results raw html of related content for post
 */
function raGetRelated( $wpPost ) {

	if ( !$wpPost )
		return;

	$raCache = new raWordPressCache( $wpPost->ID );			
	

   if ( $raCache->exists() && $raCache->lastUpdate() <= intval( get_option( 'ra_maxCacheAge' ) ) )
   	return $raCache->getCache();


	//Get black and white lists for keyword generation
	$ignore = raGetWordList( 'ignore.txt', -1 );
	$hot =  raGetWordList ( 'hot.txt', -2 );	
	
	//Begin generating keywords	
	$keywords = new raKeywords( $ignore, $hot, intval( get_option( 'ra_postHotWeight' ) ) );
	$areas = raGetIncludedAreas();

	if (!$areas)
		return;
				
	foreach ( $areas as $area => $weight ) {
		$keywords->addKeywords( raGetAreaText( $area ), $weight );
	}
	
	$postKeywords = $keywords->makeList(' OR ', get_option( 'ra_keywordLimit' ) );		
	
	$request = new raRequest( 'http', 'json' );
	$request->setRequestURI( 'http://api.donorschoose.org/common/json_feed.html?' );  
	$request->formQuery( array(	'keywords' => $postKeywords, 'APIKey' => 'vsexve8e3i', 
											'max' => 3 ) );						
		
	if ( !$request->doRequest() ) {	
		return $raCache->getRandomCache();
	}
	
	$results = $request->decodeResponse();	
	
	if ( count( $results->proposals ) < 1 )
		return $raCache->getRandomCache();
		
	$results = raListActions( $results->proposals );
	
	if ( !$results )
		return $raCache->getRandomCache();	
	
	if ( $raCache->exists() ) {
		$raCache->updateCache( $results );
	} else {
		$raCache->addCache( $results );
	}
	
	return $results;
}			

/*
 * Gets content area and weightings for keyword generation. Without admin
 *
 * interface, mostly worthless function.
 * @returns array $areas assoc array of content area and its weighting
 */
function raGetIncludedAreas() {

	$areas = array();	
	
	if ( get_option( 'ra_includeTitle' ) )
		$areas['title'] = get_option( 'ra_postTitleWeight' );
		
	if ( get_option( 'ra_includeTag' ) )
		$areas['tag'] = get_option( 'ra_postTagWeight' );
	
	if ( get_option( 'ra_includeContent' ) )
		$areas['content'] = get_option( 'ra_postContentWeight' );
		
 	return $areas;
}

/*
 * Finds and returns text of a given area, like tags, title, or post body
 *
 * @params string $area a given area's name
 * @returns string text of a given area
 */
function raGetAreaText( $area ) {
	global $post;

	switch ($area) {
		case 'content':
			return $post->post_content;
			break;
		case 'title':
			return $post->post_title;
			break;
		case 'tag':
			$tags = wp_get_post_tags( $post->ID );
			if ( count($tags) < 1 ) 
				return "";
			foreach ($tags as $tag) {
				$postTags .= $tag->name . " ";
			}
			
			return $postTags;
			break;
	}
}

/*
 * Formats JSON-decoded response from API into a HTML <ul></ul>
 * 
 * @params array $results multi-dimensional array of results from API
 * @returns string $html raw html of related content
 */
function raListActions( $results )  {

	if ( !$results )
		 return false;

	$html = "<div class='raWrapper'>";
	$html .= "<span class='raHeader'>Possibly Related Classroom Projects From 
				<a href='http://www.DonorsChoose.org'>DonorsChoose.org</a></span>\n";
			
	foreach ( $results as $result ) {
		list($url) = explode( "&", $result->proposalURL );
		$urlTitle = htmlentities( $result->shortDescription );
		$onclick = "this.href=\"" . raMakeRedirect( $result->proposalURL ) . "\"";	
		
		if ( strlen( $result->title >= 85 ) ) {
			$linkText = substr( $result->title, 0, 82 ) . "...";
		} else {
			$linkText = $result->title;
		}
		
		$actions[] = "<li><a href='$url' title='$urlTitle' onclick='$onclick'>$linkText</a></li>\n";
	}
	
	$html .= "<ul>" . implode("\n", $actions) . "</ul>\n";
	$html .= "<span class='raTagLine'>Powered by <a href='http://www.socialactions.com'>Social Actions</a></span>";
	$html .= "</div>\n"; 
	return $html;
}

function raGetWordList( $listName, $postID ) {
	$wlCache = new raWordPressCache( $postID );
	
	if ( $wlCache->isValidCache( 24 ) ) {
		return $wlCache->getCache();
	} else {
		$wlReq = new raRequest( 'httpfile', 'txt' );
		$wlReq->setRequestURI( 'http://www.socialactions.com/~wp/lists/' );
		$wlReq->formQuery( $listName );		
				
		
		if ( !$wlReq->doRequest() ) {
			if ( $wlCache->exists() )
				return $wlCache->getCache();
			return array();		
		}
		
		$list = $wlReq->decodeResponse();
		
		if ( $wlCache->exists() ) {
			$wlCache->updateCache( $list, true );
		} else {
			$wlCache->addCache( $list );
		}			
		
		return $list;
	}
		 	
}

/*
 * Makes redirect to SA site to track click-throughs for system improvements
 * 
 * @params string $url url to make redirect out of
 * @returns string redirect url
 */
function raMakeRedirect( $url )  {
	if ( !$url )
		return false;
		
	$redirect = new SocialActionsRedirect( "http://www.socialactions.com/~wp/redirect.php" );
	$redirect->setTarget( $url );
	$redirect->addParam( "r", $_SERVER['SERVER_NAME'] );
		
	
	return $redirect->getRedirect();
}

/*
 * Filter function used to not display related content on a given page
 *
 * @params string $content text content of a given blog post
 * @returns string $content parsed text to remove tag if present
 */
function raIgnore( $content ) {
	
	if ( preg_match( "/%NOCP%/i", $content ) ) {
		$content = preg_replace( "/%NOCP%/i", "", $content );
		return $content;
	}
	return false;
}	
 
 			
?>

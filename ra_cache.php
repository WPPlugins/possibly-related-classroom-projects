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
 

class raWordPressCache {

	/*
	 * Constructor for the PRCP Wordpress class. Uses a post ID to initalize itself.
	 *	 
	 * @params integer $postID post ID of a given post
	 * @returns bool
	 */
	function raWordPressCache( $postID=false ) {
		global $wpdb;
					
		if ( $postID )
			$this->postID = $postID;
		
		$this->cacheTable = "ra_cache"; 	//Set cache table name
		$this->db = $wpdb;					//Copies global WP database into a class var	
	}
	
	/*
	 * Checks if a cached result exists for a given postID
	 *	 
	 * @returns bool
	 */
	function exists() {
		if ( !$this->postID )
			return false;
			
		$sql = "SELECT post_id FROM $this->cacheTable WHERE post_id = " . $this->db->escape( $this->postID ) . "";
		
		$results = $this->db->get_results( $sql );
		
		if ( $results[0] )
			return true;
			
		return false;
		
	}
	
	/*
	 * Returns number of hours between now and last cache update
	 *	 
	 * @returns integer $lastUpdate time in hours since last update
	 */
	function lastUpdate() {
		if ( !$this->postID && !$this->exists() )
			return 999;
			
		$sql = "SELECT last_update FROM $this->cacheTable WHERE post_id = " . $this->db->escape( $this->postID ) . "";
		
		$results = $this->db->get_results( $sql );
		
		if ( !$results[0] )
			return 999;	
		
		$updateTime = strtotime( $results[0]->last_update );
		$current = strtotime( date( "Y-m-d H:i:s" ) );		
		$lastUpdate = ( $current - $updateTime ) / 3600;		
		
		return $lastUpdate;		
	
	}
	
	/*
	 * A short-cut function for raWordPressCache::exists() and 
	 * raWordPressCache::lastUpdate(). Checks if cache exists and 
	 * was updated within a given number of hours.
	 *	 
	 * @params integer $maxAge max hours between now and last cache update
	 * @returns bool
	 */
	function isValidCache( $maxAge=24 ) {
		if ( !$this->postID )
			return false;
			
		if ( $this->exists() && ( $this->lastUpdate() < $maxAge ) )
			return true;
			
		return false; 
	}
	
	/*
	 * Finds and returns cached result for a given postID. If no cache can
	 * be found, the function returns false.
	 *	 
	 * @returns mixed $results string of raw html on success, bool false on failure.
	 */
	function getCache() {
		if ( !$this->postID )
			return false;
			
		$sql = "SELECT cached_result FROM $this->cacheTable WHERE post_id = '" . $this->db->escape( $this->postID ) . "'";
			
		$results = $this->db->get_results( $sql );
		
		if ( !$results[0] )
			return false;
			
		return $results[0]->cached_result;
	}
	
	/*
	 * Adds a cached result into database given a postID
	 *	 
	 * @params mixed $results raw html generally in the form of a string.
	 * @returns bool
	 */
	function addCache( $results ) {
		if ( !$this->postID )
			return false;
			
		$sql = "INSERT INTO $this->cacheTable (post_id,cached_result) VALUES (" . $this->db->escape( $this->postID ) . ", '" . $this->db->escape( $results ). "')";
		
		$results = $this->db->query( $sql );
	
		if ( !$results )
			return false;
	 
		return true;
	}
	
	/*
	 * Updates a given cached result in the database.
	 *	 
	 * @params mixed $results raw html generally in the form of a string.
	 * @params bool $forece flag to force last_update timestamp to update to current time
	 * @returns bool
	 */
	function updateCache ( $results, $force=false ) {
		if ( !$this->postID )
			return false;
			
		$sql = "UPDATE $this->cacheTable SET cached_result ='". $this->db->escape( $results ) . "' WHERE post_id = " . $this->db->escape( $this->postID ) . "";		
		
		$results = $this->db->query( $sql );
			
		if ( $force ) {
			$sql = "UPDATE $this->cacheTable SET last_update = NULL WHERE post_id = " . $this->db->escape( $this->postID ) . "";
			$results = $this->db->query( $sql );
		}
		
		if ( !$results ) 
			return false;		
		
		return true;
	}
	
	/*
	 * Function used to call a random cached result from the database. Changed from static
	 * to normal method due to PHP4 lacking such a feature
	 *	 
	 * @returns mixed $results raw html generally in the form of a string.
	 */
	function getRandomCache() {
		global $wpdb;
		
		$sql = "SELECT MAX(cache_id) as max_id FROM ra_cache";
		$results = $wpdb->get_results( $sql );
		$randomID = mt_rand( 1, $results[0]->max_id );		
		
		$sql = "SELECT cached_result FROM ra_cache WHERE cache_id >= " . $randomID . " AND post_id >= 1  ORDER BY cache_id ASC LIMIT 1";
		$results = $wpdb->get_results( $sql );

		if ( !$results )
			return "";		
			
		return $results[0]->cached_result;	
			
	}
}

?>
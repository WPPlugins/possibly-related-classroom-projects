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


class SocialActionsRedirect {

	/*
	 * Constructor for the class. Initalizes with redirect base.
	 *	 
	 * @params string $redirectBase url of the redirect's base
	 * @returns bool
	 */	
	function SocialActionsRedirect( $redirectBase=false ) {
		if ( $redirectBase && preg_match( "/^http:\/\//", $redirectBase ) )
			$this->base = $redirectBase;
	}
	
	/*
	 * Adds to the query string a key and value pairing to the redirect's url
	 *	 
	 * @params string $name the name of param
	 * @params mixed $val value of the param	 
	 * @returns bool
	 */	
	function addParam( $name, $val ) {
		$this->params[urlencode( $name )] = urlencode( $val );
		
		return true;
	}
	
	/*
	 * Sets target url (final url) of the redirect
	 *	 
	 * @params string $target url of redirect target
	 * @returns bool
	 */
	function setTarget( $target ) {
		if ( preg_match( "/^http:\/\//", $target ) )
			$this->target = urlencode( $target );
		
		return true;	
	}
	
	/*
	 * Builds and returns full url to use for the redirect
	 *	 
	 * @returns string $url full url of redirect
	 */
	function getRedirect() {
		if ( !$this->base || !$this->target ) 
			return false;
			
		$url = $this->base . "?t=" . $this->target;
		
		if ( $this->params ) {
			foreach ( $this->params as $name => $val ) {
				$url .= "&" . $name . "=" . $val;
			}
		}
		
		return $url;
	}
	
	/*
 	 * Finds and returns a given param for a redirect
 	 * 
 	 * @params mixed $p name of parameter
 	 * @returns mixed		value of requested parameter
 	 */
	function getParam( $p ) {
		if ( $this->params[$p] )
			return $this->params[$p];
		
		return false;
	}
	
	/*
	 * Redirect web browser to the redirect target using HTML headers
	 *	 
	 * @returns bool
	 */
	function redirectFromHeader() {
		if ( $this->target ) {
			header("location: " . urldecode( $this->target ) );
			return true;
		}
		
		return false;
	}
	
	/*
	 * Redirect web browser using Javascript inserted into the page
	 *	 
	 * @returns bool
	 */
	function redirectFromJS() {
		if ( $this->target ) {
			echo '<script type="text/javascript"> location.href = "' . urldecode( $this->target ) . '"</script>';
			return true;
		}
		
		return false;
	}	
	
	/*
	 * Builds and returns the redirect object from a given URL. Sort of useless since
	 * PHP4 doesn't support static methods
	 *	 
	 * @params string $url url to construct redirect from
	 * @returns object $re redirect object with parameters from supplied url
	 */
 	function newFromURL( $url ) {
		$url = parse_url( $url );
		$base = $url['scheme'] . $url['host'] . $url['path'];
				
		$params = array();
		$target = "";
		
		if ( $url['query'] ) {
			$q = explode( "&", $url['query'] );		 	
			foreach ( $q as $param ) {
				list($name, $val) = explode( "=", $param);
				if ( $name == "t" ) {
					$target = urldecode( $val );
				} else {					
					$params[$name] = $val;
				}			
			}
		}		
		
		$re = new SocialActionsRedirect( $base );
		
		if ( $target )
			$re->setTarget( $target );
			
		if ( $params ) {
			foreach ( $params as $name => $val ) {
				$re->addParam( urldecode( $name ), urldecode( $val ) );
			}
		}
		
		return $re; 
		 
	} 

}

?>
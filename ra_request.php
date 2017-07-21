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

class raRequest {

	function raRequest( $method='', $decode='', $URI='' ) {
		if ( $method )
			$this->method = strtoupper( $method );

		if ( $decode )	
			$this->decode = strtoupper( $decode );
			
		if ( $URI )
			$this->requestURI = $URI;
	}
	
	function setRequestURI( $URI ) {
		$this->requestURI = $URI;
	}
	
	function formQuery($params) {
		if ( !$params || !$this->method || ( $this->method == 'HTTP' && !$this->requestURI) ) 
			return false;
		
		$queryMethod = 'formQuery' . $this->method;		
		$this->requestParams = $params;
		
		$this->requestText = call_user_func( array( $this, $queryMethod ) );
	
		return true;
	}
	
	function formQueryHTTP() {
		if ( !$this->requestParams )
			return false;
			
		$params = $this->requestParams;
		
		foreach ( $params as $param => $val ) {
			$urlParams[] = $param . "=" . urlencode( $val );
		}
		
		$this->requestText = $this->requestURI . implode( '&', $urlParams );
		
		return $this->requestText;
	}
	
	function formQueryHTTPFILE() {
		if ( !$this->requestParams )
			return false;
			
		$params = $this->requestParams;
			
		$this->requestText = $this->requestURI . $params;		
			
		return $this->requestText;
	}
	
	function getQueryText() {
		if ( $this->requestText )
			return $this->requestText;
		
		return false;
		
	}
	
	function doRequest() { 
		if ( !$this->requestURI || !$this->method || !$this->requestText )
			return false;
			
		$requestMethod = 'doRequest' . strtoupper( $this->method );
		
		$this->requestResponseRaw = call_user_func( array( $this, $requestMethod ) );
		
		if ( $this->requestResponseRaw )
			return true;
			
		return false;
	} 
	
	function doRequestHTTP() {
		$uri = $this->requestText;
		
		$ch = curl_init();  
		
		curl_setopt($ch, CURLOPT_URL,$uri);
		curl_setopt($ch, CURLOPT_FAILONERROR, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 4);
		curl_setopt($ch, CURLOPT_TIMEOUT, 8);

		$result = curl_exec($ch); 		
		curl_close($ch); 
		
		if ( !$result )  
			return false;
			
		$this->requestResponseRaw = $result;
		
		return $this->requestResponseRaw;
	}
	
	function doRequestHTTPFILE() {
		$uri = $this->requestText;
		
		$ch = curl_init();  
		
		curl_setopt($ch, CURLOPT_URL,$uri);
		curl_setopt($ch, CURLOPT_FAILONERROR, 1);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPGET, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
		curl_setopt($ch, CURLOPT_TIMEOUT, 7);

		$result = curl_exec($ch); 		
		curl_close($ch); 		
		
		if ( !$result )  
			return false;
			
		$this->requestResponseRaw = $result;
		
		return $this->requestResponseRaw;
	}	
	
	function decodeResponse( ) {
		if ( !$this->decode || !$this->requestResponseRaw )
		 	return false;
		 	
		$decodeMethod = 'decodeResponse' . strtoupper ( $this->decode );
		
		$this->requestResponse = call_user_func( array( $this, $decodeMethod ) );
		
		return $this->requestResponse;
	}
	
	function decodeResponseJSON() {
		if ( !$this->requestResponseRaw )
			return false;
		
		if ( !class_exists( 'Services_JSON' ) )	
			require_once("JSON.php");
		
		$json = new Services_JSON();
	 	$decoded = $json->decode($this->requestResponseRaw);

	 	return $decoded;
	}
	
	function decodeResponseTXT() {
		if ( !$this->requestResponseRaw )
			return false;
			
		return $this->requestResponseRaw;

	}
}	
?>
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

class raKeywords {

	/* Mapping of keywords and their current aggregate weight */
	var $keywords = array();

	/*
	 * Constructor for keywords class. Allows optional setting of black and white lists
	 *	 
	 * @params mixed $ignoreList array or string of words to add to ignore list
	 * @params mixed $hotList array or string of words to add to hot list
	 * @params float $hotListWeight value of weighting to apply to hot list words
	 * @returns bool
	 */
	function raKeywords( $ignoreList='', $hotList='', $hotListWeight=1 ) {
		if ( $ignoreList ) 
			$this->ignore = $this->makeKeywordArray( $ignoreList );
	
		if ( $hotList ) 
			$this->hot = $this->makeKeywordArray( $hotList );
		
		if ( $hotListWeight ) 
			$this->hotWeight = $this->makeValidWeight( $hotListWeight );
			
	}
	
	/*
	 * Contribute keywords to current mapping. Takes a string of text, and parses
	 * it from there. 
	 *	 
	 * @params string $text string to extract keywords from
	 * @params float $weight optional weighting to apply to this particular group of keywords
	 * @returns bool
	 */
	function addKeywords( $text, $weight=1 ) {
		$keywords = preg_split( '/\s*[\s+\.|\?|,|(|)|\-+|\'|\"|=|;|&#0215;|\$|\/|:|{|}]\s*/i', strip_tags( strtolower( $text ) ) );	
				
		if ($this->ignore) {
			$keywords = array_diff( $keywords, $this->ignore );
		}
		
		$keywords = array_count_values( $keywords );
		
		foreach ( $keywords as $keyword => $freq ) {				
			
			if ( $this->hot && in_array( $keyword, $this->hot ) )
				$freq *= $this->hotWeight;
				
		   if ( isset( $this->keywords[$keyword] ) ) 
		   	$freq += $this->keywords[$keyword];
		
			$keywords[$keyword] = $freq * $weight;				   	   
		   
		   if ( trim($keyword) == "" )
				unset($keywords[$keyword]);
		}
		
		$this->keywords = array_merge( $this->keywords, $keywords );
		
		return true;
	}
	
	/*
	 * Using a list deliminater, returns a list-based string of top keywords.
	 *	 
	 * @params string $delim deliminater to separate keywords with
	 * @params int $limit optional limit to apply to returned keywords
	 * @returns string $listKeywords list of keywords separated by $delim and limited by $limit
	 */
	function makeList ( $delim=", ", $limit=false ) {
		$keywords = $this->keywords;
		arsort( $keywords, SORT_NUMERIC );
		
		$i = 0;
		foreach ( $keywords as $word => $freq ) {
			$selectedKeywords[] = $word;
			$i++;
			
			if ($limit) {
				if ( $i == $limit )
					break;
			}
		} 
			
		$listKeywords = implode( $delim, $selectedKeywords );
		
		return $listKeywords;
	}
	
	/*
	 * If given word list isn't an array, it makes it an array. It assumes ", " as the
	 * deliminater.
	 *	 
	 * @params mixed $list keywords in array or string form
	 * @returns array $list given list returned in an array
	 */
	function makeKeyWordArray( $list ) {
		if ( is_array( $list ) )
			return $list;
			
		$list = explode( ', ', strtolower( $list ) );
		
		return $list;
	}
	
	/*
	 * Make sure given weighting is within specs: 0 > weight <= 5
	 *
	 * @params float $weight a supplied weighting to apply to keywords
	 * @returns float $weight a valid weight derived from supplied weight
	 */
	function makeValidWeight( $weight ) {
		if ( !is_numeric( $weight ) ) 
			return 1;
			
		if ( $weight < 0 )
			$weight *= -1;
			
		if ( $weight > 5 )
			$weight = 5;
			
		return $weight;
	
	} 
}		 

?>
<?php

/// Caching implementation that supports elements for tx_newspaper
/** As long as a single, cached FE plugin (tx_newspaper_pi1) manages all 
 *  \c newspaper content, the Typo3 caching mechanism can not know if content on
 *  a \c newspaper page has changed. That would necessarily lead to all
 *  \c newspaper pages being delivered uncached. To mitigate this, two
 *  complementary approaches are implemented by this class:
 *   - Deleting the page cache for all Typo3 pages affected by a \c newspaper 
 *     operation.
 *   - Caching the content managed by newspaper to speed up delivery on uncached
 * 	   Typo3 pages. 
 *  \todo Preview uncached
 *  \todo Saving any newspaper-related item must invalidate typo3 page cache
 */
class tx_newspaper_Cache {
	
	/// \c true if a valid element can be found in the cache.
	function isPresent($table, $uid) {
		return false;
	}
	
	/// Recovers a rendered element from the cache.
	function read($table, $uid) { 
		
	}

	/// Writes the fully rendered HTML for an element to the cache.
	function write($table, $uid, $content) { 
		// blah blah blah
	}
	
	/// Clears the specified element from the cache.
	function invalidate($table, $uid) {
		if($table == 'pages') {
			// ... clear typo3 cache for page
		} else {
			// 
		} 
	}
	
	static function getInstance() {
		if (!self::$instance) {
			self::$instance = new tx_newspaper_Cache();
			// oder dumb_cache oder semiclever_cache 
		}
		return self::$instance;		 
	}
	
	/// Protected constructor, tx_newspaper_Cache cannot be created freely
	protected function __construct() { }
	
	/// Cloning tx_newspaper_Cache is prohibited by making __clone private
	private function __clone() { }
	
	private static $instance = null;
}

?>
<?php
/*
 * Created on Aug 12, 2008
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

class Registry {
	
	///	stuff to make this class a singleton
	
	private function __construct() {
		$this->setDisplayMode('...');
	}
	
	static function getInstance() {
		return $blah;
	}

	private function setDisplayMode() { 
		// ...
	}

	function getDisplayMode() {
		// ...	
	}
	
	/** @param string $lowLevelFields 
	 *  @return array all requested fields and their values					 */	
	function getFields($lowLevelFields, $key, $source) {

		/// this should be more intelligent. $lowLevelFields is plural, so we must
		///	check every single field and read only those from DB which are not yet in cache.
		if (is_already_in_cache($lowLevelFields, $key, $source)) 
			return get_from_cache($lowLevelFields, $key, $source);
			
		if(!is_a('Source', $source)) throw '$source must be a Source object!';
		
		///	TODO: get only needed fields here
		$result = $source->getFields($lowLevelFields, $key);
		
		/// TODO: ...and write only those to the cache
		write_to_cache($result, $lowLevelFields, $key, $source);
		
		return $result;
	}
	
	/** @param string $verknuepfungstabelle singular! don't need to check if parts are already in cache.
	 * 
	 */
	function getArray($verknuepfungstabelle, $key, $source) {
		
		if (is_already_in_cache($verknuepfungstabelle, $key, $source)) 
			return get_from_cache($verknuepfungstabelle, $key, $source);
			
		if(!is_a('Source', $source)) throw '$source must be a Source object!';
		
		$result = $source->getArray($verknuepfungstabelle, $key);
		
		write_to_cache($result, $verknuepfungstabelle, $key, $source);
		
		return $result;
	}

	/** @param array $extraDefinition Form (e.g.): array (
				'ExtraType' => 'LinkBox',
				'WantedFields' => 'Title, NumLinks'
			)
			**/
	function getExtras($extraDefinition, $key, $source) {
		return new $extraDefinition['ExtraType']($key, $source);
	}
}

?>

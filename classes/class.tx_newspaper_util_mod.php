<?php
/**
 *  \file class.tx_newspaper_util_mod.php
 *
 *  \author Oliver Schröder <newspaper@schroederbros.de>
 *  \date Mar 21, 2009
 */

/// utility functions for backend modules
class tx_newspaper_UtilMod {
	
	
	/// converts $_POST data to an url encoded query string
	/** \param array $overwrite entries is this array overwrite the current $_POST item
	  * \return string querystring (some entries might have been overwritten)
	  */ 
	static public function convertPost2Querystring(array $overwrite=array()) {
		$item = array();
		foreach (t3lib_div::_POST() as $key => $value) {
			$item[$key] = $key . '=' . urlencode($value);
  		}
  		/// overwrite 
  		foreach($overwrite as $key => $value) {
  			$item[$key] = $key . '=' . urlencode($value);
  		}
  		$query_string = implode("&", $item);
#t3lib_div::devlog('querystring', 'newspaper', 0, $query_string);
 		return $query_string;
	}
	
	
	/// calculates a timestamp in the past based on last midnight (and the given type)
	/** \param $type can be 'today' (default), 'no_limit' or 'day_n' [n = positiv integer]
	 *  \return timestamp (last midnight - n days (=n*86400) or 0 for no limit)
	 */
	static public function calculateTimestamp($type) {
		$type = strtolower($type);
		
		$tmp = getdate();
		$last_midnight = mktime(0, 0, 0, $tmp['mon'], $tmp['mday'], $tmp['year']);
		
		if ($type == 'today')
			return $last_midnight;
		if ($type == 'no_limit')
			return 0;
		if (substr($type, 0, 4) == 'day_')
			return $last_midnight - intval(substr($type, 4)) * 86400; // 86400 = 1 day in seconds			
		return $last_midnight; // default: today only
	}
	
	
	
	
}

?>
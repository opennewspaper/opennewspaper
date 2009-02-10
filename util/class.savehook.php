<?php

class user_savehook_newspaper {


  function processDatamap_preProcessFieldArray($incomingFieldArray, $table, $id, $that) {
t3lib_div::devlog('sh fields', 'newspaper', 0, $incomingFieldArray);
t3lib_div::devlog('sh table', 'newspaper', 0, $table);
t3lib_div::devlog('sh id', 'newspaper', 0, $id);

	/// check if a newspaper record is saved and make sure it's stored in the appropriate sysfolder
	if (class_exists($table)) { ///<newspaper specification: table name = class name
		$np_obj = new $table();
		if (in_array("tx_newspaper_InSysFolder", class_implements($np_obj))) { 
			/// tx_newspaper_InSysFolder is implemented, so record is to be stored in a special sysfolder
			$pid = $np_obj->getModuleName();
t3lib_div::devlog('sh pid', 'newspaper', 0, $pid);
		}
	}



  }

}

?>

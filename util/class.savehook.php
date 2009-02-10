<?php

class user_savehook_newspaper {


  function processDatamap_preProcessFieldArray($incomingFieldArray, $table, $id, $that) {
t3lib_div::devlog('sh fields', 'newspaper', 0, $incomingFieldArray);
t3lib_div::devlog('sh table', 'newspaper', 0, $table);
t3lib_div::devlog('sh id', 'newspaper', 0, $id);

t3lib_div::devlog('classes', 'newspaper', 0, get_declared_classes());


/// \todo: remove by-pass after article class and table have the same name
$class_bypass = ($table == 'tx_newspaper_article')? 'tx_newspaper_ArticleImpl' : $table;



	/// check if a newspaper record is saved and make sure it's stored in the appropriate sysfolder
	if (class_exists($class_bypass)) { ///<newspaper specification: table name = class name
		$np_obj = new $class_bypass();
		if (in_array("tx_newspaper_InSysFolder", class_implements($np_obj))) { 
			/// tx_newspaper_InSysFolder is implemented, so record is to be stored in a special sysfolder
			$sf = new tx_newspaper_Sysfolder();
			$pid = $sf->getPid($np_obj);
t3lib_div::devlog('sh pid', 'newspaper', 0, $pid);
		}
	}



  }

}

?>

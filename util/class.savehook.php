<?php

class user_savehook_newspaper {


  function processDatamap_preProcessFieldArray($incomingFieldArray, $table, $id, $that) {
t3lib_div::devlog('sh fields', 'newspaper', 0, $incomingFieldArray);
t3lib_div::devlog('sh table', 'newspaper', 0, $table);
t3lib_div::devlog('sh id', 'newspaper', 0, $id);

  }

}

?>

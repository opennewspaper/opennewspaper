<?php
/**
 * Author: Lene Preuss <lene.preuss@gmail.com>
 * Date:   2/9/12
 * Time:   6:22 PM
 */


$DIR = dirname(__FILE__);

/*
$dirs = explode('/', $DIR);
$mod_path = '';

$i = 0;
while ($dirs[$i] != 'typo3conf') {
    unset($dirs[$i]);
    $i++;
}
print_r($dirs);

$mod_path = implode('/', $dirs) . '/typo3';

define('TYPO3_MOD_PATH', $mod_path . '/');

require_once(dirname(__FILE__) . '/../../../../typo3_src/typo3/init.php');
*/

define('PATH_typo3conf', $DIR . '/../../../');

require_once(dirname(__FILE__) . '/../classes/private/class.tx_newspaper_file.php');
require_once(dirname(__FILE__) . '/../tests/class.AsynchronousTask_testcase.php');

function getObjectFromObjectFile($filename) {
    $serialized_file = new tx_newspaper_File($filename);
    $serialized_object = $serialized_file->read();
    return unserialize($serialized_object);
}

$object = getObjectFromObjectFile($argv[1]);

echo "class: " . get_class($object) . ", method: " . $argv[2] . ", args: " . $argv[3];


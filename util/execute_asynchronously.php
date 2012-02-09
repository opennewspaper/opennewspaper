<?php
/**
 * Author: Lene Preuss <lene.preuss@gmail.com>
 * Date:   2/9/12
 * Time:   6:22 PM
 */

require_once(dirname(__FILE__) . '/../classes/private/class.tx_newspaper_file.php');

function getObjectFromObjectFile($filename) {
    $serialized_file = new tx_newspaper_File($filename);
    $serialized_object = $serialized_file->read();
    return unserialize($serialized_object);
}

$object = getObjectFromObjectFile($argv[1]);

echo "class: " . get_class($object) . ", method: " . $argv[2] . ", args: " . $argv[3];


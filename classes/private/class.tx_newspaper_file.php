<?php
/**
 * Created by JetBrains PhpStorm.
 * User: lene
 * Date: 12/14/11
 * Time: 1:40 PM
 * To change this template use File | Settings | File Templates.
 */
 
class tx_newspaper_File {

    public function __construct($filename, $mode = 'a+') {
        $this->handle = @fopen($filename, $mode);
    }

    public function __destruct() {
        @fclose($this->handle);
    }

    public function write($string) {
        @fwrite($this->handle, $string);
    }

    private static function checkModeValid($mode) {
        if (strlen($mode) > 2 ||
            array_search($mode[0], array('r', 'w', 'a', 'x', 'c') === false ||
            (strlen($mode) == 2) && $mode[1] != '+')) {
            throw new tx_newspaper_IllegalUsageException(
                'Unsupported mode for opening a file: ' . $mode
            );
        }
    }

    private $handle = null;
}
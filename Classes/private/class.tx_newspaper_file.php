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
        self::checkModeValid($mode);
        $this->filename = $filename;
        $this->handle = @fopen($filename, $mode);
    }

    public function __destruct() {
        @fclose($this->handle);
    }

    public function write($string) {
        @fwrite($this->handle, $string);
    }

    public function read($length = -1) {
        if ($length < 0) {
            $length = filesize($this->getName());
        }
        return @fread($this->handle, $length);
    }

    public function getName() {
        return $this->filename;
    }

    public static function unlink($filename) {
        if (file_exists($filename)) {
            unlink($filename);
        }
    }

    ////////////////////////////////////////////////////////////////////////////

    private static function checkModeValid($mode) {
        if (strlen($mode) > 2 ||
            (array_search($mode[0], array('r', 'w', 'a', 'x', 'c')) === false) ||
            (strlen($mode) == 2 && $mode[1] != '+')) {
            throw new tx_newspaper_IllegalUsageException(
                'Unsupported mode for opening a file: ' . $mode
            );
        }
    }

    private $filename = '';
    private $handle = null;

}

class tx_newspaper_Debug {

    public static function w($text, $filename = '/tmp/debug.out') {
        $file = new tx_newspaper_File($filename);
        $file->write("$text\n");
    }

    public static function backtrace($length = 5) {
        $lines = array_slice(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS), 0, $length+1);
        for ($i = 1; $i <= $length; $i++) {
            self::w(
                $lines[$i]['class'] . "::" . $lines[$i]['function'] . "() - " . self::file($lines[$i-1]['file']) . " line " . $lines[$i-1]['line']
            );
        }
    }

    private static function file($file, $num_path_segments = 3) {
        return implode('/', array_slice(explode('/', $file), -$num_path_segments-1));
    }

}
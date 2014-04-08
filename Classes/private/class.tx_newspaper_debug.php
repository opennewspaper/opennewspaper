<?php
/**
 * Created by PhpStorm.
 * User: lene
 * Date: 4/8/14
 * Time: 1:33 PM
 */


class tx_newspaper_Debug {

    public static function w($text, $filename = '/tmp/debug.out') {
        self::getStream($filename)->write("$text");
    }

    public static function backtrace($length = 5, $filename = '/tmp/debug.out') {
        $lines = array_slice(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS), 0, $length+1);
        for ($i = 1; $i <= $length; $i++) {
            self::w(
                $lines[$i]['class'] . "::" . $lines[$i]['function'] . "() - " .
                    self::file($lines[$i-1]['file']) . " line " . $lines[$i-1]['line'],
                $filename
            );
        }
    }

    ////////////////////////////////////////////////////////////////////////////

    private static function getStream($filename) {
        if (!isset(self::$registry[$filename])) {
            self::$registry[$filename] = new tx_newspaper_Debug($filename);
        }
        return self::$registry[$filename];
    }

    private function __construct($filename) {
        $this->filename = $filename;
    }

    private function __destruct() {
        $file = new tx_newspaper_File($this->filename);
        foreach ($this->lines as $line) $file->write("$line\n");
    }

    private function write($text) {
        $this->lines[] = $text;
    }

    private static function file($file, $num_path_segments = 3) {
        return implode('/', array_slice(explode('/', $file), -$num_path_segments-1));
    }

    private static $registry = array();

    private $filename = '';
    private $lines = array();

}

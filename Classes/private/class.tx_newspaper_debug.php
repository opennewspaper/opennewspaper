<?php
/**
 * Created by PhpStorm.
 * User: lene
 * Date: 4/8/14
 * Time: 1:33 PM
 */


/**
 *  A class to manage writing debug messages to a log file.
 */
class tx_newspaper_Debug {

    /**
     *  The name of the log file, if none is supplied explicitly
     */
    const default_file = '/tmp/debug.out';

    /**
     *  Write text to the debugging log
     *
     *  @param string $text The text written to the debugging log
     *  @param string $filename The file name for the debugging log
     */
    public static function w($text, $filename = self::default_file) {
        self::getStream($filename)->write("$text");
    }

    /**
     *  Write a stack backtrace to the debugging log
     *
     *  @param int $length How many lines of backtrace to log
     *  @param string $filename The file name for the debugging log
     */
    public static function backtrace($length = 5, $filename = self::default_file) {
        $lines = array_slice(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS), 0, $length+1);
        for ($i = 1; $i <= $length; $i++) {
            self::w(
                self::functionString($lines[$i]) . " - " . self::locationString($lines[$i-1]),
                $filename
            );
        }
    }

    ////////////////////////////////////////////////////////////////////////////

    /**
     * @param string $filename
     * @return tx_newspaper_Debug
     */
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
        $file->write("\n");
    }

    private function write($text) {
        $this->lines[] = $text;
    }

    private static function functionString($backtrace_entry) {
        return  $lines[$i]['class'] . "::" . $lines[$i]['function'] . "()";
    }

    private static function locationString($backtrace_entry) {
        return self::file($backtrace_entry['file']) . " line " . $backtrace_entry['line'];
    }

    private static function file($file, $num_path_segments = 3) {
        return implode('/', array_slice(explode('/', $file), -$num_path_segments-1));
    }

    private static $registry = array();

    private $filename = '';
    private $lines = array();

}

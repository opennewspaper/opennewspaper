<?php
/**
 * Created by JetBrains PhpStorm.
 * User: lene
 * Date: 12/13/11
 * Time: 4:35 PM
 * To change this template use File | Settings | File Templates.
 */

require_once('class.tx_newspaper_timinglogger.php');
require_once('class.tx_newspaper_timinginfo.php');

class tx_newspaper_ExecutionTimer {

    public static function setLogger(tx_newspaper_TimingLogger $logger) {
        self::$logger = $logger;
    }

    public function __construct($message = '') {
        self::$recursion_level++;

        $this->message = $message;
        $this->execution_start_time = microtime(true);
    }

    public function __destruct() {
        self::writeToLogger(
            $this->message,
            new tx_newspaper_TimingInfo($this->execution_start_time)
        );

        self::$recursion_level--;
    }

    public static function start() {
        self::$execution_time_stack[] = microtime(true);
    }

    public static function logExecutionTime($message = '') {
        self::writeToLogger($message, self::getTimingInfo());
    }

    public static function getExecutionTime() {
        return self::getTimingInfo()->getExecutionTime();
    }

    /** @return tx_newspaper_TimingInfo */
    public static function getTimingInfo() {
        return new tx_newspaper_TimingInfo(array_pop(self::$execution_time_stack));
    }

    ////////////////////////////////////////////////////////////////////////////

    private static function logExecutionTimes() {
        return intval(tx_newspaper::getTSConfigVar('logExecutionTimes'));
    }

    private static function writeToLogger($message, tx_newspaper_TimingInfo $timing_info) {
        if (!self::logExecutionTimes()) return;

        if (!self::$logger) {
            self::setLogger(new tx_newspaper_Devlogger());
        }

        self::$logger->log($message, $timing_info);
    }

    private $message = '';
    
    private $execution_start_time = 0;

    private static $execution_time_stack = array();

    /** @var tx_newspaper_TimingLogger */
    private static $logger = null;

    private static $recursion_level = 0;

}

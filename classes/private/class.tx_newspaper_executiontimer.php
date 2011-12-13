<?php
/**
 * Created by JetBrains PhpStorm.
 * User: lene
 * Date: 12/13/11
 * Time: 4:35 PM
 * To change this template use File | Settings | File Templates.
 */

require_once('class.tx_newspaper_logger.php');

class tx_newspaper_ExecutionTimer {

    /// Whether to measure the execution times of functions
    const log_execution_times_default = true;

    public static function setLogger(tx_newspaper_Logger $logger) {
        self::$logger = $logger;
    }

    public static function start() {
        self::$execution_start_time = microtime(true);
        self::$execution_time_stack[] = self::$execution_start_time;
    }

    public static function logExecutionTime($message = '') {

        $timing_info = self::getTimingInfo();
        $timing_info['message'] = $message;

        if (self::logExecutionTimes()) {
            self::writeToLogger($timing_info);
        }
    }

    public static function getExecutionTime() {
        $timing_info = self::getTimingInfo();
        return $timing_info['execution time'];
    }

    public static function getTimingInfo() {
        $start_time = array_pop(self::$execution_time_stack);
        $execution_time = microtime(true)-$start_time;
        $execution_time_ms = 1000*$execution_time;

        return array(
            'execution time' => $execution_time_ms . ' ms',
            'object' => self::getTimedObject(),
        );
    }

    ////////////////////////////////////////////////////////////////////////////

    private static function logExecutionTimes() {
        if (tx_newspaper::getTSConfigVar('logExecutionTimes')) {
            return intval(tx_newspaper::getTSConfigVar('logExecutionTimes'));
        }
        return self::log_execution_times_default;
    }

    private static function getTimedObject() {
        $backtrace = array_slice(debug_backtrace(), 0, 5);
        foreach($backtrace as $function) {
            if ($function['class'] == 'tx_newspaper_ExecutionTimer') continue;
            return $function['object'];
        }
    }

    private static function writeToLogger($timing_info) {
        if (!self::$logger) {
            self::$logger = new tx_newspaper_Devlogger();
        }
        self::$logger->log('logExecutionTime', $timing_info);
    }

    private static $execution_start_time = 0;

    private static $execution_time_stack = array();

    /** @var tx_newspaper_Logger */
    private static $logger = null;

}

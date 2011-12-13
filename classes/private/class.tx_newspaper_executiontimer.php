<?php
/**
 * Created by JetBrains PhpStorm.
 * User: lene
 * Date: 12/13/11
 * Time: 4:35 PM
 * To change this template use File | Settings | File Templates.
 */
 
class tx_newspaper_ExecutionTimer {

    /// Whether to measure the execution times of functions
    const log_execution_times = true;

    public static function start() {
        self::$execution_time_stack[] = microtime(true);

        self::$execution_start_time = microtime(true);
    }

    public static function logExecutionTime($message = '') {

        $timing_info = self::getTimingInfo();
        $timing_info['message'] = $message;

        if (self::logExecutionTimes()) tx_newspaper::devlog('logExecutionTime', $timing_info);
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
        return self::log_execution_times;
    }

    private static function getTimedObject() {
        $backtrace = array_slice(debug_backtrace(), 0, 5);
        foreach($backtrace as $function) {
            if ($function['class'] == 'tx_newspaper_ExecutionTimer') continue;
            return $function['object'];
        }
    }

    private static $execution_start_time = 0;

    private static $execution_time_stack = array();


}

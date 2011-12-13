<?php
/**
 * Created by JetBrains PhpStorm.
 * User: lene
 * Date: 12/13/11
 * Time: 6:41 PM
 * To change this template use File | Settings | File Templates.
 */

class tx_newspaper_TimingInfo {

    public function __construct($start_time) {
        $execution_time_microseconds = microtime(true)-$start_time;
        $this->execution_time_ms = 1000*$execution_time_microseconds;
        $this->timed_object = self::initializeTimedObject();
    }

    public function getExecutionTime() { return $this->execution_time_ms; }
    public function getTimedObject() { return $this->timed_object; }

    ////////////////////////////////////////////////////////////////////////////

    private $execution_time_ms;
    private  $timed_object;

    private static function initializeTimedObject() {
        $backtrace = array_slice(debug_backtrace(), 0, 5);
        foreach($backtrace as $function) {
            if ($function['class'] == 'tx_newspaper_TimingInfo') continue;
            if ($function['class'] == 'tx_newspaper_ExecutionTimer') continue;
            return $function['object'];
        }
    }

}


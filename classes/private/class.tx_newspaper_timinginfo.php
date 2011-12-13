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
        $this->initializeTimedObject();
    }

    public function __toString() {
        return $this->getTimedClass() . '::' .$this->getTimedFunction() . '(): ' . $this->getExecutionTime() . ' ms';
    }

    public function getExecutionTime() { return $this->execution_time_ms; }
    public function getTimedObject() { return $this->timed_object; }
    public function getTimedClass() { return $this->timed_class; }
    public function getTimedFunction() { return $this->timed_function; }

    ////////////////////////////////////////////////////////////////////////////

    private function initializeTimedObject() {
        $backtrace = array_slice(debug_backtrace(), 0, 5);
        foreach($backtrace as $function) {
            if (self::functionIsPartOfTimingFramework($function)) continue;
            $this->initializeFromBacktrace($function);
            return;
        }
    }

    private function initializeFromBacktrace(array $function) {
        $this->timed_object = $function['object'];
        $this->timed_class = $function['class'];
        $this->timed_function = $function['function'];
    }

    private static function functionIsPartOfTimingFramework(array $function) {
        return ($function['class'] == 'tx_newspaper_TimingInfo' ||
                $function['class'] == 'tx_newspaper_ExecutionTimer');
    }

    private $execution_time_ms;

    private $timed_object = null;
    private $timed_class = '';
    private $timed_function = '';

}


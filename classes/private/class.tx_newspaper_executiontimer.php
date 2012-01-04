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

/**
 *  Class that can be inserted in any function to measure its execution time and
 *  write it to a log.
 *
 *  Also contains methods to measure the execution time between two arbitrary
 *  points in any code.
 *
 *  Usage example:
 *  \code
 *  function measured() {
 *
 *    $timer = tx_newspaper_ExecutionTimer::create();
 *
 *    // code whose execution time is measured
 *
 *    // ... aaand we're done. The destructor ensures the execution time of
 *    // measured() is logged.
 *
 *  }
 *
 *  function partially_measured() {
 *
 *    // some code we're not interested in
 *
 *    tx_newspaper_ExecutionTimer::start();
 *
 *    // this code's execution time is measured
 *
 *    tx_newspaper_ExecutionTimer::logExecutionTime();
 *
 *    // more uninteresting code, which is not timed.
 *  }
 *  \endcode
 */
class tx_newspaper_ExecutionTimer {

    /**
     *  Sets the object that does the logging of timing information.
     *
     *  If setLogger() is not called, a tx_newspaper_Devlogger is used as
     *  default, which writes the timing information to the devlog.
     *
     *  @param tx_newspaper_TimingLogger $logger
     */
    public static function setLogger(tx_newspaper_TimingLogger $logger) {
        self::$logger = $logger;
    }

    /**
     *  Factory method to create a new tx_newspaper_ExecutionTimer.
     *
     *  @param string $message Optional message to be logged; otherwise the
     *      class and function of the caller is logged.
     *  @return tx_newspaper_ExecutionTimer
     */
    public static function create($message = '') {
        return new tx_newspaper_ExecutionTimer($message);
    }

    /**
     *  This destructor ensures that the execution time of a function will be
     *  logged when a tx_newspaper_ExecutionTimer object goes out of scope; in
     *  other words, when the code flow leaves the function it was created in.
     */
    public function __destruct() {
        self::$recursion_level--;

        self::writeToLogger(
            $this->message,
            new tx_newspaper_TimingInfo($this->execution_start_time)
        );
    }

    /**
     *  Start the execution timer without generating an object. Must be followed
     *  by a call to logExecutionTime().
     */
    public static function start() {
        self::$execution_time_stack[] = microtime(true);
        self::$recursion_level++;
    }

    /**
     *  Writes the time that has elapsed since the last call to start() to the
     *  log. Must be preceded by a call to start().
     *
     *  @param string $message Optional message to be logged; otherwise the
     *      class and function of the caller is logged.
     */
    public static function logExecutionTime($message = '') {
        self::$recursion_level--;
        self::writeToLogger($message, self::getTimingInfo());
    }

    /**
     *  Access to the time that has elapsed since the last call to start().
     *
     *  @return int The time that has elapsed since the last call to start()
     */
    public static function getExecutionTime() {
        return self::getTimingInfo()->getExecutionTime();
    }

    /**
     *  Access to the time that has elapsed since the last call to start().
     *
     *  @return tx_newspaper_TimingInfo
     */
    public static function getTimingInfo() {
        return new tx_newspaper_TimingInfo(array_pop(self::$execution_time_stack));
    }

    ////////////////////////////////////////////////////////////////////////////

    /**
     *  Constructor is private; objects can only be created via create().
     */
    private function __construct($message = '') {
        $this->message = $message;
        $this->execution_start_time = microtime(true);

        self::$recursion_level++;
    }

    private static function logExecutionTimes() {
        $tsconfig = tx_newspaper::getTSConfig();
        return intval($tsconfig['newspaper.']['logExecutionTimes']);
    }

    private static function writeToLogger($message, tx_newspaper_TimingInfo $timing_info) {

        if (!self::logExecutionTimes()) return;

        self::ensureLoggerIsPresent();

        self::$logger->log($message, $timing_info, self::$recursion_level);
    }

    private static function ensureLoggerIsPresent() {
        if (!self::$logger) {
            self::setLogger(new tx_newspaper_Devlogger());
        }
    }


    private $message = '';
    
    private $execution_start_time = 0;

    /** @var tx_newspaper_TimingInfo[] */
    private static $execution_time_stack = array();

    /** @var tx_newspaper_TimingLogger */
    private static $logger = null;

    private static $recursion_level = 0;

}

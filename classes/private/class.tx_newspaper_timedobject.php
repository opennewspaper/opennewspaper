<?php
/**
 * Author: Lene Preuss <lene.preuss@gmail.com>
 * Date: 12/15/11
 * Time: 4:13 PM
 */

require_once('class.tx_newspaper_executiontimer.php');

/**
 *  Class to time every method of another class, using a tx_newspaper_ExecutionTimer.
 *  The result of the time measurements is logged according to the tx_newspaper_TimingLogger
 *  set with tx_newspaper_ExecutionTimer::setLogger() - by default it is written
 *  to the devlog.
 *
 *  Usage: instead of
 *  \code
 *  $object = new MyClass();
 *  \endcode
 *  use
 *  \code
 *  $object = new tx_newspaper_TimedObject('MyClass');
 *  \endcode
 *  If you use dependency injection, you can simply change the generator to use this.
 *
 *  Needs PHP 5.3.2 or newer. If the PHP version is too old, throws a
 *  tx_newspaper_IllegalUsageException.
 *
 *  @throws tx_newspaper_IllegalUsageException if PHP version < 5.3.2
 *
 */
class tx_newspaper_TimedObject {

    public function __construct($class) {
        tx_newspaper::checkAtLeastPHPVersion('5.3.2', 'tx_newspaper_TimedObject');
        $this->timed_class = $class;
        $this->timed_object = new $class();
    }

    public function __call($method, $arguments) {

        $reflection_method = $this->makeMethodPublic($method);

        $timer = tx_newspaper_ExecutionTimer::create($method);

        return $reflection_method->invokeArgs($this->timed_object, $arguments);
    }

    ////////////////////////////////////////////////////////////////////////////

    private function makeMethodPublic($method) {
        $reflection_method = new ReflectionMethod($this->timed_class, $method);
        $reflection_method->setAccessible(true);
        return $reflection_method;
    }

    private $timed_object = null;
    private $timed_class = '';

}

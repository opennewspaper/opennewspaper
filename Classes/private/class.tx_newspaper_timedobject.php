<?php
/**
 * Author: Lene Preuss <lene.preuss@gmail.com>
 * Date: 12/15/11
 * Time: 4:13 PM
 */

require_once('class.tx_newspaper_executiontimer.php');

/**
 *  Proxy class which times every method of another class, using a tx_newspaper_ExecutionTimer.
 *
 *  The result of the time measurements is logged according to the tx_newspaper_TimingLogger
 *  set with tx_newspaper_ExecutionTimer::setLogger() - by default it is written
 *  to the devlog.
 *
 *  Usage: instead of
 *  \code
 *  $object = new MyClass();
 *  $object = new MyClass($args);
 *  \endcode
 *  use
 *  \code
 *  $object = new tx_newspaper_TimedObject('MyClass');
 *  $object = new tx_newspaper_TimedObject(new MyClass($args));
 *  \endcode
 *
 *  If you use dependency injection, you can simply change the generator to use this. Otherwise
 *  you must change every object creation (as necessary).
 *
 *  Needs PHP 5.3.2 or newer. If the PHP version is too old, throws a
 *  tx_newspaper_IllegalUsageException.
 *
 *  @throws tx_newspaper_IllegalUsageException if PHP version < 5.3.2 or is not initialized
 *          with either an object or a class name.
 *
 */
class tx_newspaper_TimedObject {

    public function __construct($class) {

        tx_newspaper::checkAtLeastPHPVersion('5.3.2', 'tx_newspaper_TimedObject');

        if (is_string($class)) {
            $this->timed_class = $class;
            $this->timed_object = new $class();
        } elseif (is_object($class)) {
            $this->timed_class = get_class($class);
            $this->timed_object = $class;
        } else {
            throw new tx_newspaper_IllegalUsageException('Must be used either with a class name or an object');
        }
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

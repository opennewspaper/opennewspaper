<?php
/**
 * Author: Lene Preuss <lene.preuss@gmail.com>
 * Date: 2/9/12
 * Time: 4:14 PM
 */

require_once(PATH_typo3conf . 'ext/newspaper/classes/class.tx_newspaper_asynchronoustask.php');

require_once(PATH_typo3conf . 'ext/newspaper/classes/private/class.tx_newspaper_executiontimer.php');

/** PHPUnit declarations must be included manually here so that they are available
 *  to the delegate script, which does not have the entire Typo3 environment
 *  included.
 */
require_once(t3lib_extMgm::extPath('phpunit') . '/class.tx_phpunit_testcase.php');

class TestAsynchronousTaskClass {

    /** Time (in seconds) executeLongTask() takes to complete. */
    const long_task_duration = 10;
    /** Name of the file executeQuickTask() writes. */
    const static_state_file = '/tmp/TestAsynchronousTaskClass_state';
    /** Maximum time (in usec) executeQuickTask() should take to complete.
     *  This is an estimate!
     */
    const quick_execution_time = 1000;

    public function __construct() {
        $this->setState();
        tx_newspaper_File::unlink(self::static_state_file);
    }

    public function setState() {
        $this->state = md5(microtime());
    }

    public function getState() {
        return $this->state;
    }

    public function executeLongTask() {
        sleep(self::long_task_duration);
    }

    public function executeLongTaskChangingState() {
        $this->setState();
        $this->executeLongTask();
    }

    public function executeQuickTask() {
        $file = new tx_newspaper_File(self::static_state_file, 'w');
        $file->write('quick task executed: ' . $this->getState());
    }

    private $state = null;
}

class tx_newspaper_AsynchronousTask_testcase extends tx_phpunit_testcase {

    public function setUp() {
        $this->test_object = new TestAsynchronousTaskClass();
        $this->asynchronous_task = new tx_newspaper_AsynchronousTask($this->test_object, 'executeLongTaskChangingState');
    }

    public function test_delegateScriptExists() {
        $this->assertTrue(
            file_exists($this->asynchronous_task->getDelegateScript()),
            'Delegate script does not exist'
        );
    }

    public function test_delegateScriptIsExecutable() {
        $this->assertTrue(
            is_executable($this->asynchronous_task->getDelegateScript()),
            'Delegate script is not executable'
        );
    }

    public function test_getSerializedObjectFileExists() {
        $this->assertTrue(
            file_exists($this->asynchronous_task->getSerializedObjectFile()),
            'Serialized object file does not exist'
        );
    }

    public function test_getSerializedObjectFileIsInTypo3temp() {
        $this->assertTrue(
            strpos('typo3temp', $this->asynchronous_task->getSerializedObjectFile()) !== false,
            'Serialized object file is not in typo3temp folder: ' . $this->asynchronous_task->getSerializedObjectFile()
        );
    }

    public function test_getSerializedObjectFileContainsTestClass() {
        $object = $this->getObjectFromObjectFile();
        $this->assertTrue($object !== false, 'Contents of serialized object file are not unserializable');
        $this->assertTrue(is_object($object), 'Contents of serialized object file are not an object');
        $this->assertTrue(
            $object instanceof TestAsynchronousTaskClass,
            'Contents of serialized object file are not of the expected class'
        );
    }

    public function test_getSerializedObjectKeepsState() {
        $object = $this->getObjectFromObjectFile();
        $this->assertEquals(
            $this->test_object->getState(), $object->getState(),
            'Serialized object does not retain original state'
        );
    }

    public function test_executeIsFaster() {

        tx_newspaper_ExecutionTimer::start();

        $this->asynchronous_task->execute();

        $this->assertTrue(
            tx_newspaper_ExecutionTimer::getExecutionTime() < TestAsynchronousTaskClass::long_task_duration*1000,
            'execute() is not speeded up'
        );
    }

    public function test_executeIsReallyExecuted() {
        $asynchronous_task = new tx_newspaper_AsynchronousTask($this->test_object, 'executeQuickTask');
        $retval = $asynchronous_task->execute();

        usleep(TestAsynchronousTaskClass::quick_execution_time);

        $this->assertTrue(
            file_exists(TestAsynchronousTaskClass::static_state_file),
            'executeQuickTask() did not write state file; returned ' . $retval
        );
    }

    ////////////////////////////////////////////////////////////////////////////

    private function getObjectFromObjectFile() {
        $serialized_file = new tx_newspaper_File($this->asynchronous_task->getSerializedObjectFile());
        $serialized_object = $serialized_file->read();
        return unserialize($serialized_object);
    }

    /** @var tx_newspaper_AsynchronousTask */
    private $asynchronous_task = null;
    /** @var TestAsynchronousTaskClass */
    private $test_object = null;

}

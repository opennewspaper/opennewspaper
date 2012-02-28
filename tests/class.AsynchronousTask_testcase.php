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
     *  This value is dependent on how long the delegate PHP script takes to
     *  do a single task (writing a file). Because the delegate script needs to
     *  load the entire framework, expect this to take over half a second. The
     *  exact value depends on your server environment!
     */
    const quick_execution_time = 1000000;

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
        $this->test_object = new AsynchronousTaskTestHelper();
        $this->asynchronous_task = new tx_newspaper_AsynchronousTask($this->test_object, 'executeLongTaskChangingState');
    }

    public function tearDown() {
        tx_newspaper_File::unlink(AsynchronousTaskTestHelper::static_state_file);
    }

    public function test_delegateScriptExists() {
        $this->assertTrue(
            file_exists($this->asynchronous_task->getDelegateScript()),
            'Delegate script does not exist'
        );
    }

    public function test_executeIsFaster() {

        tx_newspaper_ExecutionTimer::start();

        $this->asynchronous_task->execute();

        $this->assertTrue(
            tx_newspaper_ExecutionTimer::getExecutionTime() < AsynchronousTaskTestHelper::long_task_duration*1000,
            'execute() is not speeded up'
        );
    }

    public function test_executeIsReallyExecuted() {
        $asynchronous_task = new tx_newspaper_AsynchronousTask($this->test_object, 'executeQuickTask', array(), array(__FILE__));
        $asynchronous_task->execute();

        usleep(AsynchronousTaskTestHelper::quick_execution_time);

        $this->assertTrue(
            file_exists(AsynchronousTaskTestHelper::static_state_file),
            'executeQuickTask() did not write state file. ' .
            'This may be because the server this script runs on is to slow; try ' .
            'increasing AsynchronousTaskTestHelper::quick_execution_time in the unit ' .
            'test PHP file before deciding that this test failed!'
        );

    }

    public function test_isRunning() {
        $asynchronous_task = new tx_newspaper_AsynchronousTask($this->test_object, 'executeQuickTask', array(), array(__FILE__));
        $asynchronous_task->execute();

        $this->assertTrue($asynchronous_task->isRunning(), 'isRunning() is false immediately after execute()');

        usleep(AsynchronousTaskTestHelper::quick_execution_time/100);
        $this->assertTrue($asynchronous_task->isRunning(), 'isRunning() is false after 1% of estimated execution time');

        usleep(AsynchronousTaskTestHelper::quick_execution_time+AsynchronousTaskTestHelper::quick_execution_time/2);
        $this->assertFalse($asynchronous_task->isRunning(), 'isRunning() is true after 151% of estimated execution time');

    }

    ////////////////////////////////////////////////////////////////////////////

    /** @var tx_newspaper_AsynchronousTask */
    private $asynchronous_task = null;
    /** @var TestAsynchronousTaskClass */
    private $test_object = null;

}

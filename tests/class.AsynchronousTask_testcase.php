<?php
/**
 * Author: Lene Preuss <lene.preuss@gmail.com>
 * Date: 2/9/12
 * Time: 4:14 PM
 */

class TestAsynchronousTaskClass {

    const long_task_duration = 10;

    public function __construct() {
        $this->setState();
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
        $this->executeLongTask();
        $this->setState();
    }

    private $state = null;
}

class tx_newspaper_AsynchronousTask_testcase extends tx_phpunit_testcase {

    public function setUp() {
        $this->test_object = new TestAsynchronousTaskClass();
        $this->asynchronous_task = new tx_newspaper_AsynchronousTask($this->test_object, 'executeLongTaskChangingState');
    }

    public function test_whut() {
        $script = $this->asynchronous_task->getDelegateScript();
        $this->fail($script);
    }

    /** @var tx_newspaper_AsynchronousTask */
    private $asynchronous_task = null;
    /** @var TestAsynchronousTaskClass */
    private $test_object = null;
}

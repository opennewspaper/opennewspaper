<?php
/**
 * @author: Lene Preuss <lene.preuss@gmail.com>
 */

/**
 * @todo DESCRIPTION
 */
class tx_newspaper_DependencyTreeProxy extends tx_newspaper_DependencyTree {

    public function __construct(tx_newspaper_DependencyTree $tree) {
        $this->tree = $tree;
    }

    public function executeActionsOnPages($key = '') {
        if (class_exists('tx_AsynchronousTask')) {
            $task = new tx_AsynchronousTask($this->tree, 'executeActionsOnPages', $key);
            $task->execute();
        } else {
            $this->tree->executeActionsOnPages($key);
        }
    }

    public function __call($method, $arguments) {

        $reflection_method = new ReflectionMethod($this->tree, $method);

        return $reflection_method->invokeArgs($this->tree, $arguments);
    }


    /** @var tx_newspaper_DependencyTree */
    private $tree = null;
}

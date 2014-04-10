<?php
/**
 * @author: Lene Preuss <lene.preuss@gmail.com>
 */
require_once(PATH_typo3conf . 'ext/newspaper/Classes/class.tx_newspaper_dependencytree.php');

/**
 * @todo DESCRIPTION
 */
class tx_newspaper_DependencyTreeProxy extends tx_newspaper_DependencyTree {

    public function __construct(tx_newspaper_DependencyTree $tree) {
        $this->tree = $tree;
    }

    public function executeActionsOnPages($key = '') {
        tx_newspaper_Debug::w("tx_newspaper_DependencyTreeProxy::executeActionsOnPages($key)", tx_newspaper_taz_Savehooks::deptree_debug_log);
        $timer = tx_newspaper_ExecutionTimer::create();
        if (class_exists('tx_AsynchronousTask')) {
            $task = new tx_AsynchronousTask($this->tree, 'executeActionsOnPages', array($key));
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

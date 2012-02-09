<?php
/**
 * Author: Lene Preuss <lene.preuss@gmail.com>
 * Date: 2/9/12
 * Time: 3:03 PM
 */

/**
 *  Executes a given function on a given object asynchronously in the background.
 *
 *  Because PHP program execution functions do not allow executing a program in
 *  the background, some indirection is necessary. A shell script is called which
 *  does the background execution, returning immediately.
 */
class tx_newspaper_AsynchronousTask {

    const delegate_script_name = 'util/execute_asynchronously.sh';

    public function __construct($object, $method, array $arguments = array()) {

        self::checkApplicability($object, $method);

        $this->object = $object;
        $this->method = $method;
        $this->args = $arguments;
    }

    public function execute() {
        system(
            $this->getDelegateScript() . ' ' .
            $this->getSerializedObjectFile() . ' ' .
            $this->getMethodName() . ' ' .
            $this->getSerializedArgsFile()
        );
    }

    public function getDelegateScript() {
        return t3lib_extMgm::extPath('newspaper', self::delegate_script_name);
    }

    public function getSerializedObjectFile() {
        return self::getSerializedFile($this->object);
    }

    public function getMethodName() {
        return $this->method;
    }

    public function getSerializedArgsFile() {
        return self::getSerializedFile($this->args);
    }

    ////////////////////////////////////////////////////////////////////////////

    private static function checkApplicability($object, $method) {
        if (!is_object($object)) {
            throw new tx_newspaper_IllegalUsageException("Asynchronous task can only be executed on an object");
        }
        if (!in_array($method, get_class_methods($object))) {
            throw new tx_newspaper_IllegalUsageException("Method $method not present in class " . get_class($object));
        }
    }

    private static function getSerializedFile($something) {
        $file = new tx_newspaper_File(t3lib_div::tempnam(get_class($this)), 'w');
        $file->write(serialize($something));
        return $file->getName();
    }

    private $object = null;
    private $method = '';
    private $args = array();

}

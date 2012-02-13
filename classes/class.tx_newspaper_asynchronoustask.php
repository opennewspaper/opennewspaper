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

    const delegate_shell_script_name = 'util/execute_asynchronously.sh';
    const delegate_php_script_name = 'util/execute_asynchronously.php';

    public function __construct($object, $method, array $arguments = array()) {

        self::checkApplicability($object, $method);

        $this->object = $object;
        $this->method = $method;
        $this->args = $arguments;
    }

    public function execute() {
        $this->subprocess_pid = shell_exec(
            "nohup php " .
            self::quote(self::getFullScriptPath(self::delegate_php_script_name)) . ' ' .
            self::quote($this->getSerializedObjectFile()) . ' ' .
            self::quote($this->getMethodName()) . ' ' .
            self::quote($this->getSerializedArgsFile()) .
            " > /dev/null 2>&1 &"
        );
    }

    public function execute_complicated() {
        return system(
            self::quote(self::getFullScriptPath(self::delegate_shell_script_name)) . ' ' .
            self::quote($this->getSerializedObjectFile()) . ' ' .
            self::quote($this->getMethodName()) . ' ' .
            self::quote($this->getSerializedArgsFile()) 
        );
    }

    public function getFullScriptPath($script_name) {
        return t3lib_extMgm::extPath('newspaper', $script_name);
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

    private static function quote($string) {
        return '"' . $string . '"';
    }

    private $object = null;
    private $method = '';
    private $args = array();

    private $subprocess_pid = null;

}

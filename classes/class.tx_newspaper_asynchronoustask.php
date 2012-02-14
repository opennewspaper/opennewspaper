<?php
/**
 * Author: Lene Preuss <lene.preuss@gmail.com>
 * Date: 2/9/12
 * Time: 3:03 PM
 */

/**
 *  Executes a given function on a given object asynchronously in the background.
 *
 *  If a procedure takes a lot of time, it may be desirable to execute it as a
 *  background task to improve response time. In that case, instead of
 *  \code
 *  $object = new WorkingClass();
 *  $object->overthrowBourgeoisie(); // this takes a really long time!
 *  \endcode
 *  you can use \c tx_newspaper_AsynchronousTask thusly:
 *  \code
 *  $object = new WorkingClass();
 *  $task = new tx_newspaper_AsynchronousTask($object, 'overthrowBourgeoisie');
 *  $task->execute(); // we don't have to wait for overthrowBourgeoisie() to finish
 *  \endcode
 *  Control is returned immediately after \c $task->execute(), and program flow
 *  continues immediately instead of waiting seconds or even decades for
 *  \c overthrowBourgeoisie() to finish.
 *
 *  The method called on the object can theoretically take up to three arguments,
 *  but this is not yet tested.
 */
class tx_newspaper_AsynchronousTask {

    /** The path of the PHP script that loads the objectand executes the method,
     *  relative to the newspaper extension directory.
     */
    const delegate_php_script_name = 'util/execute_asynchronously.php';
    /** Name of the file where the output of the delegate script is logged,
     *  defaulting to no logging.
     */
    const default_log_file = '/dev/null';

    public function __construct($object, $method, array $arguments = array()) {

        self::checkApplicability($object, $method);

        $this->object = $object;
        $this->method = $method;
        $this->args = $arguments;
    }

    /**
     *  Launches the task in the background, returning immediately.
     *
     *  Implementation note: for the daughter process to be executed in the
     *  background, shell_exec() makes it necessary that STDOUT and STDERR are
     *  redirected explicitly. If they are not explicitly redirected,
     *  shell_exec() waits for the process to finish, even if nohup redirects
     *  the streams implicitly.
     */
    public function execute() {
        shell_exec(
            'nohup php ' .
            self::quote(self::getDelegateScript()) . ' ' .
            self::quote($this->getSerializedObjectFile()) . ' ' .
            self::quote($this->getMethodName()) . ' ' .
            self::quote($this->getSerializedArgsFile()) .
            ' > ' . self::getLogFile() . ' 2>&1 &'
        );
    }

    public function getDelegateScript() {
        return t3lib_extMgm::extPath('newspaper', self::delegate_php_script_name);
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

    private static function getLogFile() {
        if (self::getTSconfig('execute_asynchronously_log')) {
            return self::getTSconfig('execute_asynchronously_log');
        }
        return self::default_log_file;
    }

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

    private static function getTSconfig($key) {
        $tsconfig = tx_newspaper::getTSConfig();
        return $tsconfig['newspaper.'][$key];
    }

    private $object = null;
    private $method = '';
    private $args = array();

}

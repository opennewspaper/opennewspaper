<?php

/// Executes a given function on a given object asynchronously in the background.
/**
 *  If a procedure takes a lot of time, it may be desirable to execute it as a
 *  background task to improve response time. In that case, instead of
 *  \code
 *  $object = new WorkingClass();
 *  $object->overthrowBourgeoisie(); // this may take a really long time!
 *  \endcode
 *  you can use tx_newspaper_AsynchronousTask this way:
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
 *
 *  TSconfig:
 *  \code
 *  # where the output of the delegate script is logged
 *  # default: /dev/null
 *  newspaper.execute_asynchronously_log = <filename>
 *  \endcode
 *
 *  The PHP script that is actually executed in the background loads the Typo3
 *  framework and any additionally supplied include files and then executes the
 *  requested method on the requested object.
 *
 *  @see execute_asynchronously.php
 *
 *  @author Lene Preuss <lene.preuss@gmail.com>
 *
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

    /**
     *  @param object $object The object a method is called on.
     *  @param string $method The method called on \p $object.
     *  @param array $arguments Up to three arguments for method. The method call
     *      executed is
     *      \code $object->$method($args[0], $args[1], $args[2]) \endcode
     *      Higher numbers of arguments are not supported and will fail.
     *  @param array $includes Any additional PHP files that must be included
     *      in the delegate script. This may be necessary if the class definition
     *      for \c $object is not automatically included by Typo3 in your
     *      configuration. (An example for that would be unit tests.)
     */
    public function __construct($object, $method, array $arguments = array(), array $includes = array()) {

        self::checkApplicability($object, $method);

        $this->object = $object;
        $this->method = $method;
        $this->args = $arguments;
        $this->includes = $includes;

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
        $this->serializeData();
        shell_exec($this->delegateCommand() . ' >> ' . self::getLogFile() . ' 2>&1 &');
    }

    /**
     * @return bool true while the task is still running in the background.
     */
    public function isRunning() {
        return file_exists($this->object_file);
    }

    /**
     *  Kills the process.
     */
    public function kill() {
        throw new tx_newspaper_NotYetImplementedException();
    }

    /**
     * @return string The path to the script that executes the task.
     *  @see execute_asynchronously.php
     */
    public function getDelegateScript() {
        return t3lib_extMgm::extPath('newspaper', self::delegate_php_script_name);
    }

    /**
     * @return The object the task is executed on.
     */
    public function getObject() {
        return $this->object;
    }

    /**
     * @return string Name of the method that is executed.
     */
    public function getMethodName() {
        return $this->method;
    }

    /**
     * @return array The list of arguments to the method that is executed.
     */
    public function getArgs() {
        return $this->args;
    }

    /**
     *  @return array List of PHP files that are additionally included by the
     *  delegate script.
     */
    public function getIncludes() {
        return $this->includes;
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

    private function serializeData() {
        if ($this->isRunning()) return;

        $this->object_file = self::getSerializedFile($this->object);
        $this->args_file = self::getSerializedFile($this->args);
        $this->includes_file = self::getSerializedFile($this->includes);
    }

    private function delegateCommand() {
        return 'nohup php ' .
                self::quote(self::getDelegateScript()) . ' ' .
                self::quote($this->object_file) . ' ' .
                self::quote($this->getMethodName()) . ' ' .
                self::quote($this->args_file) . ' ' .
                self::quote($this->includes_file);
    }

    private static function getLogFile() {
        if (self::getTSconfig('execute_asynchronously_log')) {
            return self::getTSconfig('execute_asynchronously_log');
        }
        return self::default_log_file;
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
    private $object_file = '';
    private $method = '';
    private $args = array();
    private $args_file = '';
    private $includes = array();
    private $includes_file = '';

}

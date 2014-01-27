<?php
/**
 * Created by JetBrains PhpStorm.
 * User: lene
 * Date: 12/13/11
 * Time: 5:29 PM
 * To change this template use File | Settings | File Templates.
 */

require_once('class.tx_newspaper_file.php');

/**
 *  Manages the writing of the execution time of a function to a log of some sort.
 */
abstract class tx_newspaper_TimingLogger {

    /**
     *  Writes the execution time of a function to a log of some sort.
     *  @abstract
     */
    abstract public function log($message, tx_newspaper_TimingInfo $info, $depth);

    /**
     *  Formatting that is used to indent functions of different depth in the
     *  call hierarchy.
     *  @abstract
     */
    abstract protected function indentString();

    /**
     *  Format an entry to the log. This is a default implementation that
     *  prepends the timing info with \p $depth times indentString().
     *  @param $message Optional message written to the log.
     *  @param tx_newspaper_TimingInfo $info The timing info.
     *  @param $depth Depth in the call hierarchy.
     *  @return string The formatted message.
     */
    protected function formatMessage($message, tx_newspaper_TimingInfo $info, $depth) {
        if (!$message) $message = $info->getTimedFunction();
        return str_repeat($this->indentString(), max($depth, 0)) . $message;
    }

}

/**
 *  Manages the writing of the execution time of a function to Typo3's devlog.
 */
class tx_newspaper_Devlogger extends tx_newspaper_TimingLogger {

    /**
     *  Writes the execution time of a function to Typo3's devlog.
     */
    public function log($message, tx_newspaper_TimingInfo $info, $depth) {
        tx_newspaper::devlog($this->formatMessage($message, $info, $depth), "$info");
    }

    /**
     * @return string Underscores are nicely visible in the devlog.
     */
    protected function indentString() { return '__'; }

}

/**
 *  Manages the writing of the execution time of a function to a text file. The
 *  path to the text file is configured via TSConfig option
 *  \c newspaper.executionTimeLogFilename.
 *
 *  No particular attention is paid to the order of the messages; the lines in
 *  file are written whenever the logger is called.
 */
class tx_newspaper_FileLogger extends tx_newspaper_TimingLogger {

    /**
     *  Writes the execution time of a function to a text file.
     *
     *  @throw tx_newspaper_IllegalUsageException if the file is not writable.
     */
    public function log($message, tx_newspaper_TimingInfo $info, $depth) {
        $this->checkLogfileValid();
        $this->writeToLogfile($this->formatMessage($message, $info, $depth));
    }

    /**
     * @return string Spaces to indent the message.
     */
    protected function indentString() { return '  '; }

    const message_width = 80;

    /**
     *  Formats the execution time so that it always appears on column
     *  \c message_width.
     */
    protected function formatMessage($message, tx_newspaper_TimingInfo $info, $depth) {
        $message = parent::formatMessage($message, $info, $depth);
        $message = str_pad($message, self::message_width, ' ');
        $time = sprintf(' %9.2f ms', $info->getExecutionTime());
        return $message . $time;
    }
    
    protected function writeToLogfile($message) {
        if (!$this->filehandle) {
            $this->filehandle = new tx_newspaper_File($this->getFileName());
        }

        $this->filehandle->write($message . "\n");
    }

    ////////////////////////////////////////////////////////////////////////////

    private function getFileName() {
        if (!$this->filename) {
            $tsconfig = tx_newspaper::getTSConfig();
            $this->filename = $tsconfig['newspaper.']['executionTimeLogFilename'];
        }
        return $this->filename;
    }

    private function checkLogfileValid() {
        $this->checkLogfileSpecified();
        $this->checkLogfileWritable();
    }

    private function checkLogfileWritable() {
        if (!self::isFileWritable($this->getFileName())) {
            throw new tx_newspaper_IllegalUsageException(
                'tx_newspaper_FileLogger: File ' . $this->getFileName() . ' not writable', true
            );
        }
    }

    private static function isFileWritable($filename) {
        if (file_exists($filename) && is_writable($filename)) return true;
        if (!file_exists($filename) && is_writable(dirname($filename))) return true;
        return false;
    }

    private function checkLogfileSpecified() {
        if (!$this->getFileName()) {
            throw new tx_newspaper_IllegalUsageException(
                'tx_newspaper_FileLogger instantiated without a file name specified in newspaper.executionTimeLogFilename', true
            );
        }
    }

    /** @var tx_newspaper_File */
    protected  $filehandle = null;
    private $filename = '';

}

/**
 *  Manages the writing of the execution time of a function to a text file. The
 *  path to the text file is configured via TSConfig option
 *  \c newspaper.executionTimeLogFilename.
 *
 *  The log entries are written in the order calling function first, called
 *  functions below the caller.
 */
class tx_newspaper_OrderedFileLogger extends tx_newspaper_FileLogger {

    /**
     *  Ensures that all messages which have not yet been written are logged.
     */
    public function __destruct() {
        if (!is_null($this->filehandle)) {
            $this->writeStackReversed();
        }
    }

    /**
     *  The message is stored until a call hierarchy has been fully done, then
     *  the stored messages are logged in the correct order.
     */
    public function log($message, tx_newspaper_TimingInfo $info, $depth) {
        $this->message_stack[] = $this->formatMessage($message, $info, $depth);
        if ($depth == 0) {
            $this->writeStackReversed();
        }
    }

    /**
     *  write messages on the stack in reverse order to the logfile; empty stack
     */
    private function writeStackReversed() {
        while (!empty($this->message_stack)) {
            $this->writeToLogfile(array_pop($this->message_stack));
        }
    }

    private $message_stack = array();

}

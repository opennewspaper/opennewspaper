<?php
/**
 * Created by JetBrains PhpStorm.
 * User: lene
 * Date: 12/13/11
 * Time: 5:29 PM
 * To change this template use File | Settings | File Templates.
 */

require_once('class.tx_newspaper_file.php');

abstract class tx_newspaper_TimingLogger {
    abstract public function log($message, tx_newspaper_TimingInfo $info, $depth);

    abstract protected function indentString();

    protected function formatMessage($message, tx_newspaper_TimingInfo $info, $depth) {
        if (!$message) $message = $info->getTimedFunction();
        return str_repeat($this->indentString(), $depth) . $message;
    }

}

class tx_newspaper_Devlogger extends tx_newspaper_TimingLogger {
    public function log($message, tx_newspaper_TimingInfo $info, $depth) {
        tx_newspaper::devlog($this->formatMessage($message, $info, $depth), "$info");
    }

    protected function indentString() { return '__'; }

}

class tx_newspaper_FileLogger extends tx_newspaper_TimingLogger {

    public function log($message, tx_newspaper_TimingInfo $info, $depth) {
        $this->checkLogfileValid();
        $this->writeToLogfile($this->formatMessage($message, $info, $depth));
    }

    protected function indentString() { return '  '; }

    protected function formatMessage($message, tx_newspaper_TimingInfo $info, $depth) {
        $message = parent::formatMessage($message, $info, $depth);
        $message = str_pad($message, 80, ' ');
        return $message . $info->getExecutionTime() . ' ms';

    }
    ////////////////////////////////////////////////////////////////////////////

    private function getFileName() {
        $tsconfig = tx_newspaper::getTSConfig();
        return $tsconfig['newspaper.']['executionTimeLogFilename'];
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

    private function writeToLogfile($message) {
        $f = new tx_newspaper_File($this->getFileName());
        $f->write($message . "\n");
    }
}


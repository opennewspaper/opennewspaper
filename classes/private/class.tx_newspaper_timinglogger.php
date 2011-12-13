<?php
/**
 * Created by JetBrains PhpStorm.
 * User: lene
 * Date: 12/13/11
 * Time: 5:29 PM
 * To change this template use File | Settings | File Templates.
 */

abstract class tx_newspaper_TimingLogger {
    abstract public function log($message, tx_newspaper_TimingInfo $info, $depth);

    protected static function formatMessage($message, tx_newspaper_TimingInfo $info, $depth) {
        if (!$message) $message = $info->getTimedFunction();
        return self::getRecursionLevelInsertString($depth) . $message;
    }

    ////////////////////////////////////////////////////////////////////////////
    
    private static function getRecursionLevelInsertString($level) {
        $string = '';
        for ($i = 0; $i < $level; $i++) {
            $string = "__$string";
        }
        return $string;
    }
    
}

class tx_newspaper_Devlogger extends tx_newspaper_TimingLogger {
    public function log($message, tx_newspaper_TimingInfo $info, $depth) {
        $message = tx_newspaper_TimingLogger::formatMessage($message, $info, $depth);
        tx_newspaper::devlog($message, "$info");
    }

}

class tx_newspaper_FileLogger extends tx_newspaper_TimingLogger {

    public function __construct($filename) {
        $this->filename = $filename;
    }

    public function log($message, tx_newspaper_TimingInfo $info, $depth) {
        $this->checkLogfileValid();

        $message = tx_newspaper_TimingLogger::formatMessage($message, $info, $depth);

        throw new tx_newspaper_NotYetImplementedException();
    }

    ////////////////////////////////////////////////////////////////////////////
    
    private function checkLogfileValid() {
        if (!$this->filename) {
            throw new tx_newspaper_IllegalUsageException('tx_newspaper_FileLogger instantiated without a file name');
        }
        if (!file_exists($this->filename) && !is_writable($this->filename)) {
            throw new tx_newspaper_IllegalUsageException('tx_newspaper_FileLogger: File ' . $this->filename . ' not writable');
        }
    }

    private $filename = '';
}

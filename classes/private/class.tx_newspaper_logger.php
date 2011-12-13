<?php
/**
 * Created by JetBrains PhpStorm.
 * User: lene
 * Date: 12/13/11
 * Time: 5:29 PM
 * To change this template use File | Settings | File Templates.
 */

interface tx_newspaper_Logger {
    public function log($message, array $info);
}

class tx_newspaper_Devlogger implements tx_newspaper_Logger {
    public function log($message, array $info) {
        tx_newspaper::devlog($message, $info);
    }
}

class tx_newspaper_FileLogger implements tx_newspaper_Logger {

    public function __construct($filename) {
        $this->filename = $filename;
    }

    public function log($message, array $info) {
        if (!$this->filename) {
            throw new tx_newspaper_IllegalUsageException('tx_newspaper_FileLogger instantiated without a file name');
        }
        if (!file_exists($this->filename) && !is_writable($this->filename)) {
            throw new tx_newspaper_IllegalUsageException('tx_newspaper_FileLogger: File ' . $this->filename . ' not writable');
        }

        throw new tx_newspaper_NotYetImplementedException();
    }

    private $filename = '';
}

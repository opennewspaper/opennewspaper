<?php
/**
 * Author: Lene Preuss <lene.preuss@gmail.com>
 * Date:   1/4/12
 * Time:   3:11 PM
 */

require_once('class.tx_newspaper_timinglogger.php');
require_once('class.tx_newspaper_tablerenderer.php');

class tx_newspaper_HTMLFileLogger extends tx_newspaper_FileLogger {

    protected function formatMessage($message, tx_newspaper_TimingInfo $info, $depth) {
        $html_row = new tx_newspaper_TableRow(self::getTableCells($depth, $info));
        return $html_row->getHTML();
    }

    protected function indentString() {
        return '&nbsp; &nbsp; ';
    }

    private function getTableCells($depth, $info) {

        $indent = str_repeat($this->indentString(), $depth);

        return array(
                    $indent . $info->getTimedFunction(),
                    sprintf(' %8.2f ms', $info->getExecutionTime())
                );
    }

}

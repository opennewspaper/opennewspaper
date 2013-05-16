<?php

require_once('private/class.tx_newspaper_file.php');

/**
 * @author Lene Preuss <lene.preuss@gmail.com>
 */
class tx_newspaper_SearchLogger {

    /// Whether to log search terms
    private static $log_searches = false;

    /// Whether to log search results
    private static $log_results = true;

    /// Path to log file
    private static $log_file = '/www/onlinetaz/logs/search.log';

    public function __construct() {
        if (!self::$log_searches) return;
        $this->log = new tx_newspaper_File(self::$log_file, 'a');
    }

    public function log($search, array $results) {
        if (is_null($this->log)) return;
        $this->log->write('Search term: ' . $search . "\n");
        $this->logResults($results);
    }

    private function logResults($results) {

        if (!self::$log_results || is_null($this->log)) return;

        $this->log->write('Results:' . "\n");
        if ($results) {
            foreach ($results as $result) {
                $this->logResult($result);
            }
        } else {
            $this->log->write('    None!' . "\n");
        }
    }

    private function logResult($result) {
        if (is_null($this->log)) return;
        if (!($result instanceof tx_newspaper_ArticleIface)) {
            $this->log->write('    Not an Article: ' . $result . "\n");
        } else {
            $this->log->write(
                '    Article ' . $result->getUid() . ': ' . $result->getAttribute('title') . "\n"
            );
        }
    }

    /** @var tx_newspaper_File */
    private $log = null;

}
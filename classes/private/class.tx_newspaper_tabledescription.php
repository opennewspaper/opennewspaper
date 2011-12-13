<?php

/// Contains the name and alias of a table used in SQL statements
/** Utility class to correctly determine the enableFields for a table, i.e.
 *  (if you're not familiar with the Typo3 terminology) additional conditions
 *  that must be met for a record to be displayed.
 */
class tx_newspaper_TableDescription {

    /** Creates an array of table descriptions from an SQL table definition
     *  that may contain any number of JOINs
     *  @return array A table description with name and alias for every table.
     */
    static public function createDescriptions($string) {
        $descriptions = array();
        foreach (self::splitOnJoins($string) as $table) {
            $descriptions[] = new tx_newspaper_TableDescription($table);
        }

        return $descriptions;
    }

    public function __construct($string) {
        $this->words = self::removeEmptyStrings(explode(' ', $string));
    }

    public function __toString() {
        return 'Name: ' . $this->getTableName() . ' Alias: ' . $this->getTableAlias() . " String: '" . $this->string . "' Words: " . print_r($this->words, 1);
    }

    /** @return string Typo3 enable fields definition as a string in the form of
     *    \code ' AND <table>.<field> = <value>' \endcode
     */
    public function getEnableFields() {
        if ($this->isRegisteredTable()) {
            if (TYPO3_MODE == 'FE') {
                return $this->getEnableFieldsFE();
            } else {
                return $this->getEnableFieldsBE();
            }
        }
    }

    public function getTableName() {
        $this->extractName();
        return $this->table_name;
    }

    public function getTableAlias() {
        $this->extractAlias();
        return $this->table_alias;
    }

    ////////////////////////////////////////////////////////////////////////////

    private function extractAlias() {
        if (!$this->table_alias) {
            for ($i = 1; $i < min(sizeof($this->words), 3); $i++) {
                if (strtolower($this->words[$i]) == 'as') continue;
                if (self::doesNotBeginAlias($this->words[$i])) break;
                $this->table_alias = $this->words[$i];
            }
            if (!$this->table_alias) {
                $this->table_alias = $this->table_name;
            }
        }
    }

    private function extractName() {
        if (!$this->table_name) {
            $this->table_name = $this->words[0];
        }
    }

    /// Show everything but deleted records in backend, if deleted flag is existing for given table
    private function getEnableFieldsBE() {
        $table_name = $this->getTableName();
        t3lib_div::loadTCA($table_name); // make sure tca is available
        if (isset($GLOBALS['TCA'][$table_name]['ctrl']['delete'])) {
            return ' AND NOT ' . $this->getTableAlias() . '.' . $GLOBALS['TCA'][$table_name]['ctrl']['delete'];
        }
    }

    /// use values defined in admPanel config (override given $show_hidden param) see: enableFields() in t3lib_pageSelect
    private function getEnableFieldsFE() {
        require_once(PATH_t3lib . '/class.t3lib_page.php');
        $show_hidden = ($this->getTableName() == 'pages') ? $GLOBALS['TSFE']->showHiddenPage : $GLOBALS['TSFE']->showHiddenRecords;
        $p = t3lib_div::makeInstance('t3lib_pageSelect');
        return $p->enableFields($this->getTableName(), $show_hidden);
    }

    private function isRegisteredTable() {
        t3lib_div::loadTCA($this->getTableName()); // make sure tca is available
        return array_key_exists($this->getTableName(), $GLOBALS['TCA']);
    }

    private static function splitOnJoins($string) {
        $string = strtolower($string);

        return self::splitArrayOnArray(
            self::splitOnComma($string),
            array(' left join ', ' right join ', ' inner join ', ' join ')
        );
    }

    private static function splitOnComma($string) {
        $comma_separated = explode(',', $string);
        for ($i = 0; $i < sizeof($comma_separated); $i++) {
            $comma_separated[$i] = trim($comma_separated[$i]);
        }
        return $comma_separated;
    }

    private static function splitStringOnWord($string, $word) {
        $split_position = strpos($string, $word);
        if ($split_position === false) return array($string);
        $first = substr($string, 0, $split_position);
        $second = substr($string, $split_position+strlen($word));
        return array_merge(array($first), self::splitStringOnWord($second, $word));
    }

    private static function splitArrayOnArray(array $strings, array $words) {
        $return = $strings;
        foreach ($words as $word) {
            $return = self::splitArrayOnWord($return, $word);
        }
        return $return;
    }

    private static function splitArrayOnWord(array $strings, $word) {
        $return = array();
        foreach ($strings as $table) {
            $return = array_merge($return, self::splitStringOnWord($table, $word));
        }
        return $return;
    }

    private static function doesNotBeginAlias($string) {
        if (strtolower($string) == 'join') return true;
        if (strtolower($string) == 'left') return true;
        if (strtolower($string) == 'right') return true;
        if (strtolower($string) == 'inner') return true;
        if (strtolower($string) == 'on') return true;
        return false;
    }

    static private function removeEmptyStrings(array $strings) {
        $return = array();
        foreach($strings as $string) {
            if (trim($string)) $return[] = $string;
        }
        return $strings;
    }

    private $table_alias = '';
    private $table_name = '';

    private $words = array();

}

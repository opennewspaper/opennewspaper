<?php

require_once('private/class.tx_newspaper_executiontimer.php');

require_once('private/class.tx_newspaper_db.php');

/// Utility class which provides static functions. A namespace, so to speak.
/** Because PHP has introduced namespaces only with PHP 5.3, and we started development for
 *  \c newspaper on 5.2, and also because 5.3 is not yet widely used, all utility functions
 *  for \c newspaper are moved into class tx_newspaper, which simulates a namespace.
 *
 *  \todo Reorder according to functionality (e.g. DB operations, class logic etc.)
 */
class tx_newspaper  {

    /// Whether to use Typo3's command- and datamap functions for DB operations
    /** If this constant is set to true, Typo3 command- or datamap functions are
     *  used wherever appropriate.
     *
     *  These functions have side effects which are not yet fully explored and
     *  seem to make more trouble than they're worth. That's why they're turned
     *  off currently.
     */
    const use_datamap = false;

    /// The \c GET parameter which determines the article UID
    const article_get_parameter = 'art';
    ///	The \c GET parameter which determines which page type is displayed
    const pagetype_get_parameter = 'pagetype';

    /// GET-parameter describing the wanted control tag for a dossier
    const default_dossier_get_parameter = 'dossier';

    const default_max_logged_queries = 1000;

    // Type for case conversion
    const toLower = 'toLower';
    const toUpper = 'toUpper';

    /// length of the description for extras in the BE
    const description_length = 50;

    ////////////////////////////////////////////////////////////////////////////
    //      DB functions
    ////////////////////////////////////////////////////////////////////////////

    /// Execute a \c SELECT query, check the result, return zero or one record(s)
    /** enableFields() are taken into account.
     *
     *  \param $fields Fields to \c SELECT
     *  \param $table Table to \c SELECT \c FROM
     *  \param $where \c WHERE - clause (defaults to selecting all records)
     *  \param $groupBy Fields to \c GROUP \c BY
     *  \param $orderBy Fields to \c ORDER \c BY
     *  \param $limit Maximum number of records to \c SELECT
     *  \return The result of the query as associative array
     *  \throw tx_newspaper_NoResException if no result is found, probably due
     * 		to a SQL syntax error
     */
    public static function selectZeroOrOneRows($fields, $table, $where = '1',
                                               $groupBy = '', $orderBy = '', $limit = '') {
        return tx_newspaper_DB::getInstance()->selectZeroOrOneRows($fields, $table, $where, $groupBy, $orderBy, $limit);
    }

    /// Execute a \c SELECT query, check the result, return \em exactly one record
    /** enableFields() are taken into account.
     *
     *  \param $fields Fields to \c SELECT
     *  \param $table Table to \c SELECT \c FROM
     *  \param $where \c WHERE - clause (defaults to selecting all records)
     *  \param $groupBy Fields to \c GROUP \c BY
     *  \param $orderBy Fields to \c ORDER \c BY
     *  \param $limit Maximum number of records to \c SELECT
     *  \return The result of the query as associative array
     *  \throw tx_newspaper_NoResException if no result is found, probably due
     * 		to a SQL syntax error
     *  \throw tx_newspaper_EmptyResultException if the SQL query returns no
     * 		result
     */
    public static function selectOneRow($fields, $table, $where = '1',
                                        $groupBy = '', $orderBy = '', $limit = '') {
        return tx_newspaper_DB::getInstance()->selectOneRow($fields, $table, $where, $groupBy, $orderBy, $limit);
    }

    /// Execute a \c SELECT query, check the result, return all records
    /** enableFields() are taken into account.
     *
     *  \param $fields Fields to \c SELECT
     *  \param $table Table to \c SELECT \c FROM
     *  \param $where \c WHERE - clause (defaults to selecting all records)
     *  \param $groupBy Fields to \c GROUP \c BY
     *  \param $orderBy Fields to \c ORDER \c BY
     *  \param $limit Maximum number of records to \c SELECT
     *  \return The result of the query as 2-dimensional associative array
     *  \throw tx_newspaper_NoResException if no result is found, probably due
     * 		to a SQL syntax error
     */
    public static function selectRows($fields, $table, $where = '1',
                                      $groupBy = '', $orderBy = '', $limit = '') {
        return tx_newspaper_DB::getInstance()->selectRows($fields, $table, $where, $groupBy, $orderBy, $limit);
    }

    /// Execute a \c SELECT query, check the result, return all records
    /** enableFields() are NOT taken into account, that's why this method is called selectRowsDIRECT
     *
     *  \param $fields Fields to \c SELECT
     *  \param $table Table to \c SELECT \c FROM
     *  \param $where \c WHERE - clause (defaults to selecting all records)
     *  \param $groupBy Fields to \c GROUP \c BY
     *  \param $orderBy Fields to \c ORDER \c BY
     *  \param $limit Maximum number of records to \c SELECT
     *  \return The result of the query as 2-dimensional associative array
     *  \throw tx_newspaper_NoResException if no result is found, probably due
     * 		to a SQL syntax error
     */
    public static function selectRowsDirect($fields, $table, $where = '1',
                                            $groupBy = '', $orderBy = '', $limit = '') {
        return tx_newspaper_DB::getInstance()->selectRowsDirect($fields, $table, $where, $groupBy, $orderBy, $limit);
    }

    /// Inserts a record into a SQL table
    /** If the class constant \c tx_newspaper::use_datamap is set, the data is
     *  written using \c process_datamap(), which fills in all needed fields and
     *  calls the save hook. Otherwise, \c $GLOBALS['TYPO3_DB']->INSERTquery() is
     *  called.
     *
     *  \param $table SQL table to insert into
     *  \param $row Data as key=>value pairs
     *  \return uid of inserted record
     *  \throw tx_newspaper_NoResException if no result is found, probably due
     * 		to a SQL syntax error
     *  \throw tx_newspaper_DBException if an error occurs in process_datamap()
     */
    public static function insertRows($table, array $record) {
        return tx_newspaper_DB::getInstance()->insertRows($table, $record);
    }

    /// Updates a record using the Typo3 API
    /** \param $table SQL table to update
     *  \param $where SQL \c WHERE condition (typically 'uid = ...')
     *  \param $row Data as key=>value pairs
     *  \return number of affected rows
     *  \throw tx_newspaper_NoResException if no result is found, probably due
     * 		to a SQL syntax error
     */
    public static function updateRows($table, $where, array $record) {
        return tx_newspaper_DB::getInstance()->updateRows($table, $where, $record);
    }

    /// Deletes a record from a DB table
    /** If the class constant \c tx_newspaper::use_datamap is set, the operation
     *  uses \c process_cmdmap(), which checks all needed fields and calls the
     *  save hook. Otherwise, if \p $table is recorded in \c $TCA, its field
     *  'deleted' is set to 1. If \p $table is not recorded in \c $TCA (which is
     *  the case for MM tables), an SQL \c DELETE query is executed.
     *
     *  \param $table SQL table to delete a record from
     *  \param $uids_or_where Array of UIDs to delete, a single UID to delete
     *  	(must be an integer), or a \c WHERE condition as string
     *  \param $key Name of key to be used for the query (default: 'uid')
     *  \param $additional_where be added to the generated where part (AND is added too)
     *  \return number of affected rows
     *  \throw tx_newspaper_NoResException if no result is found, probably due
     * 		to a SQL syntax error
     *  \throw tx_newspaper_DBException if an error occurs in process_datamap()
     */
    public static function deleteRows($table, $uids_or_where, $key='uid', $additional_where='') {
        return tx_newspaper_DB::getInstance()->deleteRows($table, $uids_or_where, $key, $additional_where);
    }

    /// \c WHERE clause to filter out unwanted records
    /** Returns a part of a \c WHERE clause which will filter out records with
     *  start/end times, deleted flag set, or hidden flag set (if hidden should
     *  be included used); switch for BE/FE is included.
     *
     *  \param $tableString name of db table to check (can be a comma separated
     *         list of tables too)
     *  \param $show_hidden [0|1] specifies if hidden records are to be included
     * 		(ignored if in FE)
     *  \return \c WHERE part of an SQL statement starting with \c AND; or an
     * 		empty string, if not applicable.
     */
    static public function enableFields($tableString) {
        return tx_newspaper_DB::getInstance()->enableFields($tableString);
    }

    public static function setDefaultFields(tx_newspaper_StoredObject &$object, array $fields) {
        foreach (self::getDefaultFieldValues($fields) as $attribute => $value) {
            $object->setAttribute($attribute, $value);
        }
        $object->setAttribute('pid', tx_newspaper_Sysfolder::getInstance()->getPid($object));
    }

    public static function getDefaultFieldValues(array $fields) {
        $values = array();
        foreach($fields as $field) {
          if (isset(self::$defaultFields[$field])) {
            $function = self::$defaultFields[$field];
            $values[$field] = self::$function();
          }
        }

        return $values;
    }

    private static $defaultFields = array(
        'tstamp' => 'getTimestamp',
        'crdate' => 'getTimestamp',
        'cruser_id' => 'getBeUserID',
        'modification_user' => 'getBeUserID',
    );

    private static function getTimestamp() { return time(); }
    private static function getBeUserID() { return tx_newspaper::getBeUserUid(); }

    ////////////////////////////////////////////////////////////////////////////
    //      Logging functions
    ////////////////////////////////////////////////////////////////////////////

    /// I've had enough of supplying all these useless parameters to devlog! Here's a wrapper function.
    public static function devlog($message, $data = array(), $extension = 'newspaper', $level = 0) {
        if (!is_array($data)) $data = array($data);
        t3lib_div::devlog($message, $extension, $level, $data);
    }

    /// Simplified creation of flash messages
    public static function showFlashMessage($message, $title, $severity = t3lib_FlashMessage::ERROR) {
        $message = t3lib_div::makeInstance('t3lib_FlashMessage', $message, $title, $severity);
        t3lib_FlashMessageQueue::addMessage($message);
    }

    ////////////////////////////////////////////////////////////////////////////

    public static function isValid(tx_newspaper_StoredObject $obj) {
        try {
            $obj->getAttribute('uid');
            return true;
        } catch (tx_newspaper_Exception $e) {
            return false;
        }
    }

    /// \return Array with TSConfig set in newspaper root folder
    public static function getTSConfig() {
        $root_page = tx_newspaper_Sysfolder::getInstance()->getPidRootfolder();
        return t3lib_BEfunc::getPagesTSconfig($root_page);
    }

    private static $tsconfig = array();
    public static function getTSConfigVar($key) {
        if (empty(self::$tsconfig)) self::$tsconfig = self::getTSConfig();
        if (!is_array($key)) {
            return self::$tsconfig[$key];
        }
        throw new tx_newspaper_NotYetImplementedException('Multidimensional TSConfig');
    }

    /**
     * @param string $key key/name of the property
     * @return array('value', 'properties') of User TSConfig for $key (or null, if BE_USER object is not available)
     */
    public static function getUserTSConfig($key) {
        /** @var $BE_USER t3lib_beUserAuth */
        global $BE_USER;
        if (!is_object($BE_USER)) {
            return null;
        }
        return $BE_USER->getTSConfig($key);
    }

    /**
     * Get value for newspaper debug setting
     * Example: newspaper.debug.be.placementModule = 1
     * @param string $key key/name of the property
     * @return int Setting for User TSConfig for $key (or null, if BE_USER object or setting is not available)
     */
    public static function getUserTSConfigForDebugging($key) {
        $tsc = self::getUserTSConfig($key);
        if (!is_array($tsc)) {
            return null;
        }
        return intval($tsc['value']);
    }

    public static function getDossierPageID() {

        $TSConfig = self::getTSConfig();

        $dossier_page = intval($TSConfig['newspaper.']['dossier_page_id']);
        if (!$dossier_page) {
            throw new tx_newspaper_IllegalUsageException(
                'No dossier page defined. Please set newspaper.dossier_page_id in TSConfig!'
            );
        }
        return $dossier_page;
    }

    public static function getDossierGETParameter() {

        $TSConfig = self::getTSConfig();
        $dossier_get_parameter = $TSConfig['newspaper.']['dossier_get_parameter'];
        if (!$dossier_get_parameter) $dossier_get_parameter = self::default_dossier_get_parameter;

        return $dossier_get_parameter;
    }

    /**
     * Get uid to use for internal preview of articles (in production list or placement module)
     * @return int uid of page
     */
    public static function getPreviewPageUid() {
        $TSConfig = self::getTSConfig();

        $previewPage = intval($TSConfig['newspaper.']['be.']['previewPageUid']);
        if (!$previewPage) {
            throw new tx_newspaper_IllegalUsageException(
                'No preview page defined. Please set newspaper.be.previewPageUid in TSConfig!'
            );
        }
        return $previewPage;
    }

    /**
     * Checks if the given $filename is located in the Typo3 upload folder for a newspaper image
     * @param $filename string Full path and filename
     * @return bool true if image is located in the newspaper image upload path, else false
     */
    public static function isNewspaperUploadPathUsed($filename) {
        t3lib_div::loadTCA('tx_newspaper_extra_image');
        $uploadPath = $GLOBALS['TCA']['tx_newspaper_extra_image']['columns']['image_file']['config']['uploadfolder'];
        return (strpos($filename, $uploadPath . '/' . basename($filename)) !== false); // @todo: this check ignores the TYPO3_path part of the $filename
    }

    /// get absolute path to Typo3 installation
    /** \param $endsWithSlash determines if the returned path ends with a slash
     *  \return absolute path to Typo3 installation
     */
    public static function getAbsolutePath($endsWithSlash=true) {

        $path = self::getBasePath();

        $path = rtrim($path, '/');
        if ($endsWithSlash) {
            $path .= '/';
        }

        return '/' . ltrim($path, '/');
    }

    public static function getBasePath() {

        /// \todo replace by a version NOT using EM conf (check t3lib_div)
        $em_conf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['newspaper']);

        if (!isset($em_conf['newspaperTypo3Path']) || !$em_conf['newspaperTypo3Path']) {
            throw new tx_newspaper_Exception('newspaperTypo3Path was not set in EM');
        }

        return trim($em_conf['newspaperTypo3Path']);
    }

    /**
     *  prepends the given absolute path part if path to check is no absolute path
     *
     *  @param string $path2check path to check if it's an absolute path
     *  @param string $absolutePath this path is prepended to $path2check; no check, if this path is absolute
     *  @return string absolute path (either absolute string was prepended or path to check was absolute already);
     *      WIN: backslashes are converted to slashes
     *  @todo: throw exception if created path does not exist???
     */
    public static function createAbsolutePath($path2check, $absolutePath) {

        // windows uses the backslash character as path delimiter - make sure slashes are used only
        $path2check = str_replace('\\', '/', $path2check);
        $absolutePath = str_replace('\\', '/', $absolutePath);

        if ($absolutePath == '')
            return preg_replace('#/+#', '/', $path2check); // nothing to prepend, just return $path2check

        if ($path2check == '')
            return preg_replace('#/+#', '/', $absolutePath); // no path to check, just return the absolute path to prepend

        $newpath = $path2check;
        // prepend absolute path, if needed
        if (TYPO3_OS == 'WIN') {
            if ($path2check[1] != ':') {
                // windows
                $newpath = $absolutePath . '/' . $path2check;
            }
        } else {
            // linux etc.
            if ($path2check[0] != '/')
                $newpath = $absolutePath . '/' . $path2check;
        }

        return preg_replace('#/+#', '/', $newpath); // remove multiple slashes
    }

    public static function getTranslation($key, $translation_file = 'locallang_newspaper.xml', $extension = 'newspaper') {
        global $LANG;

        if ($translation_file === false) { throw new tx_newspaper_IllegalUsageException('You forgot to remove the "false" parameter when refactoring!'); }
        if ($translation_file === true) { throw new tx_newspaper_IllegalUsageException('You forgot to remove the "true" parameter when refactoring!'); }

        if (!($LANG instanceof language)) {
            require_once(t3lib_extMgm::extPath('lang', 'lang.php'));
            $LANG = t3lib_div::makeInstance('language');
            $LANG->init('default');
        }

        return $LANG->sL("LLL:EXT:$extension/$translation_file:$key", false);
    }


    /**
     * Cleans HTML string (using tidy, if available)
     * @param string $text HTML string to be cleaned
     * @return string Cleaned HTML string
     */
    public static function tidyHtmlString($text) {
        if (class_exists('tidy')) {
            $tidy = new tidy();
            $text = $tidy->repairString($text, array('show-body-only' => true, 'input-encoding' => 'utf8', 'output-encoding' => 'utf8'));
        }
        return $text;
    }


    /**
     * Basic url encoding: encodes '?', '=' and '&' only
     * @param string $url URL to be encoded
     * @return string encoded URL
     */
    public static function encodeUrlBasic($url) {
        $chars = array('?', '=', '&');
        $replaceWith = array('%3F', '%3D', '%26');
        return str_replace($chars, $replaceWith, $url);
    }

    /**
     * Check if given string is UTF8 encoded
     * Thanx: http://www.php.net/manual/en/function.utf8-encode.php#82210
     * @param $string String to be checked
     * @return bool True if string is UTF8 encoded, else false
     */
    public static function isUTF8($string) {
        return (utf8_encode(utf8_decode($string)) == $string);
    }

    /**
     * Recusively convert array $arr to lowercase
     * @static
     * @param array $arr Array to be convered
     * @return array Converted array
     */
    public static function toLowerCaseArray(array $arr) {
        return self::convertCaseArray($arr, self::toLower);
    }

    /**
     * Recusively convert array $arr to uppercase
     * @static
     * @param array $arr Array to be convered
     * @return array Converted array
     */
    public static function toUpperCaseArray(array $arr) {
        return self::convertCaseArray($arr, self::toUpper);
    }

    /// Recursively convert strings in an array to case given in $type
    /**
     * @static
     * @param array $arr Array to be converted
     * @param $type Either set to self::toLower or self::toUpper ($string is returned unprocessed if another $type is
     * given)
     * @return array Converted array
     */
    private static function convertCaseArray(array $arr, $type) {
        if ($type != self::toLower && $type != self::toUpper) {
            return $arr; // no proper type, just return the given array
        }
        foreach($arr as $key => $value) {
            if (is_array($value)) {
                $arr[$key] = self::convertCaseArray($value, $type);
            } elseif (is_string($value)) {
                $arr[$key] = ($type == self::toLower)? self::toLowerCase($value) : self::toUpperCase($value);
            }
        }
        return $arr;
    }

    /**
     * Convert $string to lowercase. Uses Typo3 csConvObj to process special characters correctly.
     * @param $string String to be converted to lowercase
     * @return Converted string
     */
    public static function toLowerCase($string) {
        return self::convertCase($string, self::toLower);
    }

    /**
     * Convert $string to uppercase. Uses Typo3 csConvObj to process special characters correctly.
     * @param $string String to be converted to uppercase
     * @return Converted string
     */
    public static function toUpperCase($string) {
        return self::convertCase($string, self::toUpper);
    }

    /**
     * Converts $string to lowercase or uppercase depending on setting in $type. Uses Typo3 csConvObj to process special
     * characters correctly.
     * @static
     * @param $string String to be converted
     * @param $type Either set to self::toLower or self::toUpper ($string is returned unprocessed if another $type is
     * given)
     * @return string Converted string
     * @todo See tx_newspaper::normalizeString()
     */
    private static function convertCase($string, $type) {

        if (!is_string($string) || ($type != self::toLower && $type != self::toUpper)) {
            return $string; // nothing to do
        }

        if (TYPO3_MODE == 'BE' && !is_object($GLOBALS['TSFE'])) {
            self::buildTSFE();
            if (!is_object($GLOBALS['TSFE']) || !($GLOBALS['TSFE'] instanceof tslib_fe)) {
                return ($type == self::toLower)? strtolower($string) : strtoupper($string);
            }
        }

        $cs_converter = $GLOBALS['TSFE']->csConvObj;
        if (!$cs_converter instanceof t3lib_cs) {
            return ($type == self::toLower)? strtolower($string) : strtoupper($string);
        }

        // Fetch character set:
        $charset = $GLOBALS['TYPO3_CONF_VARS']['BE']['forceCharset']?
                $GLOBALS['TYPO3_CONF_VARS']['BE']['forceCharset'] : $GLOBALS['TSFE']->defaultCharSet;

        // Convert:
        return $cs_converter->conv_case($charset, $string, $type);
    }

    /**
     * Replacement for non-working t3lib_div::removeXSS() calls
     * @static
     * @param $string String to be checked
     * @return string String with potential XSS stuff removed
     */
    public static function removeXSS($string)	{
        require_once(PATH_typo3 . 'contrib/RemoveXSS/RemoveXSS.php');
        $removeXSS = new RemoveXSS(''); // t3lib_div::makeInstance() can't handle params ...
        return $removeXSS->RemoveXSS($string);
    }

    /**
     * Let Typo3 convert links in RTE data
     * @param string $text unconverted RTE data
     * @return string Converted RTE data
     */
    public static function convertRteField($text) {
        require_once(PATH_tslib . 'class.tslib_pibase.php');

        // prepare some Typo3 frontend object
        tx_newspaper::buildTSFE();

        /** @var $pibase tslib_pibase */
        $pibase = t3lib_div::makeInstance('tslib_pibase');
        $pibase->cObj = $GLOBALS['TSFE']->cObj;

        return $pibase->pi_RTEcssText($text);
    }

    /**
     *  Safely format an Extra's description for display in the BE, generating HTML that is always™ valid.
     */
    public static function formatDescription($title, $text, $description) {
        if (strlen($description) >= self::description_length) return substr($description, 0, self::description_length);
        if (strlen($description) + strlen($title) >= self::description_length) {
            return ($description?  "<p>$description</p>": '') .
                '<p><strong>' . substr(strip_tags($title), 0, self::description_length-strlen($description)) . '</strong></p>';
        }
        return ($description?  "<p>$description</p>": '') .
            '<p><strong>' . $title . '</strong> ' .
            substr(strip_tags($text), 0, self::description_length-strlen($description)-strlen($title)) . '</p>';
    }

    /**
     * Returns an integer from a three part version number, eg '4.12.3' -> 4012003
     * @return int Typo3 version number
     */
    public static function getTypo3Version() {
        if (class_exists('t3lib_utility_VersionNumber')) {
            return t3lib_utility_VersionNumber::convertVersionNumberToInteger(TYPO3_version);
        }
        return t3lib_div::int_from_ver(TYPO3_version);
    }


    /**
     * Gets field data of current be_user
     * @param string $field Name of field to be read
     * @return mixed $field data of logged in be_user, or false if data couldn't be fetched
     */
    public static function getBeUserData($field) {

        /** @var t3lib_beUserAuth */
        global $BE_USER;

        $field = htmlspecialchars($field);

//if ($field == 'tx_newspaper_role') { unset($BE_USER->user[$field]); } // Unset stored newspaper role for test purposes

        // Check global object BE_USER first ...
        if (isset($BE_USER->user[$field])) {
            return $BE_USER->user[$field];
        }

        // Check session then
        if ($_COOKIE['be_typo_user'] &&
            $row = tx_newspaper_DB::getInstance()->selectZeroOrOneRows(
                'ses_userid', 'be_sessions',
                'ses_id="' . $_COOKIE['be_typo_user'] . '" AND ses_name="be_typo_user"'
            )
        ) {
            // A valid backend session was found
            // So try to read field from DB directly
            tx_newspaper::devlog('sess row', $row);
            if ($row = tx_newspaper_DB::getInstance()->selectZeroOrOneRows(
                    '*', 'be_users',
                    'uid=' . intval($row['ses_userid'])
                ) && isset($row[$field])) {
                return $row[$field];
            }
        }

        return false;
    }

    /**
     * Gets the uid of current be_user
     * @return int uid of logged in be_user, or 0 if uid couldn't be fetched
     */
    public static function getBeUserUid() {
        return intval(self::getBeUserData('uid'));
    }


    ////////////////////////////////////////////////////////////////////////////

    /// Check if given class name is an abstract class
    /** \param $class class name
     *  \return \c true if abstract class, \c false else (or if no class at all)
     */
    public static function isAbstractClass($class) {
        if (!class_exists($class)) return false;

        $tmp = new ReflectionClass($class);
        return $tmp->isAbstract();
    }

  /// Return the name of the SQL table \p $class is persistently stored in
  /** \param $class either object or a class name to find the SQL table for
   *  \return The lower-cased class name of \p $class (= name of associated
   * 		db table; newspaper convention)
   */
    public static function getTable($class) {
        if (is_object($class)) return strtolower(get_class($class));
        return strtolower($class);
    }

    /// Get all child classes (but child only, no grand children etc.)
    /** Basically used to get concrete classes which extend an abstract class
     *  \param $class_name Name of class to look for child classes
     *  \return List of child classes
     */
    public static function getChildClasses($class_name) {
        if (!class_exists($class_name)) return array();
        $class_name = strtolower($class_name);
        $child_list = array();
        foreach(get_declared_classes() as $cl) {
            if (strtolower(get_parent_class($cl)) == $class_name && strtolower($cl) != $class_name)
                $child_list[] = $cl;
        }
        return $child_list;
    }

    /// Check if a given class implements a given interface
    /** \param $class PHP class to check if it implements \p $interface
     *  \param $interface PHP interface to check if it is implemented by \p $class
     *  \return true if given \p $class implentes given \p $interface
     */
    public static function classImplementsInterface($class, $interface) {
        if (!class_exists($class)) return false;

        $tmp_impl = class_implements($class);
        if (isset($tmp_impl[$interface])) return true;

        return false;
    }

    ////////////////////////////////////////////////////////////////////////////

    /// Get array based on Typo3 URL coding
    /**
     * Example: http://www.opennewspaper.org _blank cssclass titletext
     * @static
     * @param $url URL in Typo3 format (see example above)
     * @return array('href', 'target', 'css', 'title')
     */
    public static function getTypo3UrlArray($url) {
        $data = t3lib_div::unQuoteFilenames($url);
        return array(
            'href' => (strpos($data[0], ':') === false)? 'http://' . $data[0] : $data[0],
            'target' => $data[1],
            'css' => $data[2],
            'title' => $data[3]
        );
    }


    /// populate \c $GLOBALS['TSFE'] even if we're in the BE
    /** Thanks to typo3.net user semidark. Function lifted from
     *  http://www.typo3.net/forum/list/list_post//39975/?tx_mmforum_pi1[page]=&tx_mmforum_pi1[sword]=typolink%20backend%20modules#pid149544
     */
    public static function buildTSFE($force = false) {

        /** @var t3lib_timeTrack */
        global $TT;
        /** @var tslib_FE */
        global $TSFE;

        if (!defined('PATH_tslib')) { // see sysext/cms/tslib/index_ts.php
            define('PATH_tslib', PATH_typo3 . 'sysext/cms/tslib/');
        }
        require_once(PATH_t3lib . 'class.t3lib_timetrack.php');
        require_once(PATH_t3lib . 'class.t3lib_page.php');
        require_once(PATH_tslib . 'class.tslib_fe.php');
        require_once(PATH_tslib . 'class.tslib_content.php');
        require_once(PATH_tslib . 'class.tslib_pibase.php');

        $page_id = self::getPreviewPageUid();

        /* Declare */
        $temp_TSFEclassName = t3lib_div::makeInstanceClassName('tslib_fe');

          /* Begin */
        if (!is_object($TT)) {
            $TT = new t3lib_timeTrack;
            $TT->start();
        }

        if (!is_object($TSFE) || $force) {
            //*** Builds TSFE object
            $TSFE = new $temp_TSFEclassName($GLOBALS['TYPO3_CONF_VARS'],$page_id,0,0,0,0,0,0);

            //*** Builds sub objects
            $TSFE->tmpl = t3lib_div::makeInstance('t3lib_tsparser_ext');
            $TSFE->sys_page = t3lib_div::makeInstance('t3lib_pageSelect');

            //*** init template
            $TSFE->tmpl->tt_track = 0;// Do not log time-performance information
            $TSFE->tmpl->init();

            $rootLine = $TSFE->sys_page->getRootLine($page_id);

            //*** This generates the constants/config + hierarchy info for the template.

            $TSFE->tmpl->runThroughTemplates($rootLine,$template_uid);
            $TSFE->tmpl->generateConfig();
            $TSFE->tmpl->loaded=1;

            //*** Get config array and other init from pagegen
            $TSFE->getConfigArray();
            $TSFE->linkVars = ''.$TSFE->config['config']['linkVars'];

            if ($TSFE->config['config']['simulateStaticDocuments_pEnc_onlyP']) {
                foreach (t3lib_div::trimExplode(',', $TSFE->config['config']['simulateStaticDocuments_pEnc_onlyP'], 1) as $temp_p) {
                    $TSFE->pEncAllowedParamNames[$temp_p] = 1;
                }
            }
            //*** Builds a cObj
            $TSFE->newCObj();
        }
    }

    /// Get a typolink-compatible URL
    /** @param array $params Target and optional \c GET parameters. See the TSRef for
     *      details, eg. http://typo3.org/documentation/document-library/references/doc_core_tsref/4.1.0/view/5/8/
     *  @param array $conf Optional TypoScript configuration array, if present. See
     *        TSRef.
     *  @return string Generated URL
     */
    public static function typolink_url(array $params = array(), array $conf = array()) {
        $link = self::typolink('', $params, $conf);
        return $link['href'];
    }

    ////////////////////////////////////////////////////////////////////////////

    /// \return array [key]=value if $key is found in config file, emtpy array else
    public static function getNewspaperConfig($key) {

        if (!self::$newspaperConfig) {
            $newspaper_config_file = t3lib_extMgm::extPath('newspaper') . '/newspaper.conf';
            if (is_readable($newspaper_config_file)) {
                self::$newspaperConfig = parse_ini_file($newspaper_config_file);
            }
        }

        if (isset(self::$newspaperConfig[$key])) {
            return array($key => self::$newspaperConfig[$key]);
        }

        return array();

    }

    /// checks if a string starts with a specific text
    /** @param $haystack string to search
     *  @param $needle string to search for
     *  @param bool $caseSensitive specifies if the search is case-sensitive (default=false)
     */
    public static function startsWith($haystack, $needle, $caseSensitive=false) {
        $function = $caseSensitive? 'strpos': 'stripos';
        return ($function($haystack, $needle) === 0);
    }

    /**
     * Gets an array of objects with getUid() function available, creates an array with uids only
     * @param tx_newspaper_StoredObject[] $objects objects with getUid() function available
     * @return array Contains the uids of the objects
     */
    public static function getUidArray(array $objects) {
        return array_map(function($object) { return $object->getUid(); }, $objects);
    }


    /// Get a list of all the attributes/DB fields an object (or class) has
    /** \param $object An object of the desired class, or the class name as string
     *  \return The list of attributes
     */
    public static function getAttributes($object) {
        global $TCA;
        $object = self::getTable($object);
        t3lib_div::loadTCA($object);
        return array_keys($TCA[$object]['columns']);
    }

    /// The \c GET parameter which determines the article UID
    public static function GET_article() {
        return self::article_get_parameter;
    }

    /// The \c GET parameter which determines which page type is displayed
    public static function GET_pagetype() {
        return self::pagetype_get_parameter;
    }

    /// Get the tx_newspaper_Section object of the page currently displayed
    /** Currently, that means it returns the tx_newspaper_Section record which lies on the current Typo3 page.
     *  This implementation may change, but this function is required to always return the correct tx_newspaper_Section.
     *
     *  @return tx_newspaper_Section Section the plugin currently works on
     *  @throw tx_newspaper_IllegalUsageException if the current page is not associated with a tx_newspaper_Section.
     */
    public static function getSection() {
        $section_uid = intval($GLOBALS['TSFE']->page['tx_newspaper_associated_section']);

        if (!$section_uid) {
            throw new tx_newspaper_IllegalUsageException('No section associated with current page');
        }

        return new tx_newspaper_Section($section_uid);
    }

    /// Find out whether we are displaying a tx_newpaper_Article right now
    /** @return true, if on an Article tx_newspaper_Page */
    public static function onArticlePage() {
        $pagetype = new tx_newspaper_PageType($_GET);
        return $pagetype->getAttribute('is_article_page');
    }

    public static function currentURL() {
        $baseURI = explode($_SERVER['SERVER_NAME'], t3lib_div::getIndpEnv('TYPO3_REQUEST_URL'));
        return self::currentProtocolHost() . $baseURI[1];
    }

    /// @return current protocol and host
    public static function currentProtocolHost() {
        return 'http' . ($_SERVER['HTTPS']? 's': '') . '://' . $_SERVER['SERVER_NAME'];
    }

    public static function registerSource($key, tx_newspaper_Source $new_source) {
        self::$registered_sources[$key] = $new_source;
    }

    public static function getRegisteredSources() {
        return self::$registered_sources;
    }

    /**
     * Get an array of sources the current BE user can access
     * See User TSConfig newspaper.accessSources
     * @return array Sources
     */
    public static function getRegisteredSourcesWithRestrictions() {
        $sources = array();
        $allowedSources = self::getAllowedSourceNames();
        foreach(self::getRegisteredSources() as $key => $source) {
            if (in_array($key, $allowedSources)) {
                $sources[$key] = $source;
            }
        }
        return $sources;
    }

    public static function getRegisteredSource($key) {
        if (!isset(self::$registered_sources[$key])) {
            throw new tx_newspaper_InconsistencyException(
                "Requested source '$key' not present in registered sources: " . print_r(self::$registered_sources, 1)
            );
        }
        return self::$registered_sources[$key];
    }

    /**
     * Return an array of allowed registered sources names (names are strings (!), not source objects)
     * Either all sources or sources restricted using User TSConfig:
     * newspaper.accessSources = [comma separated list of source names]
     * @static
     * @return Array with source names OR false, if no be_user is available
     */
    public static function getAllowedSourceNames() {
        if (!isset($GLOBALS['BE_USER'])) {
            return false; // Doesn't make sense without a backend user ...
        }

        // Check User TSConfig setting
        if ($tsc = $GLOBALS['BE_USER']->getTSConfigVal('newspaper.accessSources')) {
            return t3lib_div::trimExplode(',', $tsc); // Return ALLOWED sources
        }

        // Read all registered sources, no TSConfig found ...
        $sources = array();
        foreach(self::getRegisteredSources() as $key => $source) {
            $sources[] = $key; // Just add the source name
        }
        return $sources; // Return ALL sources
    }


    public static function registerSaveHook($class) {
        self::$registered_savehooks[] = $class;
    }

    public static function clearSaveHooks() {
        self::$registered_savehooks = array();
    }

    public static function getRegisteredSaveHooks() {
        return self::$registered_savehooks;
    }

    // Register $extKey so extKey's tca.php is loaded after newspaper/tca_php_addon.php is loaded
    public static function registerSubTca($extKey) {
        if (t3lib_extMgm::isLoaded($extKey)) {
            self::$registeredSubTca[] = $extKey;
        }
    }

    /// Load tca.php from all registered extensions (so modifications are visible in newspaper)
    public static function loadSubTca() {
        foreach(self::$registeredSubTca as $extKey) {
            $file = PATH_typo3conf . 'ext/' . $extKey . '/tca.php';
            if (is_readable($file)) {
                require_once $file;
            }
        }
    }

    public static function checkAtLeastPHPVersion($version_string, $message) {
        if (!tx_newspaper::isAtLeastPHPVersion($version_string)) {
            throw new tx_newspaper_IllegalUsageException(
                "$message needs at least PHP $version_string. You are running PHP " .
                PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION . '.' . PHP_RELEASE_VERSION
            );
        }
    }
    
    public static function isAtLeastPHPVersion($version_string) {
        return (strnatcmp(phpversion(), $version_string) >= 0);
    }

    ////////////////////////////////////////////////////////////////////////////

    /**
     *  Ensure nobody instantiates a tx_newspaper object.
     */
    private function __construct() { }

    /// Create a HTML link with text and URL using the \c typolink() API function
    /** \param  $text the text to be displayed
     *  \param  $params target and optional \c GET parameters as parameter => value
     *  \param  $conf optional TypoScript configuration array
     *  \return array ['text'], ['href']
     */
    private static function typolink($text, array $params = array(), array $conf = array()) {
        if (TYPO3_MODE == 'BE') {
            self::buildTSFE();
            if (!is_object($GLOBALS['TSFE']) || !($GLOBALS['TSFE'] instanceof tslib_fe)) {
                throw new tx_newspaper_Exception('Tried to generate a typolink in the BE. Could not instantiate $GLOBALS[TSFE]. Have to give up, sorry.');
            }
        }

        self::makeLocalCObj();

        self::flattenParamsArray($params);

        $temp_conf = self::makeTSConfForTypolink($params, $conf);

        //  call typolink_URL() and return data
        return array(
            'text' => $text,
            'href' => self::$local_cObj->typolink_URL($temp_conf)
        );
    }

    ///  A tslib_cObj object is needed to call the typolink_URL() function
    private static function makeLocalCObj() {
        if (self::$local_cObj) return;
        self::$local_cObj = t3lib_div::makeInstance("tslib_cObj");
        self::$local_cObj->setCurrentVal($GLOBALS["TSFE"]->id);
    }

    /// Make sure \p $params is a one-dimensional array
    private static function flattenParamsArray(array &$params) {
        foreach ($params as $key => $param) {
            if (is_array($param)) {
                foreach ($param as $subkey => $value) {
                    $params[$key.'['.$subkey.']'] = $value;
                }
                unset($params[$key]);
            }
        }
    }

    /// set TypoScript config array - yes I know, it's not TSConfig! It's a TS config.
    private static function makeTSConfForTypolink(array $params, array $conf) {

        if ($conf) $temp_conf = $conf;
        else $temp_conf = array();

        if ($params['id']) $temp_conf['parameter'] = $params['id'];
        else $temp_conf['parameter.']['current'] = 1;
        unset($params['id']);

        $no_cache = false;
        $sep = '&';
        if (sizeof($params) > 0) {
            foreach ($params as $key => $value) {
                if ($key == 'no_cache' && $value != 0) $no_cache = true;
                if ($key != 'cHash') {
                    $temp_conf['additionalParams'] .= "$sep$key=$value";
                    $sep = '&';
                }
            }
        }
        if (!$no_cache) $temp_conf['useCacheHash'] = 1;

        return $temp_conf;
    }

    /**
     * Gets plain and unprocessed "Typoscript" for newspaper (so no Typoscript functionality can be used!)
     * Fetches the Typoscript part that starts with newspaper.
     *
     * Example 1:
     * newspaper.testCss = class123
     *
     * Conditions are working!
     * Example 2:
     * newspaper.dummy = Some dummy text ...
     * [globalVar = BE_USER|user|uid = 1]
     *   newspaper.dummy = Value modified within Typoscript condition
     * [GLOBAL]
     *
     * @return array Typoscript array for "newspaper."
     */
    public static function getNewspaperTyposcript() {
       if (!isset($GLOBALS['TSFE']) || !is_object($GLOBALS['TSFE']) ||
           !($GLOBALS['TSFE'] instanceof tslib_fe) || !isset($GLOBALS['TSFE']->tmpl->setup['newspaper.'])) {
           return array(); // Typoscript couldn't be fetched, so return empty array
       }
       return $GLOBALS['TSFE']->tmpl->setup['newspaper.'];
   }


    /** @var tslib_cObj Used to generate typolinks */
    private static $local_cObj = null;

    private static $registered_sources = array();

    private static $registered_savehooks = array();

    private static $registeredSubTca = array();

    private static $newspaperConfig = null;

}

?>

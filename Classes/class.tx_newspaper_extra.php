<?php

require_once(PATH_typo3conf . 'ext/newspaper/interfaces/interface.tx_newspaper_extraiface.php');
require_once(PATH_typo3conf . 'ext/newspaper/interfaces/interface.tx_newspaper_writeslog.php');

/**
 *  \file class.tx_newspaper_extra.php
 *
 *  \author Oliver Schroeder <newspaper@schroederbros.de>
 *  \date Dec 12, 2008
 */

/// An Extra for the online newspaper
/** This is an abstract class which implements most of the methods defined in
 *  interface tx_newspaper_ExtraIface, except those that must be overridden in a
 *  concrete Extra anyway. All Extras (except tx_newspaper_Article and
 *  tx_newspaper_PageZone) inherit from this class.
 *
 *  Generic functions which should be overridden:
 *  - __construct()
 *  - render()
 *  - getModuleName()
 *
 *  \par Technical notes
 *     Technically, every Extra consists of two parts:
 *  -# The data for the \em concrete Extra, an object of a class derived from
 *     tx_newspaper_Extra, which are stored in a SQL table associated with the
 *     concrete Extra class.
 *  -# Data fields which are the same for every implementation of Extra,
 *     associated with the class tx_newspaper_Extra and stored in the SQL table
 *     \c tx_newspaper_extra. These data are called the \em abstract portion of
 *     an Extra.
 *
 *  \par
 *  Whenever the words \em "abstract" or \em "concrete" appear throughout the
 *  documentation, they refer to these aspects of an Extra.
 *  Inside the object, the member variable \c $uid stores the UID of the
 *  concrete record and \c $extra_uid the UID of the abstract record. The
 *  attributes for the concrete and abstract records are stored in the arrays
 *  \c $attributes and \c $extra_attributes, respectively.
 *
 *  \par
 *  For the \ref placement of particular Extras on Page Zones it is essential
 *  that the Extra on the current Page Zone knows from which original Extra it
 *  is inherited. For that end, the so called Origin UID is stored with every
 *  Extra (in the abstract portion of the Extra). It contains the abstract UID
 *  of the Extra at the top of the inheritance hierarchy. If it is zero or equal
 *  to the abstract UID of the current Extra, this Extra starts the inheritance
 *  hierarchy.
 *
 *  \par Creating Extra implementations
 *  To implement an Extra for the newspaper extension you have to follow these
 *  steps:
 *  - <b>Creating the SQL table:</b> You must define a SQL table used by your
 *    extension that stores the persistent data for your Extra. The table must
 *    have the same name as the PHP class implementing the Extra, but lowercased.
 *    For example, the table for the Extra class tx_newspaper_Extra_Image is
 *    called \c tx_newspaper_extra_image. \n
 *    The SQL table can be created using the Typo3 Extension Kickstarter. In
 *    fact, using the Kickstarter is highly recommended.
 *  - <b>Creating the PHP class:</b> This generates the business logic of your
 *    Extra.
 *    - Implementing the render() function: render() provides the core
 *      functionality for the Extra. It displays the values, that are stored in
 *      the DB for every Extra record, in the frontend. An implementation of
 *      render() typically consists of three steps:
 *      - Call prepare_render() to initialize the tx_newspaper_Smarty rendering
 *        engine and assign the attributes as Smarty variables.
 *      - Assign additional variables to the tx_newspaper_Smarty rendering
 *        engine, if needed.
 *      - call \c fetch() on \c $this->smarty, thus rendering the Extra, and
 *        return the obtained string.
 *      For examples on how to create and use a tx_newspaper_Smarty template,
 *      please refer to concrete Extra implementations, e.g.
 *      class.tx_newspaper_extra_image.php and tx_newspaper_extra_image.tmpl.
 *    - Implementing getTitle(), getDescription() and getModuleName(): These
 *      functions are used to make the Extra recognizable for the backend user.
 *      tx_newspaper_Extra provides default implementations for each of them.
 *      - getTitle() should be overridden if you provide your Extra as part of
 *        a Typo3 extension other than tx_newspaper (which will usually be the
 *        case). It provides a user-understandable name for the Extra. For an
 *        example using the Typo3 internationalization API, look at the default
 *        implementation in tx_newspaper_Extra.
 *      - getDescription() provides a description for an individual Extra, to
 *        make it recognizable in the backend. It makes sense to print some
 *        distinguishing attributes of this Extra class (such as image file name,
 *        text box title etc.)
 *      - getModuleName() determines the name of the Typo3 System Folder where
 *        Extras of this type are stored. Choose a unique string starting with
 *        the letters \c "np_".
 *  - <b>Registering the PHP class:</b> For the new Extra to appear in
 *    newspaper's menus, a registration must be performed. Preferrably in the
 *    PHP file defining the Extra class, include a call to
 *    \c tx_newspaper_Extra::registerExtra(). For example: \code
 *    tx_newspaper_Extra::registerExtra(new tx_newspaper_Extra_Image());
 *    \endcode
 *  - <b>Including the class definition:</b> Because neither Typo3 nor the
 *    newspaper extension uses autoloading at this point (Typo3 4.2), the class
 *    definition for the new Extra class must be \c include() 'd manually. The
 *    best way to do that is from the \c ext_localconf.php for your Typo3
 *    extension.
 */
abstract class tx_newspaper_Extra implements tx_newspaper_ExtraIface, tx_newspaper_WritesLog {

    /// Create a tx_newspaper_Extra
    /** Only the UID of the corresponding DB record is set. All attributes are
     *  only read if and when they are needed.
     */
    public function __construct($uid) {
        $this->setUid($uid);
    }

    /// Creates a new reference to a concrete Extra ("shallow copy" in the DB)
    /** To clone an Extra, the concrete portion is left the same but the
     *  abstract record is written anew.
     *
     *  (I don't know whether "shallow copy" is the right term for this when
     *  dealing with DBs, but the concept is the same.)
     */
    public function __clone() {

        /// Read Typo3 fields to copy into extra table
        $row = tx_newspaper::selectOneRow(
            implode(', ', self::$fields_to_copy_into_extra_table),
            $this->getTable(),
            'uid = ' . $this->getUid()
        );

        /// Write the uid and table into extra table, with the values read above
        $row['extra_uid'] = $this->getUid();
        $row['extra_table'] = $this->getTable();
        /// \c tstamp is set to now
        $row['tstamp'] = time();

        $this->setExtraUid(tx_newspaper::insertRows(self::$table, $row));
    }

    /// Convert object to string to make it visible in stack backtraces, devlog etc.
    public function __toString() {
        try {
            $this->getAttribute('uid');
        } catch (tx_newspaper_Exception $e) {
            return 'Uh-oh. __toString() led to an exception of type ' . get_class($e) .
                ' for ' . get_class($this) . '-object with UID ' . $this->getUid();
        }
        return get_class($this) . '-object: UID ' . $this->getUid() .
            ', Extra UID: ' . $this->getExtraUID() .
            ', origin UID: ' . $this->getAttribute('origin_uid')
#               'attributes: ' . print_r($this->attributes, 1)
             . "\n";
    }

    public function getTitle() {
        $title = tx_newspaper::getTranslation('title_' . $this->getTable());
        if (!$title) {
            $title = $this->getTable(); // fallback
        }
        return $title;
    }

    /// Makes a deep copy of a tx_newspaper_Extra in the DB
    /** Copies the concrete portion of an tx_newspaper_Extra as well as giving
     *  it a new abstract record.
     *
     *  (I don't know whether "deep copy" is the right term for this when
     *  dealing with DBs, but the concept is the same.)
     */
    public function duplicate() {
        $this->getAttribute('uid');            /// Read attributes from DB

        /// Copy concrete extra data
        $temp_attributes = $this->attributes;

        //    Make sure the Extra is stored in the correct SysFolder
        $temp_attributes['pid'] = tx_newspaper_Sysfolder::getInstance()->getPid($this);

        //    clear UID so a new entry can be written
        unset ($temp_attributes['uid']);

        //    Write data for concrete Extra
        $uid = tx_newspaper::insertRows($this->getTable(), $temp_attributes);

        /// Manually copy all extra attributes instead of writing them automatically
        $temp_extra_attributes = $this->extra_attributes;
        $temp_extra_attributes['extra_uid'] = $uid;
        unset ($temp_extra_attributes['uid']);

        //    Write data for abstract Extra
        $extra_uid = tx_newspaper::insertRows(self::$table, $temp_extra_attributes);

        $that = tx_newspaper_Extra_Factory::getInstance()->create($extra_uid);

        // set origin uid to extra uid so the duplicated extra can be identified
        // as a NEW non-referenced and non-inherited extra
        $that->setAttribute('origin_uid', $extra_uid);
        $that->store();

        return $that;
    }

    /// Pass variables that are used in rendering from outside the Extra
    /** The situation can occur that an Extra must render values about which it
     *  does not know - e.g. its position in a container. The entity which calls
     *  \c render() on \c $this must call \c assignSmartyVar() before calling
     *  \c render().
     *
     *  \param $variables Array (variable name => value)
     */
    public function assignSmartyVar(array $variables) {
        if (!$this->smarty) $this->smarty = new tx_newspaper_Smarty();
        foreach ($variables as $key => $value) $this->smarty->assign($key, $value);
    }

    /// Prepare for rendering. Should be called in every reimplementation of render().
    /** This function initializes the tx_newspaper_Smarty object and sets the
     *  correct template search path. It also ensures that all attributes to the
     *  tx_newspaper_Extra are read from DB and passed to smarty as smarty
     *  variables \c $attributes and \c $extra_attributes.
     *
     *  \param template_set The template set used to render.
     */
    protected function prepare_render(&$template_set = '') {
        $this->configureSmarty($template_set);

        $this->readAttributes();
        $this->smarty->assign('attributes', $this->convertRteFields($this->attributes));
        $this->smarty->assign('extra_attributes', $this->extra_attributes);

        $this->smarty->assign('extra', $this);
    }

    private function configureSmarty($template_set) {
        if (!$this->smarty) $this->smarty = new tx_newspaper_Smarty();
        $this->setTemplateSet($template_set);
        $this->setSmartyContext();
    }

    private function setSmartyContext() {
        $pagezone = $this->getPageZone();
        if ($pagezone instanceof tx_newspaper_PageZone) {

            $this->smarty->setPageZoneType($pagezone);

            $page = $pagezone->getParentPage();
            if ($page instanceof tx_newspaper_Page) {
                $this->smarty->setPageType($page);
            } else {
                self::isLoggingOfBadPagezonesSet() && tx_newspaper::devlog('no page', array($page, $pagezone));
            }
        } else {
            self::isLoggingOfBadPagezonesSet() && tx_newspaper::devlog('no pagezone', 'extra: ' . $this->getExtraUid());
        }
    }

    /**
     *  If the current Extra is contained in another Extra, return that Extra. Otherwise, null.
     *  @return tx_newspaper_Extra
     */
    private function getContainerExtra() {
#        $container_extra_classes = array_filter(get_declared_classes(), function(
        return null;
    }

    /// Check whether to use a specific template set.
    private function setTemplateSet($template_set) {
        if ($this->getAttribute('template_set')) {
            $template_set = $this->getAttribute('template_set');
        }

        if ($template_set) {
            $this->smarty->setTemplateSet($template_set);
        }
    }

    private function readAttributes() {
        if (!$this->extra_attributes) {
            $this->extra_attributes = $this->getExtraUid() ?
                tx_newspaper::selectOneRow('*', 'tx_newspaper_extra', 'uid = ' . $this->getExtraUid()) :
                array();
        }
        if (!$this->attributes) {
            $this->attributes = tx_newspaper::selectOneRow(
                '*', $this->getTable(), 'uid = ' . $this->getUid()
            );
        }
    }

    private static function isLoggingOfBadPagezonesSet() {
        return false;
    }

    protected function getSmartyTemplate() {

        try {
            $template = trim($this->getAttribute('template'));
        } catch (tx_newspaper_WrongAttributeException $e) { }

        if (empty($template)) {
            return $this;
        }

        if (strpos($template, '.tmpl') === false) {
            $template .= '.tmpl';
        }
        return $template;

    }

    /// Convert RTE data in data fields (=attributes) to HTML data
    private function convertRteFields() {

        // make sure TCA is available
        t3lib_div::loadTCA($this->getTable());

        // converting RTE fields (setting links correctly)
        $attributes = array();
        foreach($this->attributes as $field => $value) {
            if ($GLOBALS['TCA'][$this->getTable()]['columns'][$field]['config']['wizards']['RTE']) {
                // so it's an RTE field
                $attributes[$field] = tx_newspaper::convertRteField($value);
            } else {
                // no RTE field
                $attributes[$field] = $value;
            }
        }
        return $attributes;
    }

    ///    Default implementation of the render() function
    /** \todo Remove before launch - or shouldn't we?
     */
    public function render($template_set = '') {
        $this->prepare_render($template_set);
        return '<p>' .
               get_class($this) . '::render() not yet implemented - called ' .
               get_class() . '::render(' . $template_set . ')' . "</p>\n" .
               '<p>' . print_r($this->attributes, 1) . "</p>\n";
    }

    public function getAttribute($attribute) {

        if (!$this->extra_attributes) {
            $this->readExtraAttributes();
        }
        if (!$this->attributes) {
            $this->attributes = tx_newspaper::selectOneRow(
                '*', $this->getTable(), 'uid = ' . $this->getUid()
            );
        }
         if (array_key_exists($attribute, $this->extra_attributes)) {
             return $this->extra_attributes[$attribute];
         }
         if (array_key_exists($attribute, $this->attributes)) {
             return $this->attributes[$attribute];
         }

        throw new tx_newspaper_WrongAttributeException(
            $attribute, array('attributes' => $this->attributes, 'extra_attributes' => $this->extra_attributes));
    }

    /**
     * Converts RTE codes to HTML for the requested attribute
     * @param $attribute Attribute of the Extra
     * @return Attribue value with RTE codes converted to HTML
     */
    public function getAttributeRte($attribute) {
        return tx_newspaper::convertRteField($this->getAttribute($attribute));
    }

    private function readExtraAttributes() {
        if (!$this->getExtraUid()) {
            $this->extra_attributes = array();
        } else {
            $this->extra_attributes = tx_newspaper::selectOneRow('*', 'tx_newspaper_extra', 'uid = ' . $this->getExtraUid());

        }

    }

    public function setAttribute($attribute, $value) {

        if (!$this->extra_attributes) {
            $this->readExtraAttributes();
        }
        if (!$this->attributes) {
            if ($this->getUid()) {
                $this->attributes =    tx_newspaper::selectOneRow(
                    '*', $this->getTable(), 'uid = ' . $this->getUid());
            } else {
#                $this->attributes =    tx_newspaper::makeArrayFromFields($this->getTable());
                $this->attributes =    array();
            }
        }

        /** Because we separated the attributes for the concrete Extra and the
         *  abstract superclass in two arrays, it is not trivial to decide where
         *  the new attribute should be set - in particular if we created an
         *  empty object and want to set a new attribute.
         *
         *  The logic goes thus. If an attribute exists in either array, set it
         *  there (if it exists in both, set it in both). Else assume it is
         *  meant for the concrete Extra.
         *
         *  This is consistent with the assumption made in store(), that the
         *  concrete record must exist before abstract attributes can be written.
         */
        if (array_key_exists($attribute, $this->extra_attributes)) {
            $this->extra_attributes[$attribute] = $value;
        } else {
            $this->attributes[$attribute] = $value;
        }

    }

    /// Checks TCA to set attribute to default value, if a default value is set
    // \todo: currently only checkboxes are processed (see http://typo3.org/documentation/document-library/core-documentation/doc_core_api/4.1.0/view/4/2/ for other types)
    public function setDefaultValues() {
        t3lib_div::loadTCA($this->getTable()); // make sure TCA for this extra is available

        foreach($GLOBALS['TCA'][$this->getTable()]['columns'] as $fieldName => $fieldData) {
//t3lib_div::devlog('setDefaultValues()', 'newspaper', 0, array('fieldName' => $fieldName, 'fieldData' => $fieldData));

            switch($fieldData['config']['type']) {
                case 'check':
                    if ($fieldData['config']['default'] == 1) {
                        $this->setAttribute($fieldName, 1);
                    }
                break;
            }

        }
    }

    /// A short description that makes an Extra uniquely identifiable in the BE
    /** This function should be overridden in every class that can be pooled, to
     *  provide the BE user a way to find an Extra to create a new Extra from.
     */
    public function getDescription() {
        //    default implementation
        return ($this->getAttribute('short_description'))?
            $this->getAttribute('short_description') :
            $this->getTitle() . ' ' . $this->getUid();
    }

    /**
     * Get backend title for an abstract extra
     * @return string Content of field "short_description" in concrete Extra or concrete table name and uid if field "short_description" is empty.
     */
    public function getAbstractExtraLabel() {
        if (!$title = $this->getAttribute('short_description')) {
            $title = $this->getTable() . ' #' . $this->getUid() . ' (#' . $this->getExtraUid() . ')';
        }
        return $title;
    }

    public function deleteIncludingReferences() {
        /// Find abstract records linking to the concrete Extra
        $uids = tx_newspaper::selectRows(
            'uid', self::$table,
            'extra_table = ' . $GLOBALS['TYPO3_DB']->fullQuoteStr($this->getTable(), $this->getTable()) .
            ' AND extra_uid = ' . $this->getUid());

        foreach ($uids as $uid) {
            /// Delete entries in association tables linking to abstract record
            tx_newspaper::deleteRows(
                tx_newspaper_Article::getExtra2PagezoneTable(),
                'uid_foreign = ' . intval($uid['uid'])
            );
            tx_newspaper::deleteRows(
                tx_newspaper_PageZone_Page::getExtra2PagezoneTable(),
                'uid_foreign = ' . intval($uid['uid'])
            );

            /// Delete the abstract record
            tx_newspaper::deleteRows(self::$table, 'uid = ' . intval($uid['uid']));
        }

        /// delete the concrete record
        tx_newspaper::deleteRows($this->getTable(), 'uid = ' . $this->getUid());
    }

    /// Lists Extras which are in the pool of master copies for new Extras.
    /** \return Array of pooled Extras or \c false if pool option isn't
     *  available for this Extra.
     */
    public function getPooledExtras() {
        try {
            $uids = tx_newspaper::selectRows(
                'uid', $this->getTable(), 'pool', '', 'crdate DESC'
            );
        } catch (tx_newspaper_DBException $e) {
            return false;
        }

        $pooled_extras = array();
        foreach ($uids as $uid) {
            $class = $this->getTable();
            $pooled_extras[] = new $class(intval($uid['uid']));
        }
        return $pooled_extras;
    }

    /// The fields which are searched for in fulltext searches for extras
    /** Non-static because static functions do not allow late binding.
     */
    public function getSearchFields() {
        return array('short_description');
    }

    /// Get Extras available for given search term
    /** \param $search_term String to be searched for
     *  \param $hidden Determines if hidden Extras should be taken into account
     *                 (they are if set to true)
     *  \return Array of Extras according to filter settings
     */
    public function getSearchResults($search_term = '',
                                     $offset = 0, $number = 10, $hidden = false,
                                     $order_by = 'tstamp DESC') {

        $table = $this->getTable();

        $uids = tx_newspaper::selectRowsDirect(
            'uid',
            $table,
            $this->getSeachWhere($search_term, $hidden),
            '',
            $order_by,
            "$offset, $number"
        );

        $extras = array();
        foreach ($uids as $uid) {
            $extras[] = new $table($uid['uid']);
        }

        return $extras;
    }

    /// Count Extras available for given search term
    /** \param $search_term String to be searched for
     *  \param $hidden Determines if hidden Extras should be taken into account
     *                 (they are if set to true)
     *  \return Number of Extras found
     */
    public function countSearchResults($search_term='', $hidden=false) {
        $row = tx_newspaper::selectRowsDirect(
            'COUNT(*) AS c',
            $this->getTable(),
            $this->getSeachWhere($search_term, $hidden)
        );
        return $row[0]['c'];
    }

    /// Get where part SQL statement to select Extras
    /** \param $search_term String to be searched for
     *  \param $hidden Determines if hidden Extras should be taken into account
     *                 (they are if set to true)
     *  \return Part to be used a WHERE part in an SQL statement
     */
    private function getSeachWhere($search_term='', $hidden=false) {

        if ($search_term && $search_where_parts = $this->getSearchFields()) {
            foreach ($search_where_parts as $key => $field) {
                $search_where_parts[$key] = $field . ' LIKE \'%' . $search_term . '%\'';
            }

            $search_where = implode(' OR ', $search_where_parts);
            if ($search_where) {
                // set parantheses, so following "AND" can be used correctly
                $search_where = '(' . $search_where . ')';
            }
        } else {
            $search_where = '1';
        }

        $search_where .= ' AND NOT deleted';

        if (!$hidden && tx_newspaper_DB::getInstance()->fieldExists($this->getTable(), 'hidden')) {
            $search_where .= ' AND NOT hidden';
        }

        return $search_where;

    }


    /// Checks if a tx_newspaper_Extra is registered.
    /** \param $extra tx_newspaper_Extra of the type to be checked.
     *  \return \c true if \p $extra is registered (else \c false).
     */
    public static function isRegisteredExtra(tx_newspaper_Extra $extra) {
        for ($i = 0; $i < sizeof(self::$registeredExtra); $i++) {
            if ($extra->getTable() == self::$registeredExtra[$i]->getTable())
                return true;
        }
        return false;
    }

    /// Register a tx_newspaper_Extra.
    /** Every class derived from tx_newspaper_Extra has to register to be used.
     *
     * \param $extra A concrete tx_newspaper_Extra object.
     * \return \c true if this tx_newspaper_Extra class was registered
     *      successfully or \c false if the class was registered already.
     */
    public static function registerExtra(tx_newspaper_Extra $extra) {
        if (!self::isRegisteredExtra($extra)) {
            self::$registeredExtra[] = $extra; // add this Extra to list
            return true;
        }
        return false;
    }

    /// Get sorted list of registered tx_newspaper_Extra classes.
    /** \return Array with (registered) Extra objects (\em not class names).
     */
    static public function getRegisteredExtras() {

        // get extra titles (localized)
        $extrasUnsorted = array();
        foreach(self::$registeredExtra as $extra) {
            $extrasUnsorted[$extra->getTable()] = $extra->getTitle();
        }

        // sort extras
        natcasesort($extrasUnsorted);

        // create sorted array
        $extras = array();
        foreach($extrasUnsorted as $extraClassname => $title) {
            $extras[] = new $extraClassname();
        }

        return $extras;
    }


    /// Get list of registered Extra that are not hidden in TSConfig
    /**
     * TSConfig: newspapre.be.hideExtra.[class name] = value
     * Value  0: don't hide (default)
     * Value -1: always hide
     * Value > 0: Bitmask, see newspaper TSConofig documentation
     * \param $type
     * \return Array Allowed extras
     */
    static public function getAllowedExtras($type) {

        $type = intval($type);

        $extras = array();
        foreach(self::getRegisteredExtras() as $extra) {

            // read user tsconfig configuration for current extra
            $perm = $GLOBALS['BE_USER']->getTSConfigVal('newspaper.be.hideExtra.' . $extra->getTable());

            if ($perm == self::HIDE_NOWHERE || (($type != self::HIDE_EVERYWHERE) && !($type & $perm))) {
                // add to array (either HIDE_NOWHERE is configured or the given $type matches the $perm bitmask)
                $extras[] = $extra;
            }
//if($perm) t3lib_div::debug(array('table' => $extra->getTable(), 'type' => $type, 'perm' => $perm, 'bitmask check' => ($type & $perm)));
        }
        return $extras;
    }


    /// The module name for the newspaper sysfolder
    /** For the base class tx_newspaper_Extra this is set to the default folder
     *  for data associated with newspaper etxension.
     *
     *  Overwrite this function in conrete tx_newspaper_Extra implementations.
     *
     *  \return Value for the \c tx_newspaper_module field in the \c pages table
     *      of the SysFolder which stores objects of this class.
     */
    public static function getModuleName() { return 'np_extra_default'; }

    ///  PID of SysFolder to store tx_newspaper_Extra records in.
    /** \return PID of Typo3 SysFolder to store tx_newspaper_Extra records in.
     */
    public function getSysfolderPid() {
        return tx_newspaper_Sysfolder::getInstance()->getPid($this);
    }

    public function getTable() {
        return tx_newspaper::getTable($this);
    }

    public function store() {

        if ($this->getUid()) {
            $this->updateExisting();
        } else {
            $this->insertNew();
        }

        return $this->getUid();
    }

    private function insertNew() {

        if ($this->extra_attributes) {
            throw new tx_newspaper_InconsistencyException(
                'Attributes for abstract Extra have been set before a concrete Extra exists. ' .
                print_r($this->extra_attributes, 1)
            );
        }

        tx_newspaper::setDefaultFields($this, array('crdate', 'tstamp', 'pid', 'cruser_id'));

        //    Write data for concrete Extra
        $this->setUid(
            tx_newspaper::insertRows(
                $this->getTable(), $this->attributes
            )
        );
        if (!$this->getUid())
            t3lib_div::debug(tx_newspaper_DB::getQuery());
        //    Write data for abstract Extra
        $this->setExtraUid(
            self::createExtraRecord($this->getUid(), $this->getTable())
        );
    }

    private function updateExisting() {

        $this->getAttribute('uid');

        tx_newspaper::setDefaultFields($this, array('tstamp'));
        tx_newspaper::updateRows(
            $this->getTable(), 'uid = ' . $this->getUid(), $this->attributes
        );
        tx_newspaper::updateRows(
            'tx_newspaper_extra',
            'uid = ' . $this->getExtraUid(),
            $this->extra_attributes
        );
    }

    /// Read data of tx_newspaper_Extra
    /** \param $uid uid of record in given table
     *  \param $table name of table (f.ex \c tx_newspaper_extra_image)
     *  \return Array row with Extra data for given uid and table
     *  \todo explain why this is needed.
     */
    public static function readExtraItem($uid, $table) {
        if (!$uid) return array();

        return tx_newspaper::selectOneRow('*', $table, 'uid = ' . intval($uid));
    }

    /// Create the record for a concrete tx_newspaper_Extra in the table of abstract Extras
    /** This is probably necessary because a concrete tx_newspaper_Extra has been freshly
     *  created.
     *
     *  By default, does nothing if the concrete Extra is already linked in the
     *  abstract table. That way createExtraRecord() can be called on already
     *  existing Extras with no effect.
     *
     *  If $force is set, creates an entry in the abstract table anyway. This is
     *  useful for making new references to already existing Extras.
     *
     *  \param $uid UID of the Extra in the table of concrete Extras
     *  \param $table Table of concrete Extras
     *  \param $force If set, create an abstract record in any case.
     *  \return UID of abstract Extra record
     */
    public static function createExtraRecord($uid, $table, $force = false) {
        if (!$force) {
            /// Check if record is already present in extra table
            $row = tx_newspaper::selectZeroOrOneRows(
                'uid', self::$table,
                'extra_table = ' . $GLOBALS['TYPO3_DB']->fullQuoteStr($table, $table) .
                ' AND extra_uid = ' . intval($uid)
            );
            if ($row['uid']) {
                return $row['uid'];
            }
        }

        /// Read Typo3 fields to copy into extra table
        $row = tx_newspaper::selectOneRow(
            implode(', ', self::$fields_to_copy_into_extra_table),
            $table,
            'uid = ' . intval($uid)
        );

        /// Write the uid and table into extra table, with the values read above
        $row['extra_uid'] = $uid;
        $row['extra_table'] = $table;
        $row['tstamp'] = time();                /// \c tstamp is set to now

        return tx_newspaper::insertRows(self::$table, $row);
    }

    public function getUid() { return intval($this->uid); }
    public function setUid($uid) {
        $this->uid = $uid;
        if ($this->attributes) $this->attributes['uid'] = $uid;
    }

    /// Assign a new UID to the abstract portion of the tx_newspaper_Extra.
    /** \param $uid New UID for the abstract record
     */
    public function setExtraUid($uid) {
        $this->extra_uid = $uid;
        if ($this->extra_attributes) $this->extra_attributes['uid'] = $uid;
    }

    /// Get UID for the abstract portion of the tx_newspaper_Extra.
    /** \attention This function is only public so unit tests can access it.
     *  \return UID for the abstract portion of the tx_newspaper_Extra.
     */
    public function getExtraUid() {
        if (!$this->extra_uid) {
            if (!$this->getUid()) {
                return 0;
#                t3lib_div::debug('tried to call getExtraUid() before a record was written');
#                t3lib_div::debug(debug_backtrace());
            }
            $this->extra_uid = self::createExtraRecord($this->getUid(), $this->getTable());
        }
        return intval($this->extra_uid);
    }

    /// Gets the origin uid of a tx_newspaper_Extra.
    /** \todo Explain origin UIDs! Either here or in the class description.
     *  \return int the origin uid of an extra (if 0 return abstract extra uid)
     */
    public function getOriginUid() {
        $origin = intval($this->getAttribute('origin_uid'));
        if ($origin) return $origin;
        return intval($this->getExtraUid());
    }

    public function setOriginUid($origin_uid) {
        $this->readExtraAttributes();
        $this->setAttribute('origin_uid', $origin_uid);
    }
    /// Checks if this tx_newspaper_Extra was placed on this page zone
    /** \return \c true if this tx_newspaper_Extra was placed on this page zone
     *  \todo Honestly, I don't know what "this page zone" means. Oliver?
     */
    public function isOriginExtra() {
        return (($this->getAttribute('origin_uid') == 0) ||
                ($this->getAttribute('origin_uid') == $this->getExtraUid()));
    }

    /// Gets the number of abstract records pointing to the current concrete record.
    /** \return  The number of abstract records pointing to the current concrete
     *      record.
     */
    public function getReferenceCount() {
        $row = tx_newspaper::selectOneRow(
            'COUNT(*) AS c',
            self::$table,
            'extra_table = ' . $GLOBALS['TYPO3_DB']->fullQuoteStr($this->getTable(), $this->getTable()) .
            ' AND extra_uid = ' . $this->getUid()
        );
        return intval($row['c']);
    }

    /// Finds the tx_newspaper_PageZone this tx_newspaper_Extra is placed upon.
    /**
     *  I'm afraid this raises several problems, so this function should be used
     *  with care.
     *  - What if a tx_newspaper_Extra is placed on more than one
     *    tx_newspaper_PageZone?
     *  - What if a tx_newspaper_Extra is not placed on any tx_newspaper_PageZone
     *    at all (perhaps because it is a template from which other
     *    tx_newspaper_Extra are copied)?
     *  - We must manually select the tx_newspaper_Extra from all
     *    Extra-to-PageZone-MM-tables there are.
     *    - These are currently limited to two (for tx_newspaper_Article and
     *      tx_newspaper_PageZone_Page), but there is no guarantee that that
     *      stays this way (although it is highly likely). When that happens,
     *      this function must be changed. Bad software design! No cookie for me!
     *    - The order in which these MM-tables are checked for the
     *      tx_newspaper_Extra is pretty arbitrary.
     *
     *    @return tx_newspaper_PageZone Page zone this tx_newspaper_Extra is placed upon, or \c null
     */
    public function getPageZone() {
        /// Check if the Extra insertExtraAfteris associated with an article...
        foreach (array('tx_newspaper_article_extras_mm' => 'tx_newspaper_Article',
                        /// ...or a page zone...
                        'tx_newspaper_pagezone_page_extras_mm' => 'tx_newspaper_PageZone_Page')
                       as $table => $type) {
            $row = tx_newspaper::selectZeroOrOneRows(
                'uid_local', $table, 'uid_foreign = ' . $this->getExtraUid(), '', '', '', false
            );
            if ($row['uid_local']) {
                return new $type(intval($row['uid_local']));
            }
        }

        $extra = $this->getContainerExtra();
        return null;
    }


    /// save hook functions

    /// \todo: documentation
    public static function processCmdmap_preProcess($command, $table, $id, $value, $that) {

        // check if a concrete extra can be deleted (and delete all associated records if yes)
        // tx_newspaper_article implements the extra interface but shouldn't be regarded as an extra when it comes to deleting
        if ($table != 'tx_newspaper_article' && tx_newspaper::classImplementsInterface($table, 'tx_newspaper_ExtraIface')) {
//t3lib_div::devlog('delete extra', 'newspaper', 0, array('table' => $table, 'id' => $id));
            $e = new $table(intval($id));
//t3lib_div::devlog('delete extra count ref', 'newspaper', 0, array($e->getReferenceCount()));
            if ($e->getReferenceCount() <= 1) {
                // just one (or none?) abstract extra for this concrete extra
                $e->deleteIncludingReferences();
            } else {
                // \todo: include list of reference, or even a link to delete all references?
                die('This extra can\'t be deleted because ' . $e->getReferenceCount() . ' references are existing! Please remove the extras one by one');
            }
        }
    }

    /// Save hook function, called from the global save hook in tx_newspaper_typo3hook
    /** Writes an abstract record for a concreate3 article list, if no abstract record is available
     * \param $status Status of the current operation, 'new' or 'update
     * \param $table The table currently processing data for
     * \param $id The record uid currently processing data for, [integer] or [string] (like 'NEW...')
     * \param $fieldArray The field array of a record
     * \param $that t3lib_TCEmain object?
     */
    public static function processDatamap_afterDatabaseOperations($status, $table, $id, &$fieldArray, $that) {
        $timer = tx_newspaper_ExecutionTimer::create();
        self::writeRecordsIfNewExtraOnPageZone($status, $table, $id, $fieldArray, $that);
    }

    public static function updateDependencyTree(tx_newspaper_Extra $extra) {
        if (tx_newspaper_DependencyTree::useDependencyTree()) {
            $tree = tx_newspaper_DependencyTree::generateFromExtra($extra);
            $tree->executeActionsOnPages('tx_newspaper_Extra');
        }
    }

    /// writes tx_newspaper_extra and tx_newspaper_pagezone_page_extras_mm records if a new extra is added to a pagezone
    /** \todo: explain in detail what's happening here, and in particular, how this function is called!
     */
    private static function writeRecordsIfNewExtraOnPageZone($status, $table, $id, $fieldArray, t3lib_tcemain $that) {

        if (!self::isNewExtraOnPageZone($status, $table)) return;

        $pz = self::getPagezoneForInsertingNewExtra($table, $id);
        $e = self::instantiateNewExtra($table, $id, $that);

        self::setDefaultsForNewExtra($e, $pz->getTable());

        self::insertNewExtraOnPagezone($e, $pz);

        $pz->changeExtraParagraph($e, intval(t3lib_div::_GP('paragraph'))); // changeExtraParagraph() stores the extras, so no need to store after call this function call
    }

    private static function isNewExtraOnPageZone($status, $table) {
        return (
            tx_newspaper::classImplementsInterface($table, 'tx_newspaper_ExtraIface') &&
            !tx_newspaper::isAbstractClass($table) &&
            strtolower($table) != 'tx_newspaper_article' &&
            $status == 'new' &&
            !$_REQUEST['tx_newspaper_mod1']['newExtraInElementBrowser']
        );
    }

    /** @return tx_newspaper_PageZone */
    private static function getPagezoneForInsertingNewExtra($table, $id) {
        $pz_uid = intval(t3lib_div::_GP('new_extra_pz_uid'));
        if (!$pz_uid) {
            t3lib_div::devlog('writeRecordsIfNewExtraOnPageZone(): Illegal value for pagezone uid: #', 'newspaper', 3, array('table' => $table, 'id' => $id, 'pz_uid' => $pz_uid));
            die('Fatal error: Illegal value for pagezone uid: #' . $pz_uid . '. Please contact developers');
        }
        return tx_newspaper_PageZone_Factory::getInstance()->create($pz_uid);
    }

    private static function instantiateNewExtra($table, $id, t3lib_tcemain $that) {
        // get uid of new concrete extra (that was just stored)
        if (!$concrete_extra_uid = intval($that->substNEWwithIDs[$id])) {
            t3lib_div::devlog('writeRecordsIfNewExtraOnPageZone(): new id ' . $id . ' could not be substituted', 'newspaper', 3, array('table' => $table, 'id' => $id, 'pz_uid' => $pz_uid));
            die('Fatal error: New extra ' . $table . ' could not be created. <b>Please contact developers.</b> Please <i>reload</i> the backend if you cannot access the backend anymore.');
        }

        // create abstract record for this concrete extra
        $abstract_uid = tx_newspaper_Extra::createExtraRecord($concrete_extra_uid, $table, true); // $force=true, there's no abstract record for this extra existing (for this is a totally new extra)

        return tx_newspaper_Extra_Factory::getInstance()->create($abstract_uid);
    }

    private static function setDefaultsForNewExtra(tx_newspaper_Extra &$e, $table) {
        // set show and inherit to 0 if extra is placed on a pagezone_page
        // set show to 1 if extra is placed in an article
        $state = ($table == 'tx_newspaper_pagezone_page')? 0 : 1;

        $e->setAttribute('show_extra', $state);
        $e->setAttribute('is_inheritable', 1); // always 1 until #844 is fixed (and un-inherited etxra causes a bug)
    }

    private static function insertNewExtraOnPagezone(tx_newspaper_Extra $e, tx_newspaper_PageZone $pz) {
        $after_origin_uid = intval(t3lib_div::_GP('new_extra_after_origin_uid'));
        $pz->insertExtraAfter($e, $after_origin_uid, true); // insert BEFORE setting the paragraph (so the paragraph can be inherited)

        // log placing extras on pagezone_page
        if ($pz->getTable() == 'tx_newspaper_pagezone_page') {
            tx_newspaper_workflow::logPlacement('tx_newspaper_pagezone', $pz->getAbstractUid(), array('origin uid' => $after_origin_uid, 'extra uid' => $e->getExtraUid()), NP_WORKLFOW_LOG_PLACEMENT_INSERT_AFTER);
        }

    }


    private $uid = 0;            ///< Extra's UID in the concrete Extra table
    protected $extra_uid = 0;    ///< Extra's UID in the abstract Extra table

    private $attributes = array();                ///< Attributes of the concrete extra
    private $extra_attributes = array();        ///< Attributes of the abstract extra

    /** @var tx_newspaper_Smarty */
    protected $smarty = null;                    ///< tx_newspaper_Smarty rendering engine

    /** @var tx_newspaper_Extra[] */
    private static $registeredExtra = array();    ///< List of registered tx_newspaper_Extra


    /// Extra table must be defined here because tx_newspaper_ExtraIface is an interface
    /** \todo this table is defined in tx_newspaper_Extra_Factory too. decide
     *          on one class to store it!
     */
    private static $table = 'tx_newspaper_extra';

    /// When a new reference to an Extra is made, these fields are copied
    private static $fields_to_copy_into_extra_table = array(
        'pid', 'crdate', 'cruser_id', 'deleted',
    );

    // constants for hiding extras
    // User TSConfig: newspaper.be.hideExtra.[class anme] 0  [value]
    const HIDE_NOWHERE = 0;
    const HIDE_EVERYWHERE = -1;
    const HIDE_IN_ARTICLE_BE = 1;
    const HIDE_IN_PLACEMENT = 16;

}
?>

<?php
/**
 * @author Lene Preuss <lene.preuss@gmail.com>
 */

/**
 *  Generates a SQL query's table and where parts to select articles filtered by arbitrary criteria.
 *  Pass it an array as input, and for each key of that array define a method AddConditionFor{Key}()
 *  that adds conditions to the WHERE clause and optionally tables to the FROM clause.
 */
class tx_newspaper_module2_QueryBuilder {

    const TEXT_SEARCH_FOR_TEXT = 1; // Value should be searched as text
    const TEXT_SEARCH_FOR_UID = 2;  // Value #[uid] should return article with article uid given in value

    private $nonFilterFields = array('type', 'go', 'reset_filter', 'startPage'); // Easier to debug if these fields are ignored

    public function __construct(array $input) {
        $this->input = $input;
        $this->prepareQuery();
    }


    /** Create where part of sql statement for current filter setting and sets basic db tables needed
     *  @return void
     */
    private function prepareQuery() {

        $this->addWhere('tx_newspaper_article.is_template = 0');
        $this->addWhere('tx_newspaper_article.pid = ' . tx_newspaper_Sysfolder::getInstance()->getPid(new tx_newspaper_Article()));
        $this->addTableReference('tx_newspaper_article');

        // Start with text filter: May contain #[uid], so the article with that uid should be returned ONLY
        if ($this->useFilter('text')) {
            if ($this->addConditionForText() == self::TEXT_SEARCH_FOR_UID) {
                return; // Query already built completely ...
            }
            unset($this->input['text']); // Don't add text filter twice ...
        }

        // Check section filter then: Might lead to an empty result set, no matter how the other filters are set
        if ($this->useFilter('section')) {
            if ($this->addConditionForSection() === false) {
                return; // No articles in result set
            }
            unset($this->input['section']); // Don't add section filter twice ...
        }

        foreach (array_keys($this->input) as $key) {
            if ($this->useFilter($key)) {
                $method = 'addConditionFor' . ucfirst($key);
                if (method_exists($this, $method)) {
                    $this->$method();
                }
            }
        }

    }


    /**
     * Add table or join expression to the array of tables needed for the query
     * @param string $table_reference Table name or JOIN expression ("table ON condition")
     */
    public function addTableReference($table_reference) {
        $this->table_references[] = $table_reference;
    }

    /**
     * Get table references needed for query
     * @return string Table references (single table or JOIN) needed for query
     */
    public function getTableReferences() {
        return implode(' ', $this->table_references);
    }

    /**
     * Add WHERE condition for query
     * @param string $table WHERE statement
     */
    public function addWhere($where) {
        $this->where[] = $where;
    }

    /**
     * Get collected WHERE condition as string
     * @return string WHERE condition; conditions are concatenated with "AND"
     */
    public function getWhere() {
        return implode("\n AND ", $this->where);
    }

    /**
     * Checks if the filter for given $key should be used (Depending on key on given value in $this->input)
     * @param $key String Filter to be checked
     * @return bool true if the filter should be applied, else false
     */
    private function useFilter($key) {
        if (in_array($key, $this->nonFilterFields)) {
            return false; // Non-filter fields
        }

        // Make sure role=0 can be filtered
        if ($key == 'role') {
            return isset($this->input['role']);
        }

        return isset($this->input[$key]) && trim((string)$this->input[$key]);
    }


    /**
     * Add WHERE condition for date range
     */
    private function addConditionForRange() {
        $this->addWhere('tx_newspaper_article.tstamp>=' . tx_newspaper_UtilMod::calculateTimestamp($this->input['range']));
    }

    /**
     * Add conditions for sections
     * @return bool true, if sections could be found, else false, if no section could be found (so no article can be found)
     */
    private function addConditionForSection() {
        $whereSectionUids = array();
        foreach(t3lib_div::trimExplode(',', $this->input['section']) as $section) {
            $whereSectionUids = array_merge($whereSectionUids, $this->getUIDsForSection($section));
        }
        $whereSectionUids = array_unique($whereSectionUids); // Remove duplicate section uids

        if (empty($whereSectionUids)) {
            $this->table_references = array('tx_newspaper_article'); // Reset table array
            $this->where = array('false'); // Unknown sections, so no article available for this search query
            return false; // No matching section found, so no article in search result
         }

        $this->addTableReference('JOIN tx_newspaper_article_sections_mm ON tx_newspaper_article.uid = tx_newspaper_article_sections_mm.uid_local');
        $this->addWhere('tx_newspaper_article_sections_mm.uid_foreign IN (' . implode(',', $whereSectionUids) . ')');

        return true;
    }

    /**
     * Add where conditions for an article's publish state
     */
    private function addConditionForHidden() {
        switch($this->input['hidden']) {
            case 'on':
                $this->addWhere('hidden = 1');
                break;
            case 'off':
                $this->addWhere('hidden = 0');
                break;
        }
    }

    /**
     * Add where condition for newspaper role (if set)
     */
    private function addConditionForRole() {
        switch(intval($this->input['role'])) {
            case NP_ACTIVE_ROLE_EDITORIAL_STAFF:
            case NP_ACTIVE_ROLE_DUTY_EDITOR:
            case NP_ACTIVE_ROLE_POOL:
            case NP_ACTIVE_ROLE_NONE:
            $this->addWhere('workflow_status = ' . intval($this->input['role']));
            break;
            case '-1': // all
        }
    }


    /**
     * Get condition for author (if set)
     * The condition can be extended with a hook.
     * Hook: $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['newspaper']['getExtendedAuthorCondition'][] = [class name];
     */
    private function addConditionForAuthor() {
        $author = trim($this->input['author']);
        if (!$author) return;

        $condition = 'MATCH(tx_newspaper_article.author) AGAINST (\'' . addslashes($author) .'\' IN BOOLEAN MODE)';
        $this->addExtendedAuthorConditionHooks($author, $condition);

    }

    /// Extend condition in (registered) hooks
    private function addExtendedAuthorConditionHooks($author, $where) {
        $hook_called = false;
        if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['newspaper']['getExtendedAuthorConditionHook'])) {
            foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['newspaper']['getExtendedAuthorConditionHook'] as $class) {
                if (method_exists($class, 'getExtendedAuthorConditionHook')) {
                    $class::getExtendedAuthorConditionHook($this, $author, $where);
                    $hook_called = true;
                }
            }
        }
        if (!$hook_called) $this->addWhere($where);
    }

    /**
     * Add where condition for be_user (if set)
     */
    private function addConditionForBe_user() {
        $this->addWhere('modification_user IN (
        SELECT uid FROM be_users
            WHERE username LIKE "%' . addslashes(trim($this->input['be_user'])) . '%"
            OR realName LIKE "%' . addslashes(trim($this->input['be_user'])) . '%"
        )');
    }

    /**
     * Add where condition for text (if set)
     * @return int Either self::TEXT_SEARCH_FOR_UID or self::TEXT_SEARCH_FOR_TEXT
     */
    private function addConditionForText() {
        if (substr(trim($this->input['text']), 0, 1) == '#') {
     	    // looking for an article uid?
     		$uid = intval(substr(trim($this->input['text']), 1));
     		if (trim($this->input['text']) == '#' . $uid) {
     			// text contains a query like #[int], so search for this uid ONLY
     			$this->table_references = array('tx_newspaper_article');
     			$this->where = array('uid=' . $uid);
                return self::TEXT_SEARCH_FOR_UID;
     		}
     	}

        // Search for all terms divided by a space character
        // So "demo example" finds all articles with both the "demo" AND "example" somewhere in the article
        $wherePart = array();
        foreach(t3lib_div::trimExplode(' ', $this->input['text']) as $term) {
            $wherePart[] = '(' .
                    'title LIKE "%' . addslashes($term) . '%" OR ' .
                    'kicker LIKE "%' . addslashes($term) . '%" OR ' .
                    'teaser LIKE "%' . addslashes($term) . '%" OR ' .
                    'bodytext LIKE "%' . addslashes($term) . '%"' .
                ')';
        }

        $this->addWhere(implode(' AND ', $wherePart));
        return self::TEXT_SEARCH_FOR_TEXT;
    }

    /**
     * Adds table to $this->table and where conditions to $this->where
     * @return void
     */
    private function addConditionForControltag() {
        if (!intval($this->input['controltag'])) {
            return;
        }
        $this->addTableReference('JOIN tx_newspaper_article_tags_mm ON tx_newspaper_article.uid = tx_newspaper_article_tags_mm.uid_local');
        $this->addWhere('tx_newspaper_article_tags_mm.uid_foreign = ' . intval($this->input['controltag']));
    }

    /** Get section uids for given search term $section
     * @param $section String Search term
     * @param bool $recursive Whether or not the search is recursive
     * @return array Section uids
     */
    private function getUIDsForSection($section, $recursive=true) {
		$sectionUids = tx_newspaper::selectRows(
			'uid',
			'tx_newspaper_section',
			'section_name LIKE "%' . addslashes($section) . '%"' . // Search for sections containing the section search string
				' AND pid=' . tx_newspaper_Sysfolder::getInstance()->getPid(new tx_newspaper_section()) // Check current section sysfolder only
		);

        if (!$sectionUids) return array();

        $uids = array();
        foreach($sectionUids as $sectionRecord) {
            $uids[] = $sectionRecord['uid'];
            if ($recursive) {
                $s = new tx_newspaper_section(intval($sectionRecord['uid']));
                foreach($s->getChildSections(true) as $sub_section) {
                    $uids[] = $sub_section->getUid();
                }
            }
        }

        return array_unique($uids);
    }

    private $input = array();
    private $table_references = array();
    private $where = array();

}

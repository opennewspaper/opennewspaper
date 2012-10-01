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

    public function __construct(array $input) {
        $this->input = $input;
        $this->createWherePartArray();
    }

    public function getTable() {
        return implode(', ', $this->tables);
    }

    public function getWhere() {
        return implode("\n AND ", $this->where);
    }

	/// create where part of sql statement for current filter setting
	/// \return array key 'table' table(s) to be used, key 'where': condition combined with "AND"; or false if query will return an empty result set
	private function createWherePartArray() {
//t3lib_div::devlog('createWherePartArray()', 'newspaper', 0, array('_request' => $_REQUEST, 'input' => $this->input));
        $this->where = array(
            'is_template=0',
            'pid=' . tx_newspaper_Sysfolder::getInstance()->getPid(new tx_newspaper_Article())
        );
        $this->tables = array('tx_newspaper_article');

        ksort($this->input);    // helps ensure that text is handled last
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
     * Checks if the filter for given $key should be used (Depending on key on given value in $this->input)
     * @param $key Filter to be checked
     * @return bool true if the filter should be applied, ,else false
     */
    private function useFilter($key) {
        // Make sure role=0 can be filtered
        if ($key == 'role') {
            return isset($this->input['role']);
        }
        return isset($this->input[$key]) && trim((string)$this->input[$key]);
    }


    private function addConditionForRange() {
        $this->where[] = 'tstamp>=' . tx_newspaper_UtilMod::calculateTimestamp($this->input['range']);
    }

    private function addConditionForSection() {
        $whereSectionUids = array();
        foreach(t3lib_div::trimExplode(',', $this->input['section']) as $section) {
            $whereSectionUids = array_merge($whereSectionUids, $this->getWhereForSection($section));
        }
        $whereSectionUids = array_unique($whereSectionUids); // Remove duplicate section uids

        if (empty($whereSectionUids)) {
             return; // No matching section found, so not article in search result
         }

        $this->tables[] = 'tx_newspaper_article_sections_mm';
        $this->where[] = 'tx_newspaper_article.uid=tx_newspaper_article_sections_mm.uid_local AND tx_newspaper_article_sections_mm.uid_foreign IN (' . implode(',', $whereSectionUids) . ')';
    }

    private function addConditionForHidden() {
        switch($this->input['hidden']) {
            case 'on':
                $this->where[] = 'hidden=1';
                break;
            case 'off':
                $this->where[] = 'hidden=0';
                break;
        }
    }

    private function addConditionForRole() {
        switch(intval($this->input['role'])) {
            case NP_ACTIVE_ROLE_EDITORIAL_STAFF:
            case NP_ACTIVE_ROLE_DUTY_EDITOR:
            case NP_ACTIVE_ROLE_NONE:
            $this->where[] = 'workflow_status=' . intval($this->input['role']);
            break;
            case '-1': // all
        }
    }

    private function addConditionForAuthor() {
        $this->where[] = 'author LIKE "%' . addslashes(trim($this->input['author'])) . '%"';
    }

    private function addConditionForBe_user() {
        $this->where[] = 'modification_user IN (
        SELECT uid FROM be_users
            WHERE username LIKE "%' . addslashes(trim($this->input['be_user'])) . '%"
            OR realName LIKE "%' . addslashes(trim($this->input['be_user'])) . '%"
        )';
    }

    private function addConditionForText() {
        if (substr(trim($this->input['text']), 0, 1) == '#') {
     	    // looking for an article uid?
     		$uid = intval(substr(trim($this->input['text']), 1));
     		if (trim($this->input['text']) == '#' . $uid) {
     			// text contains a query like #[int], so search for this uid ONLY
     			$this->tables = array('tx_newspaper_article');
     			$this->where = array('uid=' . $uid);
                return;
     		}
     	}

        $this->where[] = '(title LIKE "%' . addslashes(trim($this->input['text'])) . '%" OR kicker LIKE "%' .
                     addslashes(trim($this->input['text'])) . '%" OR teaser LIKE "%' .
                     addslashes(trim($this->input['text'])) . '%" OR bodytext LIKE "%' .
                     addslashes(trim($this->input['text'])) . '%")';
    }

    private function addConditionForControltag() {
        $tags = tx_newspaper_Tag::getAllTagsWhere(
            "title='" . $this->input['controltag'] ."' AND tag_type=" . tx_newspaper_Tag::getControltagType()
        );
        if (empty($tags)) return;

        $this->where[] = 'tx_newspaper_article_tags_mm.uid_foreign=' . $tags[0]->getUid() . ' AND tx_newspaper_article.uid=tx_newspaper_article_tags_mm.uid_local';
        $this->tables[] = 'tx_newspaper_article_tags_mm';
    }

    /** Get section uids for given search term $section
     * @param $section String Search term
     * @param bool $recursive Whether or not the search is recursive
     * @return array Section uids
     */
    private function getWhereForSection($section, $recursive=true) {
		$sectionUids = tx_newspaper::selectRows(
			'uid',
			'tx_newspaper_section',
			'section_name LIKE "%' . addslashes($section) . '%"' . // Search for sections containing the section search string
				' AND pid=' . tx_newspaper_Sysfolder::getInstance()->getPid(new tx_newspaper_section()) // Check current section sysfolder only
		);

        if (!$sectionUids) {
            return array(); // No matching section found
        }

        $uids = array();
        foreach($sectionUids as $sectionUid) {
            $uids[] = $sectionUid['uid'];
            $s = new tx_newspaper_section(intval($sectionUid['uid']));
            if ($recursive) {
                foreach($s->getChildSections(true) as $sub_section) {
                    $uids[] = $sub_section->getUid();
                }
            }
        }
//t3lib_div::devlog('getWhereForSection()', 'newspaper', 0, array('$sectionUids' => $sectionUids, 'uids' => $uids));
		return array_unique($uids);
	}

    private $input = array();
    private $tables = array();
    private $where = array();

}

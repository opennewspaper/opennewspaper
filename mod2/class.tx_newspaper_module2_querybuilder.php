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
        return implode(' AND ', $this->where);
    }

	/// create where part of sql statement for current filter setting
	/// \return array key 'table' table(s) to be used, key 'where': condition combined with "AND"; or false if query will return an empty result set
	private function createWherePartArray() {
//t3lib_div::devlog('createWherePartArray()', 'newspaper', 0, array('_request' => $_REQUEST, 'input' => $this->input));
		$this->where = array(
            'is_template=0',
            'tstamp>=' . tx_newspaper_UtilMod::calculateTimestamp($this->input['range']),
            'pid=' . tx_newspaper_Sysfolder::getInstance()->getPid(new tx_newspaper_Article())
        );
        $this->tables = array('tx_newspaper_article');

        ksort($this->input);    // helps ensure that text is handled last
        foreach (array_keys($this->input) as $key) {
            if (trim($this->input[$key])) {
                $method = 'addConditionFor' . ucfirst($key);
                if (method_exists($this, $method)) {
                    $this->$method();
                }
       		}
        }

	}

    private function addConditionForSection() {
        $where_section = $this->getWhereForSection($this->input['section']);
     	if ($where_section === false) {
     		return; // no matching section found, so not article in search result
     	}
     	$this->tables[] = 'tx_newspaper_article_sections_mm';
     	$this->where[] = 'tx_newspaper_article.uid=tx_newspaper_article_sections_mm.uid_local AND tx_newspaper_article_sections_mm.uid_foreign IN (' . $where_section . ')';
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
        switch(strtolower($this->input['role'])) {
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

	/// Get section uids for given search term $section
	/// \param $section search term for sections (is NOT trimmed)
	/// \param $recursive wheater or not sub section are searched too
	/// \return comma separated list of section uids or false if no section could be found
	private function getWhereForSection($section, $recursive=true) {
		$sectionUids = tx_newspaper::selectRows(
			'uid',
			'tx_newspaper_section',
			'section_name LIKE "%' . addslashes($section) . '%"' . // search for sections contains the section search string
				' AND pid=' . tx_newspaper_Sysfolder::getInstance()->getPid(new tx_newspaper_section()) // check current section sysfolder only
		);
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
		$sectionUidList = implode(',', array_unique($uids));

		if (!$sectionUidList) {
			// no matching section found, so no article in result set
			return false;
		}
//t3lib_div::devlog('getWhereForSection()', 'newspaper', 0, array('$sectionUids' => $sectionUids, 'sectionUidList' => $sectionUidList, 'query' => tx_newspaper::$query));
		return $sectionUidList;
	}

    private $input = array();
    private $tables = array();
    private $where = array();

}

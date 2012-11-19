<?php
/**
 * @author Lene Preuss <lene.preuss@gmail.com>
 */

require_once('class.tx_newspaper_module2_querybuilder.php');

class tx_newspaper_module2_Filter {

    const template_path = 'typo3conf/ext/newspaper/mod2/res/';
    const production_list_template = 'mod2_filterbox_prodlist.tmpl';
    const article_browser_template = 'mod2_filterbox_artbrowser.tmpl';

    public function __construct($LL, $input, $is_article_browser) {
//t3lib_div::devlog('Filter ProdList/Article browser input', 'np', 0, array('input' => $input));
        $this->is_article_browser = $is_article_browser;
        $this->localized_labels = $LL;
        $this->filter_values = $this->preprocessFilter($input);
        $this->query_builder = new tx_newspaper_module2_QueryBuilder($this->filter_values);
    }

    public function getCount() {
        return tx_newspaper::countRows($this->query_builder->getTable(), $this->query_builder->getWhere());
    }

    public function getArticleRecords() {
        $records = tx_newspaper::selectRows(
        	'DISTINCT tx_newspaper_article.*', // Make sure articles are list once only, even if assigned to multiple sections
        	$this->query_builder->getTable(),
        	$this->query_builder->getWhere(),
        	'',
        	'tstamp DESC',
        	intval($this->filter_values['startPage']) * intval($this->filter_values['step']) . ', ' . (intval($this->filter_values['step']))
       	);
//tx_newspaper::devlog('Filter ProdList/Article browser', array('table' => $this->query_builder->getTable(), 'where' => $this->query_builder->getWhere()));
        return $records;
    }

    public function renderBox(tx_newspaper_Smarty $smarty) {

        $smarty->setTemplateSearchPath(array(self::template_path));

        $smarty->assign('LL', $this->localized_labels); // localized labels
        $smarty->assign('FILTER', $this->filter_values); // add filter settings (for setting selected values in select boxes and text fields)
        $smarty->assign('RANGE', $this->getRangeArray()); // add data for range dropdown
        $smarty->assign('HIDDEN', $this->getHiddenArray()); // add data for "hidden" dropdown
        $smarty->assign('ROLE_FILTER_EQUALS_USER_ROLE', $this->isRoleFilterEqualToUserRole());
        $smarty->assign('ROLE', $this->getRoleArray()); // add data for role dropdown
        $smarty->assign('CONTROLTAGS', $this->getControltags());
        $smarty->assign('STEPS', array(10, 20, 30, 50, 100)); // add data for step dropdown (for page browser)

        return $smarty->fetch($this->is_article_browser? self::article_browser_template: self::production_list_template);
    }

    ////////////////////////////////////////////////////////////////////////////

    /// Read filter setting from get params (set default values if not set), stores in $this->input
    private function preprocessFilter($input) {
//t3lib_div::devlog('processFilter()', 'newspaper', 0, array('_r' => $_REQUEST, 'input' => $this->input));
		if ($input['type'] == 'filter' || $input['type'] == 'reset_startpage') {
			// use filter settings, add default values if needed
			// no_reset = 1 -> if an article is publish or deleted etc.: don't reset filter settings
			$filter = $this->addDefaultFilterValues($input);
			if ($input['type'] == 'reset_startpage') {
				$filter['startPage'] = 0; // reset startPage if filter settings were submitted
			}
            return $filter; // Return store filter settings
		}

		// Module was called from menu or filter were resetted
		return $this->addDefaultFilterValues(array(), true); // Get default values

//t3lib_div::devlog('processFilter()', 'newspaper',0, array('input' => $this->input));
	}

	/// Adds default filter settings if filter type is missing in given array
	/** if array $settings is empty or filled partly only, all missing filter values are filled with default values
     * \param $settings filter settings
	 * \param $forceReset if set to true some fields are forced to be filled with default values
	 * \return array with filter settings where missing filters were added (using default values)
	 */
	private function addDefaultFilterValues(array $settings, $forceReset=false) {
//t3lib_div::devlog('addDefaultFilterValues()', 'newspaper', 0, array('settings' => $settings));

        self::$force_reset = $forceReset;

        self::addDefaultFilterValue($settings, 'author', '', false);
        self::addDefaultFilterValue($settings, 'be_user', '', false);
        self::addDefaultFilterValue($settings, 'text', '', false);
        self::addDefaultFilterValue($settings, 'controltag', 0, true);
        self::addDefaultFilterValue($settings, 'step', 10, true);
        self::addDefaultFilterValue($settings, 'startPage', 0, true);
        self::addDefaultFilterValue($settings, 'hidden', 'all', true);
        self::addDefaultFilterValue($settings, 'role', ($this->is_article_browser? '-1': tx_newspaper_workflow::getRole()), false);
        self::addDefaultFilterValue($settings, 'range', $this->getDefaultRange(), true);
        self::addDefaultFilterValue($settings, 'section', ($this->is_article_browser && $_REQUEST['s'])? $_REQUEST['s']: $this->getDefaultSection(), false);

//t3lib_div::devlog('addDefaultFilterValues() done', 'newspaper', 0, array('settings' => $settings, 'type' => $type));
		return $settings;
	}

    static $force_reset = false;
    private static function addDefaultFilterValue(array &$settings, $key, $value, $checkEmptyValue=false) {
        if (self::$force_reset || !array_key_exists($key, $settings) || ($checkEmptyValue && !$settings[$key])) {
      		$settings[$key] = $value;
      	}
    }

    /**
     * Get default range filter setting (for production list or article browser)
     * Either day_2 for production list, day_180 for article list
     * or User TSConfig
     * newspaper.productionList.defaultRange = [today|day_1,day_2,day_3,day_7,day_14,day_30,day_60,day_90,day_180,day_360,no_limit]
     * newspaper.articleBrowser.defaultRange = [today|day_1,day_2,day_3,day_7,day_14,day_30,day_60,day_90,day_180,day_360,no_limit]
     * @return string A valid range value (to be used in filter select box)
     */
    private function getDefaultRange() {
        if (isset($GLOBALS['BE_USER'])) {
            if (!$this->is_article_browser) {
                $tsc = 'newspaper.productionList.defaultRange'; // Production list
            } else {
                $tsc = 'newspaper.articleBrowser.defaultRange'; // Article browser
            }
            if ($range = trim($GLOBALS['BE_USER']->getTSConfigVal($tsc))) {
                if ($this->isValidRange($range)) {
                    return $range;
                }
            }
        }

        // Just return hard-coded default values
        return ($this->is_article_browser ? 'day_180' : 'day_7');
    }

    /**
     * Checks if given $range is an available filter option
     * @param $range String (Examples: 'today', 'day_1', 'day_2', ......., 'days_360', 'no_limit', see getRangeArray() for details
     * @return bool true if the $range is an available option
     */
    private function isValidRange($range) {
        return array_key_exists($range, $this->getRangeArray());
    }


    /**
     * Get default value for section filter
     * If user TSConfig newspaper.baseSections is set, all sections (comma separated) will be added
     * @return string Default section name(s) or empty string if not set
     */
    private function getDefaultSection() {
        // Read User TSConfig for base sections (if available): get uids of base sections
        if ($GLOBALS['BE_USER']) {
            if ($GLOBALS['BE_USER']->getTSConfigVal('newspaper.baseSections')) {
                $baseSectionUids = t3lib_div::trimExplode(',', $GLOBALS['BE_USER']->getTSConfigVal('newspaper.baseSections'));
            }
            if ($baseSectionUids) {
                $sectionsNames = array();
                foreach($baseSectionUids as $sectionUid) {
                    $section = new tx_newspaper_Section(intval($sectionUid));
                    $sectionsNames[] = $section->getAttribute('section_name');
                }
                return implode(', ', $sectionsNames);
            }
        }
        return ''; // Default section filter
    }


    // Functions to fill filter dropdowns with data

    /**
     * Get available time range filters
     * @return array Options for time range dropdown (array('key used in form' => 'localized label')
     */
    private function getRangeArray() {
		return array(
            'today' => $this->localized_labels['option_range_today'],
            'day_1' => '1 ' . $this->localized_labels['option_range_day'],
            'day_2' => '2 ' . $this->localized_labels['option_range_days'],
            'day_3' => '3 ' . $this->localized_labels['option_range_days'],
            'day_7' => '7 ' . $this->localized_labels['option_range_days'],
            'day_14' => '14 ' . $this->localized_labels['option_range_days'],
            'day_30' => '30 ' . $this->localized_labels['option_range_days'],
            'day_60' => '60 ' . $this->localized_labels['option_range_days'],
            'day_90' => '90 ' . $this->localized_labels['option_range_days'],
            'day_180' => '180 ' . $this->localized_labels['option_range_days'],
            'day_360' => '360 ' . $this->localized_labels['option_range_days'],
            'no_limit' => $this->localized_labels['option_range_no_limit']
        );
	}

	/// \return Array with options for publish state dropdown
	private function getHiddenArray() {
		$hidden = array();
		$hidden['all'] = $this->localized_labels['option_status_hidden_all'];
		$hidden['on'] = $this->localized_labels['option_status_hidden_on'];
		$hidden['off'] = $this->localized_labels['option_status_hidden_off'];
		return $hidden;
	}

	/// \return true if role filter equals the current role of the be_user, else false
	private function isRoleFilterEqualToUserRole() {
		return ($this->filter_values['role'] ==  tx_newspaper_workflow::getRole());
	}

   	/// \return Array with options for workflow/role dropdown
	private function getRoleArray() {
		global $LANG;
		$role = array();
		$role['-1'] = $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label_status_role_all', false);
		$role[NP_ACTIVE_ROLE_EDITORIAL_STAFF] = $LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:label_workflow_role_editorialstaff', false);
		$role[NP_ACTIVE_ROLE_DUTY_EDITOR] = $LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:label_workflow_role_dutyeditor', false);
		$role[NP_ACTIVE_ROLE_NONE] = $LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:label_workflow_role_none', false);
		return $role;
	}

    /**
     * Get (sorted) control tags
     * @return array [tag uid] => Control tag title
     */
    private function getControltags() {
        $tags = array(0 => ''); // Empty entry
        foreach(tx_newspaper_Tag::getAllControlTags(self::getControltagCategoryUid()) as $tag) {
            $tags[$tag->getUid()] = $tag->getAttribute('title');
        }
        natcasesort($tags);
        return $tags;
    }

    /**
     * @todo handle categories better than simply using the first one -> User TSConfig?
     */
    private static function getControltagCategoryUid() {
        $categories = tx_newspaper_Tag::getAllControltagCategories();
        return intval($categories[0]['uid']);
    }

    private $localized_labels = array();
    private $filter_values = array();
    private $is_article_browser = false;
    /** @var tx_newspaper_module2_QueryBuilder */
    private $query_builder = null;
}

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
//tx_newspaper::devlog('Filter ProdList/Article browser input', array('input' => $input));
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
            return $filter; // store filter setting (no matter in receive by get param or default value)
		}

		// module was called from menu or filter were resetted
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
//t3lib_div::devlog('addDefaultFilterValues()', 'newspaper', 0, array('settings' => $settings, 'type' => $type));

        self::$force_reset = $forceReset;

        self::addDefaultFilterValue($settings, 'author', '');
        self::addDefaultFilterValue($settings, 'be_user', '');
        self::addDefaultFilterValue($settings, 'text', '');
        self::addDefaultFilterValue($settings, 'controltag', '');
        self::addDefaultFilterValue($settings, 'step', 10);
        self::addDefaultFilterValue($settings, 'startPage', 0);
        self::addDefaultFilterValue($settings, 'hidden', 'all');

        self::addDefaultFilterValue($settings, 'role', $this->is_article_browser? '-1': tx_newspaper_workflow::getRole());
        self::addDefaultFilterValue($settings, 'range', $this->is_article_browser? 'day_180': 'day_2'); // \todo: make tsconfigurable
        $section = ($this->is_article_browser && $_REQUEST['s'])? $_REQUEST['s']: $this->getDefaultSection();
        self::addDefaultFilterValue($settings, 'section', $section);

//t3lib_div::devlog('addDefaultFilterValues() done', 'newspaper', 0, array('settings' => $settings, 'type' => $type));
		return $settings;
	}

    static $force_reset = false;
    private static function addDefaultFilterValue(array &$settings, $key, $value) {
        if (!array_key_exists($key, $settings) || !$settings[$key] || self::$force_reset) {
      		$settings[$key] = $value;
      	}
    }

    /**
     * Get default value for section filter
     * If user TSConfig newspaper.baseSections is set, the first section will be used as default filter
     * @return string Default section title or empty string if not set
     */
    private function getDefaultSection() {
        // Read User TSConfig for base sections (if available): get uids of base sections
        if ($GLOBALS['BE_USER']) {
            if ($GLOBALS['BE_USER']->getTSConfigVal('newspaper.baseSections')) {
                $baseSectionUids = t3lib_div::trimExplode(',', $GLOBALS['BE_USER']->getTSConfigVal('newspaper.baseSections'));
            }
            if ($baseSectionUids) {
                $section = new tx_newspaper_Section(intval($baseSectionUids[0]));
                return $section->getAttribute('section_name');
            }
        }
        return ''; // Default section filter
    }

    // functions to fill filter dropdowns with data

   	/// \return Array with options for time range dropdown
	private function getRangeArray() {
		$range = array();
		$range['today'] = $this->localized_labels['option_range_today'];
		$range['day_1'] = '1 ' . $this->localized_labels['option_range_day'];
		$range['day_2'] = '2 ' . $this->localized_labels['option_range_days'];
		$range['day_3'] = '3 ' . $this->localized_labels['option_range_days'];
		$range['day_7'] = '7 ' . $this->localized_labels['option_range_days'];
		$range['day_14'] = '14 ' . $this->localized_labels['option_range_days'];
		$range['day_30'] = '30 ' . $this->localized_labels['option_range_days'];
		$range['day_60'] = '60 ' . $this->localized_labels['option_range_days'];
		$range['day_90'] = '90 ' . $this->localized_labels['option_range_days'];
		$range['day_180'] = '180 ' . $this->localized_labels['option_range_days'];
		$range['day_360'] = '360 ' . $this->localized_labels['option_range_days'];
		$range['no_limit'] = $this->localized_labels['option_range_no_limit'];
		return $range;
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

    private function getControltags() {
        $tags = tx_newspaper_Tag::getAllControlTags(self::getControltagCategoryUid());
        array_walk($tags, array($this, 'extractTagTitle'));
        return array_merge(array(''), $tags);
    }

    /**
     * @todo handle categories better than simply using the first one
     */
    private static function getControltagCategoryUid() {
        $categories = tx_newspaper_Tag::getAllControltagCategories();
        return intval($categories[0]['uid']);
    }

    private function extractTagTitle(&$tag, $key) { $tag = $tag->getAttribute('title'); }

    private $localized_labels = array();
    private $filter_values = array();
    private $is_article_browser = false;
    /** @var tx_newspaper_module2_QueryBuilder */
    private $query_builder = null;
}

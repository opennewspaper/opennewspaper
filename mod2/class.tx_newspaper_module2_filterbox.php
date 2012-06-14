<?php
/**
 * @author Lene Preuss <lene.preuss@gmail.com>
 */
 

class tx_newspaper_module2_Filterbox {

    public function __construct($LL, $input) {
        $this->LL = $LL;
        $this->input = $input;
    }

    public function render() {
        $smarty = new tx_newspaper_Smarty();
        $smarty->setTemplateSearchPath(array('typo3conf/ext/newspaper/mod2/res/'));

        $smarty->assign('LL', $this->LL); // localized labels
        $smarty->assign('FILTER', $this->input); // add filter settings (for setting selected values in select boxes and text fields)
        $smarty->assign('RANGE', $this->getRangeArray()); // add data for range dropdown
        $smarty->assign('HIDDEN', $this->getHiddenArray()); // add data for "hidden" dropdown
        $smarty->assign('ROLE_FILTER_EQUALS_USER_ROLE', $this->isRoleFilterEqualToUserRole());
        $smarty->assign('ROLE', $this->getRoleArray()); // add data for role dropdown
        $smarty->assign('CONTROLTAGS', $this->getControltags());
        $smarty->assign('STEP', array(10, 20, 30, 50, 100)); // add data for step dropdown (for page browser)

        return $smarty->fetch('mod2_filterbox.tmpl');
    }

    // functions to fill filter dropdowns with data

   	/// \return Array with options for time range dropdown
	private function getRangeArray() {
		$range = array();
		$range['today'] = $this->LL['option_range_today'];
		$range['day_1'] = '1 ' . $this->LL['option_range_day'];
		$range['day_2'] = '2 ' . $this->LL['option_range_days'];
		$range['day_3'] = '3 ' . $this->LL['option_range_days'];
		$range['day_7'] = '7 ' . $this->LL['option_range_days'];
		$range['day_14'] = '14 ' . $this->LL['option_range_days'];
		$range['day_30'] = '30 ' . $this->LL['option_range_days'];
		$range['day_60'] = '60 ' . $this->LL['option_range_days'];
		$range['day_90'] = '90 ' . $this->LL['option_range_days'];
		$range['day_180'] = '180 ' . $this->LL['option_range_days'];
		$range['day_360'] = '360 ' . $this->LL['option_range_days'];
		$range['no_limit'] = $this->LL['option_range_no_limit'];
		return $range;
	}

	/// \return Array with options for publish state dropdown
	private function getHiddenArray() {
		$hidden = array();
		$hidden['all'] = $this->LL['option_status_hidden_all'];
		$hidden['on'] = $this->LL['option_status_hidden_on'];
		$hidden['off'] = $this->LL['option_status_hidden_off'];
		return $hidden;
	}

	/// \return true if role filter equals the current role of the be_user, else false
	private function isRoleFilterEqualToUserRole() {
		return ($this->input['role'] ==  tx_newspaper_workflow::getRole());
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
        $categories = tx_newspaper_Tag::getAllControltagCategories();
        if (empty($categories)) return array();

        $tags = tx_newspaper_Tag::getAllControlTags($categories[0]['uid']);
        array_walk($tags, array($this, 'extractTagTitle'));
        return array_merge(array(''), $tags);
    }

    private function extractTagTitle(&$tag, $key) { $tag = $tag->getAttribute('title'); }

    private $LL = array();
    private $input = array();
}

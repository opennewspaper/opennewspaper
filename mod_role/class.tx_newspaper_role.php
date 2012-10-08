<?php
/***************************************************************
*  Copyright notice
*
*  Oliver Schr�der
*  Based on opendocs sys extension
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*  A copy is found in the textfile GPL.txt and important notices to the license
*  from the author is found in LICENSE.txt distributed with these scripts.
*
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

	// load the language file
$GLOBALS['LANG']->includeLLFile('EXT:newspaper/mod_role/locallang_role.xml');

require_once(PATH_typo3.'interfaces/interface.backend_toolbaritem.php');


/**
 * Adding newspaper role to the backend.php
 *
 * @author	Oliver Schröder
 * @package	TYPO3
 * @subpackage	newspaper
 */
class tx_newspaper_role implements backend_toolbarItem {

	private $extkey = 'newspaper';

	private $backendReference; // reference back to the backend object

	private $role = 0; // current role of be_user
	private $changeToRole = 0; // change role to this role

	private $docsAssignedToRole = 0; // numer of articles assigned to current role


	/**
	 * constructor, loads the documents from the user control
	 * \param	TYPO3backend	TYPO3 backend object reference
	 */
	public function __construct(TYPO3backend &$backendReference=null) {
		$this->backendReference = $backendReference;
	}

    /**
     * @return int Integer representing the newspaper role to change to
     */
    private function getRoleToChangeTo() {
		return (tx_newspaper_workflow::getRole() == NP_ACTIVE_ROLE_DUTY_EDITOR)?
			NP_ACTIVE_ROLE_EDITORIAL_STAFF :
			NP_ACTIVE_ROLE_DUTY_EDITOR;
	}

	/**
	 * Checks whether the user has access to this toolbar item
	 * @return boolean true if user has access, false if not
	 */
	public function checkAccess() {
		$conf = $GLOBALS['BE_USER']->getTSConfig('backendToolbarItem.tx_newsspaper_role.disabled');
		return ($conf['value'] == 1 ? false : true);
	}

	/**
	 * Renders the toolbar item and the initial menu
	 *
	 * @return string The toolbar item including the initial menu content as HTML
	 */
	public function render() {
		$this->addJavascriptToBackend();
		$this->addCssToBackend();
		$title = $GLOBALS['LANG']->getLL('toolbaritem', true);

			// toolbar item icon
		$menu[] = '<a href="#" class="toolbar-item"">';
		$menu[] = '<input type="text" id="tx-newspaper-role-role" disabled="disabled" value="' . tx_newspaper_Workflow::getRoleTitle(tx_newspaper_workflow::getRole()) . '" />';
		$menu[] = '<input type="text" id="tx-newspaper-role-counter" disabled="disabled" value="' . $this->docsAssignedToRole . '" />';
		$menu[] = '<img'.t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'], t3lib_extMgm::extRelPath($this->extkey) . '/mod_role/newspaper.png', 'width="23" height="16"').'  alt="' . $title . '" title="' . $title . '" /></a>';
		$menu[] = '</a>';

			// toolbar item menu and initial content
		$menu[] = '<div class="toolbar-item-menu" style="display: none;">';
		$menu[] = $this->renderMenu();
		$menu[] = '</div>';

		return implode("\n", $menu);
	}

	/**
	 * Renders the pure contents of the menu
	 * @return string The menu's content
	 */
	public function renderMenu() {
		$content = '<table class="list" cellspacing="0" cellpadding="0" border="0">';

		$label = $this->getChangeRoleLabel();
		$ajaxFunction = (tx_newspaper_Workflow::getRole() == NP_ACTIVE_ROLE_EDITORIAL_STAFF)? 'changeRoleToDutyEditor' : 'changeRoleToEditorialStaff';

		$content .= '<tr id="newspaper-role-changerole" class="newspaper-role first-row">
	<td class="label"><a href="#" target="content" onclick="TYPO3BackendNewspaperRole.toggleMenu(); TYPO3BackendNewspaperRole.' . $ajaxFunction . '(); return false;">' . $label . '</a></td>
</tr>
</table>';
		return $content;
	}

	// @return Localized String: current role and new role
	private function getChangeRoleLabel() {
		return $GLOBALS['LANG']->getLL('labelChangeRole', true) . ' -> ' . tx_newspaper_Workflow::getRoleTitle($this->getRoleToChangeTo());
	}


	/**
	 * Adds javascript to the backend
	 * @return	void
	 */
	protected function addJavascriptToBackend() {
		$this->backendReference->addJavascriptFile(t3lib_extMgm::extRelPath($this->extkey) . 'mod_role/newspaper_role.js');
	}

	/**
	 * Adds CSS to the backend
	 * @return	void
	 */
	protected function addCssToBackend() {
		$this->backendReference->addCssFile('newspaper-role', t3lib_extMgm::extRelPath($this->extkey) . 'mod_role/newspaper_role.css');
	}



	// AJAX functions
	public function changeRoleToEditorialStaff($params=array(), TYPO3AJAX &$ajaxObj=null) {
		$this->changeRole(NP_ACTIVE_ROLE_EDITORIAL_STAFF, $ajaxObj);
	}
	public function changeRoleToDutyEditor($params=array(), TYPO3AJAX &$ajaxObj=null) {
		$this->changeRole(NP_ACTIVE_ROLE_DUTY_EDITOR, $ajaxObj);
	}

    /**
     * Change the be_user's role to $role
     * @param $role
     * @param $ajaxObj
     */
    private function changeRole($role, $ajaxObj) {
		// Change the role
		tx_newspaper_workflow::changeRole(intval($role));

		// Render json (new menu and new label)
		$content = json_encode(array('roleLabel' => tx_newspaper_Workflow::getRoleTitle(tx_newspaper_Workflow::getRole()), 'menu' => $this->renderMenu()));

		// Set output
		$ajaxObj->setContentFormat('json');
		$ajaxObj->addContent('newspaperRoleMenu', $content);

	}


	/**
	 * Returns additional attributes for the list item in the toolbar
	 * @return	string	list item HTML attibutes
	 */
	public function getAdditionalAttributes() {
		return ' id="tx-newspaper-role-menu"';
	}

}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/newspaper/mod_role/class.tx_newspaper_role.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/newspaper/mod_role/class.tx_newspaper_role.php']);
}
?>
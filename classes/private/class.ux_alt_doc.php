<?php

class ux_SC_alt_doc extends SC_alt_doc {


	protected function getButtons()	{
//debug($this->elementsData, 'elementsData');
//debug($this->editconf, 'editconf');
//debug($this->elementsData[0]['table'], 'table');
//debug($_REQUEST, '$_request');

        // No newspaper stuff found, so let typo3 handle this on its own ;-)
		if (!tx_newspaper::startsWith($this->elementsData[0]['table'], 'tx_newspaper')) {
			return parent::getButtons();
		}

        // New Extra in Extra element browser, all buttons are needed (well, preview isn't really needed ...)
		if ($_REQUEST['tx_newspaper_mod1']['newExtraInElementBrowser']) {
			return parent::getButtons();
		}

        // Process article backend buttons
		if ($this->elementsData[0]['table'] == 'tx_newspaper_article') {
            return $this->processButtonsInArticleBackend();
		}


		// Hide, delete, save&new and save&preview buttons (and docheader2) for extras
		if (!tx_newspaper::isAbstractClass($this->elementsData[0]['table'])) {
			if (tx_newspaper::classImplementsInterface($this->elementsData[0]['table'], 'tx_newspaper_ExtraIface')) {
                return $this->processButtonsForExtraBackend();
            }
		}


		// no need to modify the button array for this newspaper record, so let Typo3 handle this call ...
		return parent::getButtons();

	}

    /**
     * Newspaper extra is being edited, don't show show delete, view, new and close buttons
     * @return array with buttons (like they are used in Typo3 doheader)
     */
    protected function processButtonsForExtraBackend() {
        $buttons = parent::getButtons();
        $buttons['save_view'] = '';
        $buttons['save_new'] = '';

        if ($this->hideCloseButtons()) {
            // no close buttons in placement module
            $buttons['save_close'] = '';
            $buttons['close'] = '';
        }
        // Mis-use delete button to add css code and hide docheader2
        $buttons['delete'] = self::getStyleToHideDocheader2();
        return $buttons;
    }

    /**
     * Add workflow buttons to newspaper articles (and remove docheader2)
     * @return array with buttons (like they are used in Typo3 doheader)
     */
    protected function processButtonsInArticleBackend() {
        global $TCA, $LANG;

        // clear button array
        $buttons = array(
            'save' => '',
            'save_view' => '',
            'save_new' => '',
            'save_close' => '',
            'close' => '',
            'delete' => '',
            'undo' => '',
            'history' => '',
            'columns_only' => '',
            'csh' => '',
            'translation_save' => '',
            'translation_saveclear' => '',
        );


        // Render SAVE type buttons and add newspaper workflow buttons
        // The action of each button is decided by its name attribute. (See doProcessData())
        if (!$this->errorC && !$TCA[$this->firstEl['table']]['ctrl']['readOnly']) {

            // SAVE button:
            $articleLabel = $LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:title_tx_newspaper_article', false);
            $buttons['save'] = $articleLabel . '<input type="image" onclick="return tabManagement.submitTabs(this);" class="c-inputButton" name="_savedok"' . t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/savedok.gif', '') . ' title="' . $LANG->sL('LLL:EXT:lang/locallang_core.php:rm.saveDoc', 1) . '" />';

            // SAVE / CLOSE
            $buttons['save_close'] = '<input type="image" onclick="return tabManagement.submitTabs(this);" class="c-inputButton" name="_saveandclosedok"' . t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/saveandclosedok.gif', '') . ' title="' . $LANG->sL('LLL:EXT:lang/locallang_core.php:rm.saveCloseDoc', 1) . '" />';

            // FINISH TRANSLATION / SAVE / CLOSE
            if ($GLOBALS['TYPO3_CONF_VARS']['BE']['explicitConfirmationOfTranslation']) {
                $buttons['translation_save'] = '<input type="image" class="c-inputButton" name="_translation_savedok"' . t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/translationsavedok.gif', '') . ' title="' . $LANG->sL('LLL:EXT:lang/locallang_core.php:rm.translationSaveDoc', 1) . '" />';
            }
        }


        // CLOSE button:
        // Some css definitions are added here ...
        $buttons['close'] = self::getStyleForDocheaderColor() . self::getStyleToHideDocheader2() . '
					<a href="#" onclick="document.editform.closeDoc.value=1; document.editform.submit(); return false;">' .
            '<img' . t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/closedok.gif', 'width="21" height="16"') . ' class="c-inputButton" title="' . $LANG->sL('LLL:EXT:lang/locallang_core.php:rm.closeDoc', 1) . '" alt="" />' .
            '</a>';


        // DELETE + UNDO buttons:
        if (!$this->errorC && !$TCA[$this->firstEl['table']]['ctrl']['readOnly'] && count($this->elementsData) == 1) {
            if ($this->firstEl['cmd'] != 'new' && t3lib_div::testInt($this->firstEl['uid'])) {

                // Delete:
                if ($this->firstEl['deleteAccess'] && !$TCA[$this->firstEl['table']]['ctrl']['readOnly'] && !$this->getNewIconMode($this->firstEl['table'], 'disableDelete')) {
                    $aOnClick = 'return deleteRecord(\'' . $this->firstEl['table'] . '\',\'' . $this->firstEl['uid'] . '\',unescape(\'' . rawurlencode($this->retUrl) . '\'));';
                    $buttons['delete'] = '<a href="#" onclick="' . htmlspecialchars($aOnClick) . '">' .
                        '<img' . t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/deletedok.gif', 'width="21" height="16"') . ' class="c-inputButton" title="' . $LANG->getLL('deleteItem', 1) . '" alt="" />' .
                        '</a>';
                }

                // Undo:
                $undoRes = $GLOBALS['TYPO3_DB']->exec_SELECTquery('tstamp', 'sys_history', 'tablename=' . $GLOBALS['TYPO3_DB']->fullQuoteStr($this->firstEl['table'], 'sys_history') . ' AND recuid=' . intval($this->firstEl['uid']), '', 'tstamp DESC', '1');
                if ($undoButtonR = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($undoRes)) {
                    $aOnClick = 'window.location.href=\'show_rechis.php?element=' . rawurlencode($this->firstEl['table'] . ':' . $this->firstEl['uid']) . '&revert=ALL_FIELDS&sumUp=-1&returnUrl=' . rawurlencode($this->R_URI) . '\'; return false;';
                    $buttons['undo'] = '<a href="#" onclick="' . htmlspecialchars($aOnClick) . '">' .
                        '<img' . t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/undo.gif', 'width="21" height="16"') . ' class="c-inputButton" title="' . htmlspecialchars(sprintf($LANG->getLL('undoLastChange'), t3lib_BEfunc::calcAge(time() - $undoButtonR['tstamp'], $LANG->sL('LLL:EXT:lang/locallang_core.php:labels.minutesHoursDaysYears')))) . '" alt="" />' .
                        '</a>';
                }
                if ($this->getNewIconMode($this->firstEl['table'], 'showHistory')) {
                    $aOnClick = 'window.location.href=\'show_rechis.php?element=' . rawurlencode($this->firstEl['table'] . ':' . $this->firstEl['uid']) . '&returnUrl=' . rawurlencode($this->R_URI) . '\'; return false;';
                    $buttons['history'] = '<a href="#" onclick="' . htmlspecialchars($aOnClick) . '">' .
                        '<img' . t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/history2.gif', 'width="13" height="12"') . ' class="c-inputButton" alt="" />' .
                        '</a>';
                }

                // If only SOME fields are shown in the form, this will link the user to the FULL form:
                if ($this->columnsOnly) {
                    $buttons['columns_only'] = '<a href="' . htmlspecialchars($this->R_URI . '&columnsOnly=') . '">' .
                        '<img' . t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/edit2.gif', 'width="11" height="12"') . ' class="c-inputButton" title="' . $LANG->getLL('editWholeRecord', 1) . '" alt="" />' .
                        '</a>';
                }
            }
        }

        // add the CSH icon
        $buttons['csh'] = t3lib_BEfunc::cshItem('xMOD_csh_corebe', 'TCEforms', $GLOBALS['BACK_PATH'], '', TRUE);
        $buttons['shortcut'] = $this->shortCutLink();
        $buttons['open_in_new_window'] = $this->openInNewWindowLink();

        // was read in parent::makeEditForm() before, but not stored in a accessible way
        $row = t3lib_BEfunc::getRecord($this->elementsData[0]['table'], $this->elementsData[0]['uid']);
//t3lib_div::devlog('XCLASS', 'newspaper', 0, array('row' => $row));

        // render all workflow buttons as 'save_close' buttons
        $buttons['save_close'] .= self::getJsForArticlePreview() . tx_newspaper_workflow::getWorkflowButtons($row);

        return $buttons;
    }


    /**
	 * Checks if close buttons should be hidden
	 * @return true if close buttons (close, save&close etc.) should be hidden
	 */
	private function hideCloseButtons() {

		if (t3lib_div::_GP('tx_newspaper_close_option') == 1) {
			return false; // this option forces the close buttons to be available
		}

		return !$this->isInPlacementModule();
	}

	/// very simple check if the users is editing in the placement module (mod3)
	private function isInPlacementModule() {
		return (strpos($this->returnUrl, 'newspaper/mod3/res/close.html') !== false);
	}

	private function getJsForArticlePreview() {
		return '<script type="text/javascript">
function showArticlePreview(article_uid) {
	var path = window.location.pathname;
	path = path.substring(0, path.lastIndexOf("/") - 5); // -5 -> cut of "typo3"
	var url = path + "typo3conf/ext/newspaper/mod7/index.php?tx_newspaper_mod7[controller]=preview&tx_newspaper_mod7[articleid]=" + article_uid;
	top.NpBackend.showArticlePreview(url);
}
</script>
<a href="#" onclick="showArticlePreview('. $this->elementsData[0]['uid'] . '); return false;">' . tx_newspaper_BE::renderIcon('gfx/zoom.gif', '', $GLOBALS['LANG']->sL('LLL:EXT:newspaper/mod2/locallang.xml:label.preview_article', false)) . '</a>';
	}


	private static function getStyleToHideDocheader2() {
		return '<!-- Hide docheader2 (csh and path info) -->
<style>
#typo3-docheader-row2 { display:none; }
div#typo3-docbody { top:20px; }
</style>
';
	}

    /**
     * Adds CSS for docheader text color if Typo3 4.5.x or higher
     * @return string CSS code or empty string
     */
    private function getStyleForDocheaderColor() {

        $typo3version = explode('.', TYPO3_branch);

        if ($typo3version[0] < 4 ) {
            return ''; // Typo3 3.x.x not supported
        }

        if ($typo3version[0] == 4 && $typo3version[1] < 5) {
            return ''; // The CSS code is needed for Typo3 4.5.x and above only
        }

        // Set text color to light gray
        return '<style>
div.buttongroup { color:#e0e0e0; }
</style>
';
    }

}

?>
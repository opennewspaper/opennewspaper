<?php

class ux_SC_alt_doc extends SC_alt_doc {


	protected function getButtons()	{
		global $TCA,$LANG;
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
		);

			// Render SAVE type buttons:
			// The action of each button is decided by its name attribute. (See doProcessData())
		if (!$this->errorC && !$TCA[$this->firstEl['table']]['ctrl']['readOnly'])	{

				// SAVE button:
			$buttons['save'] = 'Hallo Samuel<input type="image" class="c-inputButton" name="_savedok"'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/savedok.gif','').' title="'.$LANG->sL('LLL:EXT:lang/locallang_core.php:rm.saveDoc',1).'" />';

				// SAVE / VIEW button:
			if ($this->viewId && !$this->noView && t3lib_extMgm::isLoaded('cms')) {
				$buttons['save_view'] = '<input type="image" class="c-inputButton" name="_savedokview"'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/savedokshow.gif','').' title="'.$LANG->sL('LLL:EXT:lang/locallang_core.php:rm.saveDocShow',1).'" />';
			}

				// SAVE / NEW button:
			if (count($this->elementsData)==1 && $this->getNewIconMode($this->firstEl['table'])) {
				$buttons['save_new'] = '<input type="image" class="c-inputButton" name="_savedoknew"'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/savedoknew.gif','').' title="'.$LANG->sL('LLL:EXT:lang/locallang_core.php:rm.saveNewDoc',1).'" />';
			}

				// SAVE / CLOSE
			$buttons['save_close'] = '<input type="image" class="c-inputButton" name="_saveandclosedok"'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/saveandclosedok.gif','').' title="'.$LANG->sL('LLL:EXT:lang/locallang_core.php:rm.saveCloseDoc',1).'" />';

				// FINISH TRANSLATION / SAVE / CLOSE
			if ($GLOBALS['TYPO3_CONF_VARS']['BE']['explicitConfirmationOfTranslation'])	{
				$buttons['translation_save'] = '<input type="image" class="c-inputButton" name="_translation_savedok"'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/translationsavedok.gif','').' title="'.$LANG->sL('LLL:EXT:lang/locallang_core.php:rm.translationSaveDoc',1).'" />';
			}
		}

			// CLOSE button:
		$buttons['close'] = '<a href="#" onclick="document.editform.closeDoc.value=1; document.editform.submit(); return false;">'.
				'<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/closedok.gif','width="21" height="16"').' class="c-inputButton" title="'.$LANG->sL('LLL:EXT:lang/locallang_core.php:rm.closeDoc',1).'" alt="" />'.
				'</a>';


			// DELETE + UNDO buttons:
		if (!$this->errorC && !$TCA[$this->firstEl['table']]['ctrl']['readOnly'] && count($this->elementsData)==1)	{
			if ($this->firstEl['cmd']!='new' && t3lib_div::testInt($this->firstEl['uid']))	{

					// Delete:
				if ($this->firstEl['deleteAccess'] && !$TCA[$this->firstEl['table']]['ctrl']['readOnly'] && !$this->getNewIconMode($this->firstEl['table'],'disableDelete')) {
					$aOnClick = 'return deleteRecord(\''.$this->firstEl['table'].'\',\''.$this->firstEl['uid'].'\',unescape(\''.rawurlencode($this->retUrl).'\'));';
					$buttons['delete'] = '<a href="#" onclick="'.htmlspecialchars($aOnClick).'">'.
							'<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/deletedok.gif','width="21" height="16"').' class="c-inputButton" title="'.$LANG->getLL('deleteItem',1).'" alt="" />'.
							'</a>';
				}

					// Undo:
				$undoRes = $GLOBALS['TYPO3_DB']->exec_SELECTquery('tstamp', 'sys_history', 'tablename='.$GLOBALS['TYPO3_DB']->fullQuoteStr($this->firstEl['table'], 'sys_history').' AND recuid='.intval($this->firstEl['uid']), '', 'tstamp DESC', '1');
				if ($undoButtonR = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($undoRes))	{
					$aOnClick = 'window.location.href=\'show_rechis.php?element='.rawurlencode($this->firstEl['table'].':'.$this->firstEl['uid']).'&revert=ALL_FIELDS&sumUp=-1&returnUrl='.rawurlencode($this->R_URI).'\'; return false;';
					$buttons['undo'] = '<a href="#" onclick="'.htmlspecialchars($aOnClick).'">'.
							'<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/undo.gif','width="21" height="16"').' class="c-inputButton" title="'.htmlspecialchars(sprintf($LANG->getLL('undoLastChange'),t3lib_BEfunc::calcAge(time()-$undoButtonR['tstamp'],$LANG->sL('LLL:EXT:lang/locallang_core.php:labels.minutesHoursDaysYears')))).'" alt="" />'.
							'</a>';
				}
				if ($this->getNewIconMode($this->firstEl['table'],'showHistory'))	{
					$aOnClick = 'window.location.href=\'show_rechis.php?element='.rawurlencode($this->firstEl['table'].':'.$this->firstEl['uid']).'&returnUrl='.rawurlencode($this->R_URI).'\'; return false;';
					$buttons['history'] = '<a href="#" onclick="'.htmlspecialchars($aOnClick).'">'.
							'<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/history2.gif','width="13" height="12"').' class="c-inputButton" alt="" />'.
							'</a>';
				}

					// If only SOME fields are shown in the form, this will link the user to the FULL form:
				if ($this->columnsOnly)	{
					$buttons['columns_only'] = '<a href="'.htmlspecialchars($this->R_URI.'&columnsOnly=').'">'.
							'<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/edit2.gif','width="11" height="12"').' class="c-inputButton" title="'.$LANG->getLL('editWholeRecord',1).'" alt="" />'.
							'</a>';
				}
			}
		}

			// add the CSH icon
		$buttons['csh'] = t3lib_BEfunc::cshItem('xMOD_csh_corebe', 'TCEforms', $GLOBALS['BACK_PATH'], '', TRUE);
		$buttons['shortcut'] = $this->shortCutLink();
		$buttons['open_in_new_window'] = $this->openInNewWindowLink();
		return $buttons;
	}
	
}

?>
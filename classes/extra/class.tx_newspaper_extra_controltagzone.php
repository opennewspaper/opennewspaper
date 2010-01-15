<?php

require_once(PATH_typo3conf . 'ext/newspaper/classes/class.tx_newspaper_extra.php');

/// tx_newspaper_Extra displaying another Extra depending on Control Tags.
/** An article can have control tags associated with it. Depending on these tags
 *  Extras can be displayed exclusively with Articles which have these tags.
 * 
 *  Every Control Tag Zone Extra is associated to one Control Tag Zone, so the
 *  backend user knows where the Extra is placed. These Zones are named so the 
 *  user recognized where they are. The user must take care to place Control Tag
 *  Extras only in appropriate places. 
 * 
 *  \em Example: A Zone is called "Below Article". The user can place a Control
 *  Tag Zone Extra anywhere on any Page Zone, and \em should of course place it
 *  only below an Article.
 * 
 *  The correlation from Extras to Control Tags is done in the Backend Module
 *  \p mod6, tx_newspaper_module6.
 * 
 *  Attributes:
 *  - \p tag_zone (UID of tx_newspaper_tag_zone record)
 *  - \p tag_type (string)
 *  - \p default_extra (UIDs of tx_newspaper_Extra records)
 */
class tx_newspaper_Extra_ControlTagZone extends tx_newspaper_Extra {
	
	///	SQL table matching tx_newspaer_Extra s to Control Tags and Tag Zones
	const controltag_to_extra_table = 'tx_newspaper_controltag_to_extra';
	///	SQL table n which Tag Zones are stored
	const tag_zone_table = 'tx_newspaper_tag_zone';
	///	SQL table in which tx_newspaper_Tag s are stored
	const tag_table = 'tx_newspaper_tag';
	///	SQL table associating tx_newspaper_Tag s with tx_newspaper_Article s 
	const article_tag_mm_table = 'tx_newspaper_article_tags_mm';
		
	public function __construct($uid = 0) {
		if ($uid) {
			parent::__construct($uid); 
		}
	}
	
	public function __toString() {
		try {
		return 'Extra: UID ' . $this->getExtraUid() . ', Control tag Extra: UID ' . $this->getUid() .
				' (Tag zone: ' . $this->getAttribute('tag_zone') . ')';
		} catch(Exception $e) {
			return "ControlTagZone: Exception thrown!" . $e;
		}	
	}
	
	/// Assigns extras to be rendered to the smarty template and renders it.
	/** If no Extras match, returns nothing.
	 * 
	 *  Smarty template:
	 *  \include res/templates/tx_newspaper_extra_controltagzone.tmpl
	 */
	public function render($template_set = '') {
		
		$control_tags = $this->getControlTags();
		$extras = $this->getExtras($control_tags);
		
		t3lib_div::devlog('tx_newspaper_Extra_ControlTagZone::render()', 'newspaper', 0, array($control_tags, $extras));
		if (!$extras) return;
		
		$rendered_extras = array();
		foreach ($extras as $extra) {
			$rendered_extras[] = $extra->render();
		}
				
		$this->prepare_render($template_set);

		$this->smarty->assign('extras', $rendered_extras);
		
		return $this->smarty->fetch($this);
	}

	/// Displays the Tag Zone operating on.
	public function getDescription() {
		try {
			$tag_zone = tx_newspaper::selectOneRow(
				'name', self::tag_zone_table, 
				'uid = ' . $this->getAttribute('tag_zone')
			);
			return $this->getTitle() . '(' . $tag_zone['name'] . ')';
		} catch (tx_newspaper_DBException $e) { 
			global $LANG;
			return $this->getTitle() . ' (' .
				   $LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:message_no_controltag_selected', false) .
				   ')';
		}
		
	}

	public static function getModuleName() {
		return 'np_control_tag_extra';
	}
	
	public static function dependsOnArticle() { return false; }
	
	////////////////////////////////////////////////////////////////////////////
	
	/// Find out which control tags are currently active
	/** Reads the Control Tags associated with the currently displayed Article
	 *  from the article_tag_mm_table.
	 *  \return UIDs of control tags for the currently displayed Article
	 */
	private function getControlTags() {
		$tag_uids = array();
		
		if (intval(t3lib_div::_GP(tx_newspaper::GET_article()))) {
			$article = new tx_newspaper_article(t3lib_div::_GP(tx_newspaper::GET_article()));
			$tags = tx_newspaper::selectRows(
				self::tag_table . '.uid',
				self::tag_table . 
					' JOIN ' . self::article_tag_mm_table . 
					' ON ' . self::tag_table . '.uid = ' . self::article_tag_mm_table . '.uid_foreign',
				self::article_tag_mm_table . '.uid_local = ' . $article->getUid() .
				' AND ' . self::tag_table . '.tag_type = \'' . tx_newspaper::getControlTagType .'\''
			);

			foreach ($tags as $tag) $tag_uids[] = $tag['uid']; 
		}
		return $tag_uids;
	}
	
	///	Returns the Extras displayed for the Tag Zone of the object
	/** \param $control_tags Control tags present
	 *  \return Array of Extras which have been set up for the Tag Zone of the 
	 * 		tx_newspaper_Extra_ControlTagZone object and any of the control tags
	 * 		in \p $control_tags. Extras for the first matching tag are returned,
	 * 		the following tags are ignored.
	 */
	private function getExtras(array $control_tags) {
		$extra = array();
		
		///	Check if an Extra is assigned for the current tag zone for any control tag
		foreach ($control_tags as $control_tag) {
			$extras_data = tx_newspaper::selectRows(
				'extra_uid, extra_table', self::controltag_to_extra_table,
				'tag = ' . $control_tag .
				' AND tag_zone = ' . $this->getAttribute('tag_zone')
			);

			if ($extras_data) {
				foreach ($extras_data as $extra_data) {
					$extra[] = new $extra_data['extra_table']($extra_data['extra_uid']);
				}
				break;
			}
		}
		
		///	Check if default Extra(s) are set
		if (!$extra) {
			if ($this->getAttribute('default_extra')) {
				foreach (explode(',', $this->getAttribute('default_extra')) as $extra_uid) {
					$extra[] = tx_newspaper_Extra_Factory::getInstance()->create($extra_uid);
				}
			} else {
				return;
			}
		}
		return $extra;
	}
	
}

tx_newspaper_Extra::registerExtra(new tx_newspaper_Extra_ControlTagZone());

?>
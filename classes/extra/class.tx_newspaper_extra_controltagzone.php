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
 */
class tx_newspaper_Extra_ControlTagZone extends tx_newspaper_Extra {
	
	const controltag_to_extra_table = 'tx_newspaper_controltag_to_extra';
	const tag_zone_table = 'tx_newspaper_tag_zone';
	const tag_table = 'tx_newspaper_tag';
	const article_tag_mm_table = 'tx_newspaper_article_tags_mm';

	const control_tag_type = 'control';
		
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
	
	/** Assigns stuff to the smarty template and renders it.
	 *  \todo Just assign the attributes array, not specific attributes
	 */
	public function render($template_set = '') {
		
		$control_tags = $this->getControlTags();
		$extras = $this->getExtras($control_tags);
		t3lib_div::devlog('render()', 'newspaper', 0, array('control tags' => $control_tags, 'extras' => $extras));
		if (!$extras) return;
		
		$rendered_extras = array();
		foreach ($extras as $extra) {
			$rendered_extras[] = $extra->render();
		}
				
		$this->prepare_render($template_set);

		$this->smarty->assign('extras', $rendered_extras);
		
		return $this->smarty->fetch($this);
	}

	/** Displays the title and the beginning of the text.
	 */
	public function getDescription() {
		$tag_zone = tx_newspaper::selectOneRow(
			'name', self::tag_zone_table, 
			'uid = ' . $this->getAttribute('tag_zone')
		);
		return $this->getTitle() . '(' . $tag_zone['name'] . ')';
	}

	/// title for module
	public static function getModuleName() {
		return 'np_control_tag_extra';
	}
	
	public static function dependsOnArticle() { return false; }
	
	////////////////////////////////////////////////////////////////////////////
	
	/// Find out which control tags are currently active
	/** Just a dummy version for now
	 *  \return UIDs of control tags for the currently displayed Article
	 *  \todo implement
	 */
	private function getControlTags() {
		if (intval(t3lib_div::_GP(tx_newspaper::GET_article()))) {
			$article = new tx_newspaper_article(t3lib_div::_GP(tx_newspaper::GET_article()));
			$tags = tx_newspaper::selectRows(
				self::tag_table . '.uid',
				self::tag_table . 
					' JOIN ' . self::article_tag_mm_table . 
					' ON ' . self::tag_table . '.uid = ' . self::article_tag_mm_table . '.uid_foreign',
				self::article_tag_mm_table . '.uid_local = ' . $article->getUid() .
				' AND ' . self::tag_table . '.tag_type = \'' . self::control_tag_type .'\''
			);
			t3lib_div::devlog('getControlTags()', 'newspaper', 0, $tags);
		}
		return array(1,2,3,4);
	}
	
	private function getExtras(array $control_tags) {
		$extra = array();
		
		///	Check if an Extra is assigned for the current tag zone for any control tag
		foreach ($control_tags as $control_tag) {
			$extras_data = tx_newspaper::selectRows(
				'extra_uid, extra_table', self::controltag_to_extra_table,
				'tag = ' . $control_tag .
				' AND tag_zone = ' . $this->getAttribute('tag_zone')
			);
			t3lib_div::devlog('getExtras()', 'newspaper', 0, array('query'=>tx_newspaper::$query, 'result'=>$extras_data));
			if ($extras_data) {
				foreach ($extras_data as $extra_data) {
					$extra[] = new $extra_data['extra_table']($extra_data['extra_uid']);
				}
				t3lib_div::devlog('Extras', 'newspaper', 0, $extra);
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
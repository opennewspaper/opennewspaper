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

	const description_length = 50; 
	
	const controltag_to_extra_table = 'tx_newspaper_controltag_to_extra';
	
	public function __construct($uid = 0) {
		if ($uid) {
			parent::__construct($uid); 
		}
	}
	
	public function __toString() {
		try {
		return 'Extra: UID ' . $this->getExtraUid() . ', Textbox Extra: UID ' . $this->getUid() .
				' (Title: ' . $this->getAttribute('title') . ')';
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
		return substr(
			'<strong>' . $this->getAttribute('title') . '</strong> ' . $this->getAttribute('text'), 
			0, self::description_length+2*strlen('<strong>')+1);
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
		return array(1,2);
	}
	
	private function getExtras(array $control_tags) {
		$extra = array();
		
		///	Check if an Extra is assigned for the current tag zone for any control tag
		foreach ($control_tags as $control_tag) {
			$extra_data = tx_newspaper::selectZeroOrOneRows(
				'extra_uid, extra_table', self::controltag_to_extra_table,
				'tag = ' . $this->getAttribute('tag') .
				' AND tag_zone = ' . $this->getAttribute('tag_zone')
			);
			if ($extra_data) {
				$extra[] = new $extra_data['extra_table']($extra_data['extra_uid']);
				break;
			}
		}
		
		///	Check if default Extra(s) are set
		if (!$extra) {
			if ($this->getAttribute('default_extra')) {
				foreach (explode(',', $this->getAttribute('default_extra')) as $extra_uid) {
					$extra[] = tx_newspaper_Extra_Factory::getInstance()->create($extra_uid);
				}
			} else return;
		}
	}
}

tx_newspaper_Extra::registerExtra(new tx_newspaper_Extra_ControlTagZone());

?>
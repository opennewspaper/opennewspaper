<?php

require_once(PATH_typo3conf . 'ext/newspaper/classes/class.tx_newspaper_extra.php');

/// tx_newspaper_Extra displaying other Extras inside it.
/** 
 *  Attributes:
 *  - \p extras (UIDs of tx_newspaper_Extra records)
 */
class tx_newspaper_Extra_Container extends tx_newspaper_Extra {
	
	///	SQL table matching tx_newspaer_Extra s to Control Tags and Tag Zones
	const controltag_to_extra_table = 'tx_newspaper_controltag_to_extra';
	///	SQL table n which Tag Zones are stored
	const tag_zone_table = 'tx_newspaper_tag_zone';
	///	SQL table in which tx_newspaper_Tag s are stored
	const tag_table = 'tx_newspaper_tag';
	///	SQL table associating tx_newspaper_Tag s with tx_newspaper_Article s 
	const article_tag_mm_table = 'tx_newspaper_article_tags_mm';

	///	\p tag_type field's value for Control Tags
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
	
	/// Assigns extras to be rendered to the smarty template and renders it.
	/** If no Extras match, returns nothing.
	 * 
	 *  Smarty template:
	 *  \include res/templates/tx_newspaper_extra_controltagzone.tmpl
	 */
	public function render($template_set = '') {
		
		$extras = $this->getExtras();
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
		$tag_zone = tx_newspaper::selectOneRow(
			'name', self::tag_zone_table, 
			'uid = ' . $this->getAttribute('extras')
		);
		return $this->getTitle() . '(' . $tag_zone['name'] . ')';
	}

	public static function getModuleName() {
		return 'np_extra_container';
	}
	
	public static function dependsOnArticle() { return false; }
	
	////////////////////////////////////////////////////////////////////////////
	
	///	Returns the Extras displayed in this Extra
	/** 
	 */
	private function getExtras() {
		$extra = array();

		foreach (explode(',', $this->getAttribute('extras')) as $uid) {
			$extra[] = tx_newspaper_ExtraFactory::getInstance()->create();
		}

		return $extra;
	}
	
}

tx_newspaper_Extra::registerExtra(new tx_newspaper_Extra_Container());

?>
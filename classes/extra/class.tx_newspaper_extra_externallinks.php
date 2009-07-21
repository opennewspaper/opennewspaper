<?php

require_once(PATH_typo3conf . 'ext/newspaper/classes/class.tx_newspaper_extra.php');

class tx_newspaper_ExternalLink {
	public function __construct($uid) {
		$this->text = 'dummy text';
		$this->url = 'dummy URL';
		$this->target = '';
		
		/// \todo select from DB
		$row = tx_newspaper::selectOneRow(
			'*', self::$table, 'uid = ' . intval($uid)
		);
		
		$this->text = $row['text'];
		$this->url = $row['url'];
		$this->target = $row['target'];
	}
	
	public function getText() { 
		return $this->text? $this->text: $this->url; 
	}
	
	public function getURL() {
		return $this->url;
	}
	
	public function getTarget() {
		return $this->target;
	}
	
	////////////////////////////////////////////////////////////////////////////
	
	private $text = null;
	private $url = null;
	private $target = null;
	
	private static $table = 'tx_newspaper_externallinks';
}

class tx_newspaper_Extra_ExternalLinks extends tx_newspaper_Extra {

	public function __construct($uid = 0) {
		if ($uid) {
			parent::__construct($uid); 
		}
	}
	
	public function __toString() {
		try {
		return 'Extra: UID ' . $this->getExtraUid() . ', External Links Extra: UID ' . $this->getUid() .
				' (Links: ' . $this->getAttribute('links') . ')';
		} catch(Exception $e) {
			return "External Links: Exception thrown!" . $e;
		}	
	}
	
	/** Just a quick hack to see anything
	 */
	public function render($template_set = '') {

		$this->prepare_render($template_set);

		$this->smarty->assign('title', $this->getAttribute('title'));
		$this->smarty->assign('links', $this->getLinks());
	
		return $this->smarty->fetch($this);
	}

	/// \todo getLLL
	public function getTitle() {
		return 'External Links';
	}

	public function getDescription() {
		return '<strong>' . $this->getAttribute('links') . '</strong> ';
	}

	/// title for module
	public static function getModuleName() {
		return 'np_textbox';
	}
	
	public static function dependsOnArticle() { return true; }
	
	////////////////////////////////////////////////////////////////////////////
	
	private function getLinks() {
		if (!$this->links) {
			foreach (explode(',', trim($this->getAttribute('links'))) as $link_uid) {
				$this->links[] = new tx_newspaper_ExternalLink($link_uid);
			}
		}
		
		return $this->links;
	}
	
	private $links = array();
	
}

tx_newspaper_Extra::registerExtra(new tx_newspaper_Extra_ExternalLinks());

?>
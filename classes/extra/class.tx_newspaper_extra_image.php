<?php

require_once(PATH_typo3conf . 'ext/newspaper/classes/class.tx_newspaper_extra.php');

class tx_newspaper_Extra_Image extends tx_newspaper_Extra {

	public function __construct($uid = 0) {
		if ($uid) {
			parent::__construct($uid); 
		}
//		$this->smarty = new tx_newspaper_Smarty();
	}
	
	public function __toString() {
		$ret = '';
		try{
			$ret .= 'Extra: UID ' . $this->getExtraUid() . ', Image: UID ' . $this->getUid();
			$ret .= ' (Title: ' . $this->getAttribute('title') . ')';
		} catch (Exception $e) {  }
		return $ret;	
	}
	
	/** Just a quick hack to see anything
	 */
	public function render($template_set = '') {
		$this->prepare_render($template_set);
		
		$this->smarty->assign('title', $this->getAttribute('title'));
		$this->smarty->assign('image', $this->getAttribute('image'));
		$this->smarty->assign('caption', $this->getAttribute('caption'));
		return $this->smarty->fetch($this);
	}

	/// A short description that makes an Extra uniquely identifiable in the BE
	public function getDescription() {
		return $this->getAttribute('title') . ' (#' . $this->getUid() . ')';
	}

//TODO: getLLL
	public function getTitle() {
		return 'Image';
	}

// title for module
	public static function getModuleName() {
		return 'np_image';
	}
	
	public static function dependsOnArticle() { return false; }
	
}

tx_newspaper_Extra::registerExtra(new tx_newspaper_extra_image());

//  Popups displaying enlarged images are handled here
if ($_GET['bild_fuer_artikel']) { 
	//...
}
?>
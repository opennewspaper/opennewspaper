<?php

require_once(PATH_typo3conf . 'ext/newspaper/classes/class.tx_newspaper_extraimpl.php');

class tx_newspaper_extra_ArticleRenderer extends tx_newspaper_Extra {

	public function __construct($uid = 0) { 
		if ($uid) {
			parent::__construct($uid); 
			$this->attributes = $this->readExtraItem($uid, $this->getTable());		
		}
	}
	
	/** Just a quick hack to see anything
	 *  \todo use smarty.
		\todo this is vastly over-simplified. we must insert all the extras at
		their appropriate place. To this end, we must split the text returned by
		$article->render() into paragraphs, insert extras where desired, and 
		assemble the text back into one piece. 
	 */
	public function render($template = '') {
		$article = new tx_newspaper_ArticleImpl($_GET['art']);
		return $article->render();
	}

	static function getTitle() {
		return 'ArticleRenderer';
	}

	static function getModuleName() {
		return 'np_artrend'; 
	}
}

tx_newspaper_Extra::registerExtra(new tx_newspaper_extra_ArticleRenderer());

?>
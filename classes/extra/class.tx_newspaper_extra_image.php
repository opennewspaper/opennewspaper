<?php

require_once(BASEPATH . '/typo3conf/ext/newspaper/classes/class.tx_newspaper_extraimpl.php');

class tx_newspaper_extra_image extends tx_newspaper_ExtraImpl {

	/** Just a quick hack to see anything
	 *  \todo use smarty
	 */
	public function render($template = '') {
		$ret = '<h4>' . $this->getAttribute('title') . "</h4>\n" .
		'<p>' .
		'<img src="' . $this->OutputToDataURI() .
		   '" alt="' . $this->getAttribute('caption') . '">';
		"</p>\n" . $this->getAttribute('caption') . "\n";
		
		return $ret;
	}


// internal name
	static function getName() {
		return 'tx_newspaper_extra_image';
	}

//TODO: getLLL
	static function getTitle() {
		return 'Image';
	}

// title for module
	static function getModuleName() {
		return 'npe_image'; // this is the default folder for data associated with newspaper etxension, overwrite in conrete Extras
	}

	/// Print images inline as data: URI
	/** Snatched from 
	 *  http://www.sencer.de/article/1135/how-to-inline-your-sparklinesimages-in-php-with-data-uris
	 */
	private function OutputToDataURI() {
		header('Content-type: text/html');
		return "data:image/png;base64,".base64_encode($this->getAttribute('image'));  
	} // function OutputToDataURI

}

?>
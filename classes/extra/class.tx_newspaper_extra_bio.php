<?php

require_once(PATH_typo3conf . 'ext/newspaper/classes/extra/class.tx_newspaper_extra_image.php');

/// A tx_newspaper_Extra that displays a bio for a contributor
/** A photo of an author is displayed along with some biographical text.
 *  
 *  Attributes:
 *  - \p template_set (string)
 *  - \p pool (bool)
 *  - \p author_name (string)
 *  - \p author_id (string)
 *  - \p image_file (string)
 *  - \p photo_source (string)
 *  - \p bio_text (string)
 * 
 *	The render() function is inherited from tx_newspaper_Extra_Image, but of
 *  course uses tx_newspaper_extra_bio.tmpl. Ah, the joys of inheritance...
 *  \include res/templates/tx_newspaper_extra_bio.tmpl
 * 
 *  \todo Import the box automatically from the pool when the Article is
 * 		imported.
 */
class tx_newspaper_extra_Bio extends tx_newspaper_Extra_Image {

	const description_length = 50; 

	/// Boa Constructor ;-)
	public function __construct($uid = 0) { 
		if (intval($uid)) {
			parent::__construct($uid); 
		}
	}
	
	/// A description to identify the bio box in the BE
	/** Shows the author's name and the start of the text.
	 */
	public function getDescription() {
		return substr(
			'<strong>' . $this->getAttribute('author_name') . '</strong> ' .
				$this->getAttribute('bio_text'), 
			0, self::description_length+2*strlen('<strong>')+1) .
			(strlen($this->getAttribute('author_name') . ' ' . $this->getAttribute('bio_text')) > self::description_length?
				'...': '');
	}

	public static function getModuleName() {
		return 'np_bio'; 
	}
	
	public static function dependsOnArticle() { return true; }
}

tx_newspaper_Extra::registerExtra(new tx_newspaper_extra_Bio());

?>
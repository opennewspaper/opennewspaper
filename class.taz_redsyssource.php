<?php
/**
 *  \file class.tx_newspaper_taz_redsyssource.php
 *
 *  \author Helge Preuss <helge.preuss@gmx.net>
 *  \date Oct 27, 2008
 */

require_once(BASEPATH.'/typo3conf/ext/newspaper/interface.tx_newspaper_source.php');
require_once(BASEPATH.'/typo3conf/ext/newspaper/interface.tx_newspaper_article.php');
require_once(BASEPATH.'/typo3conf/ext/newspaper/interface.tx_newspaper_extra.php');

/// A Source which reads articles from the taz redaktionssystem
class tx_newspaper_taz_RedsysSource implements tx_newspaper_Source {

	/** \param $config red.cfg file that defines the redsys
	 */
	public function __construct($config) {
       	if (file_exists($config)) {
       		$this->red_private = red_popen($config);
       	} else {
       		throw new SourceOpenFailedException("Konnte Redaktionsbereich ".
				"$config nicht oeffnen. Konfigurationsdatei existiert nicht!");
       	}

        if (!$this->red_private) {
			throw new SourceOpenFailedException("Konnte Redaktionsbereich ".
				"$config nicht oeffnen. red_popen() failed!");
        }

        red_set_in_filter($this->red_private, self::$red_filter);
	}

	public function __destruct() {
		$this->closeText();
	}

	/// Reads ONE field for the given Article (-> Source)
	/** \todo this should work for Extra too. Easy? Just replace Article with 
	 *  Extra?
	 */
	public function readField(tx_newspaper_Article $article, $field, $uid) {
		if (!$this->text) $this->text = red_text_open($this->red_private, $uid);

		$value = red_text_get($this->text, 
							  $article->mapFieldToSourceField($field, $this));		

		$article->setAttribute($field, $value);
	}

	/// Reads the specified fields of the article with the specified UID (-> Source)
	public function readFields(tx_newspaper_Article $article, array $fieldList, $uid) {
		$this->text = red_text_open($this->red_private, $uid);

		foreach ($fieldList as $field) $this->readField($article, $field, $uid);

		$this->closeText();
	}

	/// Creates and reads a full article with the specified UID (-> Source)
	public function readArticle($articleclass, $uid) {
		$article = null;
		
		/// $article is set to an object of an appropriate class
		if (is_a($articleclass, 'Article')) {
			$article = $articleclass;
			$articleclass = get_class($article);
		} else {
			if (class_exists($articleclass)) $article = new $articleclass;
			else throw new WrongClassException($articleclass);
		}
		
		/// If that didn't work, throw up
		if (!is_a($article, 'Article')) {
			throw new WrongClassException($articleclass);
		}
		
		/// Finally read all required attributes for $article
		foreach ($article->getAttributeList() as $field) {
			$this->readField($article, $field, $uid);
		}

		/// And tell the Article the truth: "I'm your father, Luke"
		$article->setSource($this);
		$article->setUid($uid);

		$this->closeText();
						 
		return $article;
	}

	/// Reads an array of articles with the specified UIDs (-> Source)
	public function readArticles($articleclass, array $uids) {
		$articles = array();
		foreach ($uids as $uid) {
			$articles[] = $this->readArticle($articleclass, $uid);
		}
		return $articles;
	}

	/// Reads array of only the specified fields of articles with the given UIDs 
	public function readPartialArticles($articleclass,array $fields, array $uids) {
		throw new NotYetImplementedException("taz_RedsysSource::readPartialArticles()");
	}

    /// reads an extra (-> Source)
    public function readExtra($extraclass, $uid) {
		throw new NotYetImplementedException("taz_RedsysSource::readExtra()");
    }

    /// reads an array of extras (-> Source)
    public function readExtras($extraclass, array $uids) {
		throw new NotYetImplementedException("taz_RedsysSource::readExtras()");
    }

    public function writeArticle(tx_newspaper_Article $article, $uid) {
    	throw new NotYetImplementedException("taz_RedsysSource::writeArticle()");
    }
    
    public function writeExtra(tx_newspaper_Extra $extra, $uid) {
    	throw new NotYetImplementedException("taz_RedsysSource::writeExtra()");
    }

	////////////////////////////////////////////////////////////////////////////
	//		end of public interface											  //
	////////////////////////////////////////////////////////////////////////////

	/// If a text resource is already opened, release the resource
	private function closeText() {
		if ($this->text) red_text_close($this->text);
		$this->text = null;
	}

    private $red_private = null;	///< The RedSys resource
    private $text = null;			///< A red_text resource
    
    private static $red_filter = "tkr2html"; 
}
?>

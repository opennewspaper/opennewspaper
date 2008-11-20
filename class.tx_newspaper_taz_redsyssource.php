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

require_once(BASEPATH.'/typo3conf/ext/newspaper/class.tx_newspaper_sourcebehavior.php');

/// A Source which reads articles from the taz redaktionssystem
class tx_newspaper_taz_RedsysSource implements tx_newspaper_Source {

	/** \param $config red.cfg file that defines the redsys
	 */
	public function __construct($config) {
		
		$this->sourceBehavior = new tx_newspaper_SourceBehavior($this);
		
       	if (file_exists($config)) {
       		$this->red_private = red_popen($config);
       	} else {
       		throw new tx_newspaper_SourceOpenFailedException("Konnte Redaktionsbereich ".
				"$config nicht oeffnen. Konfigurationsdatei existiert nicht!");
       	}

        if (!$this->red_private) {
			throw new tx_newspaper_SourceOpenFailedException("Konnte Redaktionsbereich ".
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

		$this->sourceBehavior->readFields($article, $fieldList, $uid);

		$this->closeText();
	}

	/// Creates and reads a full article with the specified UID (-> Source)
	public function readArticle($articleclass, $uid) {
		$article = $this->sourceBehavior->readArticle($articleclass, $uid);
		$this->closeText();
		return $article;
	}

	/// Reads an array of articles with the specified UIDs (-> Source)
	public function readArticles($articleclass, array $uids) {
		return  $this->sourceBehavior->readArticles($articleclass, $uids);
	}

	/// Reads array of only the specified fields of articles with the given UIDs 
	public function readPartialArticles($articleclass,array $fields, array $uids) {
		throw new tx_newspaper_NotYetImplementedException("taz_RedsysSource::readPartialArticles()");
	}

    /// reads an extra (-> Source)
    public function readExtra($extraclass, $uid) {
		throw new tx_newspaper_NotYetImplementedException("taz_RedsysSource::readExtra()");
    }

    /// reads an array of extras (-> Source)
    public function readExtras($extraclass, array $uids) {
		throw new tx_newspaper_NotYetImplementedException("taz_RedsysSource::readExtras()");
    }

    public function writeArticle(tx_newspaper_Article $article, $uid) {
    	throw new tx_newspaper_NotYetImplementedException("taz_RedsysSource::writeArticle()");
    }
    
    public function writeExtra(tx_newspaper_Extra $extra, $uid) {
    	throw new tx_newspaper_NotYetImplementedException("taz_RedsysSource::writeExtra()");
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
    
    private $sourceBehavior = null; 
}
?>

<?php
/**
 *  \file class.tx_newspaper_taz_redsyssource.php
 *
 *  \author Helge Preuss <helge.preuss@gmx.net>
 *  \date Oct 27, 2008
 */

require_once(BASEPATH.'/typo3conf/ext/newspaper/interfaces/interface.tx_newspaper_source.php');
require_once(BASEPATH.'/typo3conf/ext/newspaper/interfaces/interface.tx_newspaper_article.php');
require_once(BASEPATH.'/typo3conf/ext/newspaper/interfaces/interface.tx_newspaper_extra.php');

require_once(BASEPATH.'/typo3conf/ext/newspaper/classes/class.tx_newspaper_sourcebehavior.php');

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
	public function readField(tx_newspaper_Extra $extra, $field, tx_newspaper_SourcePath $uid) {
		if (!$this->text) $this->text = red_text_open($this->red_private, $uid->getID());

		$value = red_text_get($this->text, 
							  $extra->mapFieldToSourceField($field, $this));		

		$extra->setAttribute($field, $value);
	}

	/// Reads the specified fields of the article with the specified UID (-> Source)
	public function readFields(tx_newspaper_Extra $extra, array $fieldList, tx_newspaper_SourcePath $uid) {
		$this->text = red_text_open($this->red_private, $uid->getID());

		$this->sourceBehavior->readFields($extra, $fieldList, $uid);

		$this->closeText();
	}

	/// Creates and reads a full article with the specified UID
	public function readArticle($articleclass, tx_newspaper_SourcePath $uid) {
		$article = $this->sourceBehavior->readArticle($articleclass, $uid);
		$this->closeText();
		return $article;
	}

	/// Reads an array of articles with the specified UIDs
	public function readArticles($articleclass, array $uids) {
		return  $this->sourceBehavior->readArticles($articleclass, $uids);
	}

	/// Reads array of only the specified fields of articles with the given UIDs 
	public function readPartialArticles($articleclass,array $fields, array $uids) {
		throw new tx_newspaper_NotYetImplementedException();
	}

    /// reads an extra
    public function readExtra($extraclass, tx_newspaper_SourcePath $uid) {
		throw new tx_newspaper_NotYetImplementedException();
    }

    /// reads an array of extras
    public function readExtras($extraclass, array $uids) {
		throw new tx_newspaper_NotYetImplementedException();
    }

    public function writeArticle(tx_newspaper_Article $article, tx_newspaper_SourcePath $uid) {
    	throw new tx_newspaper_NotYetImplementedException();
    }
    
    public function writeExtra(tx_newspaper_Extra $extra, tx_newspaper_SourcePath $uid) {
    	throw new tx_newspaper_NotYetImplementedException();
    }

	/** This function requires intimate knowledge of how the taz's editing system
	 *  works and is guaranteed to be non-portable.
	 */
    public function browse(tx_newspaper_SourcePath $path) {
    	$paths = array();
    	
    	if (file_exists(red_get_var($this->red_private, 'TxtBaseDir') ."/$path/dir.list")) {
	    	foreach(array_keys(red_list_read($this->red_private, "$path/dir.list")) as $subdir)
	    		$paths[] = new tx_newspaper_SourcePath($path->getID() . "/$subdir");
    	}
    	if (file_exists(red_get_var($this->red_private, 'TxtBaseDir')."/$path/quelle.list")) {
    		$quellen = red_list_read($this->red_private, "$path/quelle.list");
    		foreach ($quellen as $quelle => $quellendescription) {
		    	if (!file_exists(red_get_var($this->red_private, 'TxtBaseDir')."/$path/$quelle.list")) {
		    		throw new tx_newspaper_InconsistencyException(
						"$quelle listed in $path/quelle.list as '$quellendescription', but no $quelle.list exists"
					);
		    	}
		    	$ressorts = red_list_read($this->red_private, "$path/$quelle.list");
		    	foreach ($ressorts as $ressort => $ressortdescription) {
			    	if (!file_exists(red_get_var($this->red_private, 'TxtBaseDir')."/$path/{$quelle}_{$ressort}.list")) {
			    		throw new tx_newspaper_InconsistencyException(
							"$ressort listed in $path/$quelle.list as '$ressortdescription', but no {$quelle}_{$ressort}.list exists"
						);
			    	}
			    	foreach(red_list_read($this->red_private, "$path/{$quelle}_{$ressort}.list") as $article)
	    				$paths[] = new tx_newspaper_SourcePath($path->getID() . "/$article");
		    	}
    		}
    	}
    	return $paths;
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
    
    private $sourceBehavior = null; ///< Object to delegate operations to

    private static $red_filter = "tkr2html";
    
}
?>

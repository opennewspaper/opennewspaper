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
class tx_newspaper_taz_DBSource implements tx_newspaper_Source {

	/** \param $config red.cfg file that defines the redsys
	 */
	public function __construct() {
		$this->sourceBehavior = new tx_newspaper_SourceBehavior($this);
	}

	public function __destruct() {
	}

	/// Reads ONE field for the given Article (-> Source)
	/** \todo this should work for Extra too. Easy? Just replace Article with 
	 *  Extra?
	 */
	public function readField(tx_newspaper_Article $article, $field, $uid) {
		$query = $GLOBALS['TYPO3_DB']->SELECTquery(
			$article->mapFieldToSourceField($field, $this),
			/// \todo How do I know the table for the Article or Extra? 
			$article->sourceTable($this),
			"uid = ".intval($uid)
		);
		$res =  $GLOBALS['TYPO3_DB']->sql_query($query);
        if (!$res) {
        	/// \todo Graceful error handling
            return "Couldn't retrieve article #$uid";
        }

        $row =  $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
        if (!$row) {
        	/// \todo Graceful error handling
        	return "Article #$uid has no article_id field";
        }
		$value = $row[$article->mapFieldToSourceField($field, $this)];		

		$article->setAttribute($field, $value);
	}

	/// Reads the specified fields of the article with the specified UID (-> Source)
	public function readFields(tx_newspaper_Article $article, array $fieldList, $uid) {
		$this->sourceBehavior->readFields($article, $fieldList, $uid);
//		foreach ($fieldList as $field) $this->readField($article, $field, $uid);
	}

	/// Creates and reads a full article with the specified UID (-> Source)
	public function readArticle($articleclass, $uid) {
		return $this->sourceBehavior->readArticle($articleclass, $uid);
/*		$article = null;
		
		/// $article is set to an object of an appropriate class
		if (is_a($articleclass, 'tx_newspaper_Article')) {
			$article = $articleclass;
			$articleclass = get_class($article);
		} else {
			if (class_exists($articleclass)) $article = new $articleclass;
			else throw new tx_newspaper_WrongClassException($articleclass);
		}
		
		/// If that didn't work, throw up
		if (!is_a($article, 'tx_newspaper_Article')) {
			throw new tx_newspaper_WrongClassException($articleclass);
		}
		
		/// Finally read all required attributes for $article
		foreach ($article->getAttributeList() as $field) {
			$this->readField($article, $field, $uid);
		}

		/// And tell the Article the truth: "I'm your father, Luke"
		$article->setSource($this);
		$article->setUid($uid);
						 
		return $article;
*/	}

	/// Reads an array of articles with the specified UIDs (-> Source)
	public function readArticles($articleclass, array $uids) {
		return  $this->sourceBehavior->readArticles($articleclass, $uids);
/*		$articles = array();
		foreach ($uids as $uid) {
			$articles[] = $this->readArticle($articleclass, $uid);
		}
		return $articles;
*/	}

	/// Reads array of only the specified fields of articles with the given UIDs 
	public function readPartialArticles($articleclass,array $fields, array $uids) {
		throw new tx_newspaper_NotYetImplementedException("taz_DBSource::readPartialArticles()");
	}

    /// reads an extra (-> Source)
    public function readExtra($extraclass, $uid) {
		throw new tx_newspaper_NotYetImplementedException("taz_DBSource::readExtra()");
    }

    /// reads an array of extras (-> Source)
    public function readExtras($extraclass, array $uids) {
		throw new tx_newspaper_NotYetImplementedException("taz_DBSource::readExtras()");
    }

    public function writeArticle(tx_newspaper_Article $article, $uid) {
    	throw new tx_newspaper_NotYetImplementedException("taz_DBSource::writeArticle()");
    }
    
    public function writeExtra(tx_newspaper_Extra $extra, $uid) {
    	throw new tx_newspaper_NotYetImplementedException("taz_DBSource::writeExtra()");
    }

	////////////////////////////////////////////////////////////////////////////
	//		end of public interface											  //
	////////////////////////////////////////////////////////////////////////////

    private $sourceBehavior = null; 

}
?>

<?php
/**
 *  \file class.tx_newspaper_sourcebehavior.php
 *
 *  \author Helge Preuss <helge.preuss@gmx.net>
 *  \date Nov 20, 2008
 */

require_once(BASEPATH.'/typo3conf/ext/newspaper/interface.tx_newspaper_source.php');
require_once(BASEPATH.'/typo3conf/ext/newspaper/interface.tx_newspaper_article.php');
require_once(BASEPATH.'/typo3conf/ext/newspaper/interface.tx_newspaper_extra.php');

/// Behavior class to factor out code common to more or less all Source implementations
class tx_newspaper_SourceBehavior {

	/** \param $parent The tx_newspaper_Source object using this Behavior
	 */ 
	public function __construct(tx_newspaper_Source $parent) {
		$this->parentSource = $parent;
	}

	/// Reads the specified fields of the article with the specified UID
	public function readFields(tx_newspaper_Article $article, array $fieldList, $uid) {
		foreach ($fieldList as $field) { 
			$this->parentSource->readField($article, $field, $uid);
		}
	}

	/// Creates and reads a full article with the specified UID
	public function readArticle($articleclass, $uid) {
		$article = null;
		
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
			$this->parentSource->readField($article, $field, $uid);
		}

		/// And tell the Article the truth: "I'm your father, Luke"
		$article->setSource($this->parentSource);
		$article->setUid($uid);
						 
		return $article;
	}

	/// Reads an array of articles with the specified UIDs (-> Source)
	public function readArticles($articleclass, array $uids) {
		$articles = array();
		foreach ($uids as $uid) {
			$articles[] = $this->parentSource->readArticle($articleclass, $uid);
		}
		return $articles;
	}

	/// Reads array of only the specified fields of articles with the given UIDs 
	public function readPartialArticles($articleclass,array $fields, array $uids) {
		throw new tx_newspaper_NotYetImplementedException("tx_newspaper_SourceBehavior::readPartialArticles()");
	}

    /// reads an extra (-> Source)
    public function readExtra($extraclass, $uid) {
		throw new tx_newspaper_NotYetImplementedException("tx_newspaper_SourceBehavior::readExtra()");
    }

    /// reads an array of extras (-> Source)
    public function readExtras($extraclass, array $uids) {
		throw new tx_newspaper_NotYetImplementedException("tx_newspaper_SourceBehavior::readExtras()");
    }

    public function writeArticle(tx_newspaper_Article $article, $uid) {
    	throw new tx_newspaper_NotYetImplementedException("tx_newspaper_SourceBehavior::writeArticle()");
    }
    
    public function writeExtra(tx_newspaper_Extra $extra, $uid) {
    	throw new tx_newspaper_NotYetImplementedException("tx_newspaper_SourceBehavior::writeExtra()");
    }

	////////////////////////////////////////////////////////////////////////////
	//		end of public interface											  //
	////////////////////////////////////////////////////////////////////////////

	private $parentSource = null;
}
?>

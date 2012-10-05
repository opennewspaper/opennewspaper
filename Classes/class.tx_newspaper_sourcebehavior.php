<?php
/**
 *  \file class.tx_newspaper_sourcebehavior.php
 *
 *  \author Lene Preuss <lene.preuss@gmx.net>
 *  \date Nov 20, 2008
 */

require_once(PATH_typo3conf . 'ext/newspaper/interfaces/interface.tx_newspaper_source.php');
require_once(PATH_typo3conf . 'ext/newspaper/interfaces/interface.tx_newspaper_articleiface.php');
require_once(PATH_typo3conf . 'ext/newspaper/interfaces/interface.tx_newspaper_extraiface.php');

/// Behavior class to factor out code common to more or less all Source implementations
/** \see tx_newspaper_Source::readField() */
class tx_newspaper_SourceBehavior {

	/** \param $parent The tx_newspaper_Source object using this Behavior
	 */ 
	public function __construct(tx_newspaper_Source $parent) {
		$this->parentSource = $parent;
	}

	/// Convert object to string to make it visible in stack backtraces, devlog etc.
	public function __toString() {
		return get_class($this) . '-object ' . "\n";
	}

	/// Reads the specified fields of the article with the specified UID
	/** \param $extra Extra object for which fields are read
	 *  \param $fieldList the fields which should be read from the source - if there's
	 *  	   more than one field, supply them as array
	 *  \param $uid a unique key to locate the article in the given source
	 */	
	public function readFields(tx_newspaper_ExtraIface $extra, array $fieldList, tx_newspaper_SourcePath $uid) {
		foreach ($fieldList as $field) { 
			$this->parentSource->readField($extra, $field, $uid);
		}
	}

	/// Creates and reads a full article with the specified UID
	/** \param $articleclass Either, the class name for the article (must 
	 * 		implement tx_newspaper_ArticleIface), or an already instantiated
	 * 		object of a class implementing tx_newspaper_ArticleIface.
	 *  \param $uid A unique key to locate the article in the given source
	 *  \return A newly created Article object
	 *  \throw WrongClassException If \p $articleclass is not the name of a 
	 * 							   class that implements Article 
	 */
	public function readArticle($articleclass, tx_newspaper_SourcePath $uid) {
		$article = null;
		
		/// $article is set to an object of an appropriate class
		if ($articleclass instanceof tx_newspaper_ArticleIface) {
			$article = $articleclass;
			$articleclass = get_class($article);	// to throw meaningful exception
		} else {
			if (class_exists($articleclass)) $article = new $articleclass;
			else throw new tx_newspaper_WrongClassException($articleclass);
		}
		
		/// If that didn't work, throw up
		if (!$article instanceof tx_newspaper_ArticleIface) {
			throw new tx_newspaper_WrongClassException($articleclass);
		}
		
		/// Finally read all required attributes for $article
		foreach ($article->getAttributeList() as $field) {
			$this->parentSource->readField($article, $field, $uid);
		}

		/// And tell the Article the truth: "I'm your father, Luke"
		$article->setSource(array($this->parentSource, $uid));
						 
		return $article;
	}

	/// Reads an array of articles with the specified UIDs
	/** \param $articleclass The class name for the article; the class must 
	 * 						 implement Article
	 *  \param $uids Unique keys to locate the articles in the given source
	 *  \return array of article objects
	 *  \throw WrongClassException If \p $articleclass is not the name of a 
	 * 							   class that implements Article 
	 */
	public function readArticles($articleclass, array $uids) {
		$articles = array();
		foreach ($uids as $uid) {
			$articles[] = $this->parentSource->readArticle($articleclass, $uid);
		}
		return $articles;
	}

	////////////////////////////////////////////////////////////////////////////
	//		end of public interface											  //
	////////////////////////////////////////////////////////////////////////////

	private $parentSource = null;
}
?>

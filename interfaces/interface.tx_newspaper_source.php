<?php

require_once(BASEPATH.'/typo3conf/ext/newspaper/interfaces/interface.tx_newspaper_article.php');

/// A path to locate a tx_newspaper_Article in a tx_newspaper_Source
/** It may consist either of an integer UID or a string. This class treats all
 *  variants equally.
 */
class tx_newspaper_SourcePath {

	public function __construct($path) { $this->path = $path; }
	
	public function __toString() { return strval($this->path); }

	public function getID() { return $this->path; }

	private $path = null;

}

 /// A source, from which articles are read
 /** This interface supplies functions which read an Article, or parts of it,
  *  or [parts of] many Articles, or one Extra, or many Extras.
  *
  *  \author Helge Preuss <helge.preuss@gmx.net>
  */
interface tx_newspaper_Source {

	/// Reads ONE field for the given Article or Extra 
	/** In theory, this is the only method of a Source that must be implemented
	 *  fully. All other methods can call readField(), directly or indirectly, 
	 *  to read their data. 
	 * 
	 *  This leads to the question, why don't we just define an abstract class
	 *  which implements all methods of the Source interface but readField(),
	 *  and derive the implementation from it? The answer (and it isn't clear
	 *  yet whether it's a good answer) is, that in PHP classes cannot inherit
	 *  from multiple classes, so if a Source implementation needs to subclass
	 *  another class, we're fscked.
	 * 
	 *  So I delegate all code that is common to all implementations to 
	 *  tx_newspaper_SourceBehavior, and let the implementing classes call the
	 *  method on the Behavior class. I haven't found a case yet, though, where
	 *  a Source needs to subclass any other class, so maybe this is 
	 *  unnecessarily complicated.
	 * 
	 *  Another answer is, though, that calling readField() multiple times can
	 *  be (much) less efficient than reading all fields at once. Compare, for 
	 *  instance, doing multiple SQL SELECT queries, each with one field of the
	 *  table, to a single SELECT which reads all relevant fields.
	 * 
	 *  \param $extra Extra object for which a field is read
	 *  \param $field The field which should be read from the source
	 *  \param $uid a unique key to locate the article in the given source
	 */
	public function readField(tx_newspaper_Extra $extra, $field, tx_newspaper_SourcePath $uid);

	/// Reads the specified fields of the Article or Extra with the specified UID
	/** \param $extra Extra object for which fields are read
	 *  \param $fieldList the fields which should be read from the source - if
	 *  	   there's more than one field, supply them as array
	 *  \param $uid a unique key to locate the article in the given source
	 */
	public function readFields(tx_newspaper_Extra $extra, array $fieldList, tx_newspaper_SourcePath $uid);

	/// Creates and reads a full article with the specified UID
	/** \param $articleclass The class name for the article; must implement 
	 * 		   				 Article
	 *  \param $uid A unique key to locate the article in the given source
	 *  \return A newly created Article object
	 *  \throw WrongClassException If \p $articleclass is not the name of a 
	 * 							   class that implements Article 
	 */
	public function readArticle($articleclass, tx_newspaper_SourcePath $uid);

	/// Reads an array of articles with the specified UIDs
	/** \param $articleclass The class name for the article; the class must 
	 * 						 implement Article
	 *  \param $uids Unique keys to locate the articles in the given source
	 *  \return array of article objects
	 *  \throw WrongClassException If \p $articleclass is not the name of a 
	 * 							   class that implements Article 
	 */
	public function readArticles($articleclass, array $uids);

	/// Reads array of only the specified fields of articles with the given UIDs 
	/** There is no function readPartialArticle() (singular Article), because
	 *  readFields() is up to the task.
	 * 
	 *  \param $articleclass The class name for the article; the class must 
	 * 						 implement Article
	 *  \param $fields List of attributes to get for every Article
	 *  \param $uids Unique keys to locate the articles in the given source
	 *  \return array of (incomplete) Article objects
	 *  \throw WrongClassException If \p $articleclass is not the name of a 
	 * 							   class that implements Article 
	 */
	public function readPartialArticles($articleclass,array $fields, array $uids);

    /// reads an Extra
	/** \param $extraclass If an object of a class implementing Extra: The
	 * 					   Extra object which is read 
	 * 					   If a string: The class name for the Extra; the 
	 * 					   class must implement Extra
	 *  \param $uid A unique key to locate the Extra in the given source
	 *  \return array of Extra objects
	 *  \throw WrongClassException If \p $extraclass is not the name of a 
	 * 							   class that implements Extra 
	 */
    public function readExtra($extraclass, tx_newspaper_SourcePath $uid);

    /// reads an array of Extra s
	/** \param $extraclass If an object of a class implementing Extra: The
	 * 					   Extra object which is read 
	 * 					   If a string: The class name for the Extra; the 
	 * 					   class must implement Extra
	 *  \param $uids Unique keys to locate the Extras in the given source
	 *  \return array of Extra objects
	 *  \throw WrongClassException If \p $extraclass is not the name of a 
	 * 							   class that implements Extra 
	 */
    public function readExtras($extraclass, array $uids);
    
    public function writeArticle(tx_newspaper_Article $article, tx_newspaper_SourcePath $uid);
    public function writeExtra(tx_newspaper_Extra $extra, tx_newspaper_SourcePath $uid);
    
    public function browse(tx_newspaper_SourcePath $path);
    
}

?>

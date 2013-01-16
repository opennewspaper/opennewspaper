<?php
/**
 *  \file interface.tx_newspaper_articleiface.php
 *
 *  \author Lene Preuss <lene.preuss@gmx.net>
 *  \date Oct 27, 2008
 */

require_once(PATH_typo3conf . 'ext/newspaper/interfaces/interface.tx_newspaper_extraiface.php');
require_once(PATH_typo3conf . 'ext/newspaper/interfaces/interface.tx_newspaper_withsource.php');

require_once(PATH_typo3conf . 'ext/newspaper/Classes/private/class.tx_newspaper_articlebehavior.php');

/// This is the interface that every Article class must implement
/** Additional functions that an Article needs to implement in addition to an
 *  tx_newspaper_ExtraIface.
 * 
 *  Historical notes:\n
 *  Basically I created this class because I want an intermediate stage for the
 *  deployment of tx_newspaper on taz.de. The Source interface returns objects
 *  of class Article, which I can implement in the class taz_article used
 *  in taz 1.0. If Source returned objects of class Article, that would be
 *  incompatible with taz 1.0, and thus messier to migrate.
 * 
 *  As it stands, a separate interface between tx_newspaper_ExtraIface and
 *  tx_newspaper_Article does not seem necessary any more.
 *
 *  \todo Check if this interface can be thrown out altogether.
 *  \todo Actually define the interface. The current functions are just
 *  	preliminary notes.
 */
interface tx_newspaper_ArticleIface
	extends tx_newspaper_ExtraIface, tx_newspaper_WithSource {

	/// \return The list of Extra s associated with this tx_newspaper_ArticleIface
	public function getExtras();

	/// Find the first tx_newspaper_Extra of a given type
	/** \param $extra_class The desired type of tx_newspaper_Extra, either as
	 *  	 object or as class name.
	 *  \return The first tx_newspaper_Extra of the given class (by appearance
	 * 		 in article), or \c null.
	 */
	public function getExtra($extra_class);	

	/// Add an Extra to the Article
	/** \param $extra tx_newspaper_Extra to be added
	 */
	public function addExtra(tx_newspaper_Extra $extra);

	/// Get article type of article
	/** \return tx_newspaper_ArticleType assigned to this Article, or \c null.
	 */
	public function getArticleType();
	
	/// \return List of attributes this tx_newspaper_ArticleIface has
	public static function getAttributeList();
	
	/// Store the relation of an Extra to a concrete Article (used from BE)
	/**
	 *  - Write the entry in the abstract Extra table, if the Extra has been 
	 *    freshly created
	 *  - Link the Extra to the given article in the MM-table
	 * 
	 *  \param $extra concrete extra
	 *  \return UID of entry in abstract extra table
	 */
	public function relateExtra2Article(tx_newspaper_ExtraIface $extra);
#	public static function relateExtra2Article(tx_newspaper_Extra $extra, tx_newspaper_Article $article);
}
?>

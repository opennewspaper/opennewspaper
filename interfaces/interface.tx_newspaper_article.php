<?php
/**
 *  \file interface.tx_newspaper_article.php
 *
 *  \author Helge Preuss <helge.preuss@gmx.net>
 *  \date Oct 27, 2008
 */

require_once(BASEPATH.'/typo3conf/ext/newspaper/interfaces/interface.tx_newspaper_extra.php');
require_once(BASEPATH.'/typo3conf/ext/newspaper/interfaces/interface.tx_newspaper_withsource.php');

require_once(BASEPATH.'/typo3conf/ext/newspaper/classes/class.tx_newspaper_articlebehavior.php');

/// This is the interface that every Article class must implement
/** Basically I created this class because I want an intermediate stage for the
 *  deployment of tt_newspaper on taz.de. The Source interface returns objects
 *  of class Article, which I can implement in the class taz_article used
 *  in taz 1.0. If Source returned objects of class Article, that would be
 *  incompatible with taz 1.0, and thus messier to migrate.
 *
 *  \todo Actually define the interface. The current functions are just
 *  preliminary notes.
 */
interface tx_newspaper_Article 
	extends tx_newspaper_Extra, tx_newspaper_WithSource {
	public function importieren(tx_newspaper_Source $quelle);
	public function exportieren(tx_newspaper_Source $quelle);
	public function laden();
	public function speichern();
	public function vergleichen();
	public function extraAnlegen();

	/// \return The list of Extra s associated with this Article
	function getExtras();
	function addExtra(tx_newspaper_Extra $newExtra);

	/// \return List of attributes this Extra has
	public static function getAttributeList();
	
	/// Store the relation of an Extra to a concrete Article (used from BE)
	/** - Write the entry in the abstract Extra table, if the Extra has been 
	 *    frshly created
	 *  - Link the Extra to the given article in the MM-table
	 *  \todo Do we take strings and ints as UIDs here, or can we already work
	 * 	with full objects?
	 */
	public static function relateExtra2Article($extra_table, $extra_uid, $article_uid);
#	public static function relateExtra2Article(tx_newspaper_Extra $extra, tx_newspaper_Article $article);
}
?>

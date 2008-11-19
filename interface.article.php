<?php
/**
 *  \file interface.article.php
 *
 *  \author Helge Preuss <helge.preuss@gmx.net>
 *  \date Oct 27, 2008
 */

require_once(BASEPATH.'/typo3conf/ext/newspaper/interface.tx_newspaper_extra.php');

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
interface tx_newspaper_Article extends tx_newspaper_Extra {
	public function importieren();
	public function exportieren();
	public function laden();
	public function speichern();
	public function vergleichen();
	public function extraAnlegen();

	/// \return The list of Extra s associated with this Article
	function getExtras();
	function addExtra();
	
	function setUid($uid);
	function getUid();
		
	/// \return List of attributes which make up an Article implementation
	static function getAttributeList();
}
?>

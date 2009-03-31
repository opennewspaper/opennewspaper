<?php
/**
 *  \file interface.tx_newspaper_extra.php
 *
 *  \author Helge Preuss <helge.preuss@gmx.net>
 *  \date Oct 27, 2008
 */

require_once(PATH_typo3conf . 'ext/newspaper/interfaces/interface.tx_newspaper_storedobject.php');

/// An Extra for Article s or Ressort s
/** An Extra is, so to speak, a plugin for a displayable object.
 *
 *  That means, an Article or Ressort is created only with the most basic
 *  attributes that are needed in \em any object of their type. Everything else
 *  is created as an Extra to that object.
 *
 *  For example, an Article is created only with Text and header. An Image would
 *  not be mandatory for articles, and thus Images are implemented as a class
 *  which implements the Extra interface.
 *
 *  \todo define the interface!
 */
interface tx_newspaper_ExtraIface extends tx_newspaper_StoredObject {
	/// Render the Extra using the given Smarty template
	/** \param $template Smarty template used to render the Extra
	 *  \return The rendered HTML
	 */
	public function render($template = '');

	/// \return Array with tx_newspaper_Extra data for given uid
	public static function readExtraItem($uid, $table);
	
}
?>
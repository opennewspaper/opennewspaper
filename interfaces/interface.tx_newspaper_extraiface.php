<?php
/**
 *  \file interface.tx_newspaper_extra.php
 *
 *  \author Helge Preuss <helge.preuss@gmail.com>
 *  \date Oct 27, 2008
 */

require_once(PATH_typo3conf . 'ext/newspaper/interfaces/interface.tx_newspaper_storedobject.php');
require_once(PATH_typo3conf . 'ext/newspaper/interfaces/interface.tx_newspaper_renderable.php');

/// An Extra for tx_newspaper_Article s or tx_newspaper_Section s
/** An Extra is, so to speak, a plugin for a displayable object.
 *
 *  That means, an Article or Section is created only with the most basic
 *  attributes that are needed in \em any object of their type. Everything else
 *  is created as an Extra to that object.
 *
 *  For example, an Article is created only with Text and header. An Image would
 *  not be mandatory for articles, and thus Images are implemented as a class
 *  which implements the Extra interface.
 *
 *  Because of the versatility of this concept (Articles and Page Zones are
 *  treated as Extras too, so they can be freely placed anywhere where Extras
 *  can be placed), and because these other classes inherit from, again, other
 *  classes, \em and because PHP does not support multiple inheritance, the
 *  Extra is split into the interface definition tx_newspaper_ExtraIface and the
 *  base class for all concrete Extras, tx_newspaper_Extra. Articles and Page
 *  Zones must implement tx_newspaper_ExtraIface themselves. 
 */
interface tx_newspaper_ExtraIface
		extends tx_newspaper_StoredObject, tx_newspaper_Renderable {

	/// A short description that makes an Extra uniquely identifiable in the BE
	public function getDescription();
	
	/// Deletes the concrete Extras and all references to it
	/** These references are: 
	 *  - all entries in the abstract Extra table which point to the concrete 
	 * 	  Extra ( \p $this ).
	 *  - all entries in the association tables between PageZones and Extras
	 *    resp. Articles and Extras which point to an abstract Extra pointing
	 * 	  to \p $this .
	 */
	public function deleteIncludingReferences();
	
	/// Lists Extras which are in the pool of master copies for new Extras
	/** Some Extra classes provide the functionality to store Extras in a 
	 *  so-called pool. Extras in the pool can be copied to create new Extra
	 *  instances, which then can either be changed or left alone.
	 * 
	 *  \return A list of Extras of the current class which are in this pool.
	 */
	public function getPooledExtras();
	
	/// \return Array with tx_newspaper_Extra data for given uid
	/** \todo Does this function still have to be in the interface?
	 */
	public static function readExtraItem($uid, $table);

	/// Per-class flag designating whether extra is article-dependent
	/** \return whether this Extra class displays content which depends on the
	 *  Article currently displayed
	 */
	public static function dependsOnArticle();
}
?>
<?php
/**
 *  \file interface.tx_newspaper_extra.php
 *
 *  \author Helge Preuss <helge.preuss@gmx.net>
 *  \date Oct 27, 2008
 */

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
 interface tx_newspaper_Extra {
 	/// Render the Extra using the given Smarty template
 	/** \param $template Smarty template used to render the Extra
 	 *  \return The rendered HTML
 	 */
	public function render($template);

	/// returns an actual member of the Extra
	/** \param $fieldname Name of the attribute which is wanted
	 *  \return The value of attribute \p $fieldname
	 */
	public function getAttribute($fieldname);
	/// sets a member
	/** \param $fieldname Name of the attribute which is to be set
	 *  \param $value New value for attribute \p $fieldname
	 */
	public function setAttribute($fieldname, $value);

	/// \return The Source object associated with this Article (if any) 
	function getSource();
	/// Set the Source object associated with this Article
	/** \param $source The new Source */
	function setSource(tx_newspaper_Source $source);

	/// \return The Unique ID which identifies the Extra in its Source
	function getUid();
	/// Set the Unique ID which identifies the Extra in its Source
	/** \param $uid The new Unique ID */
	function setUid($uid);

	/// Definition of attributes and their mapping to implementation fields
	/// for all supported Sources
	/** \note For every new Source that is defined, if the Extra must support
	 * 		  that Source, you must alter this function to contain the mapping
	 * 		  to the new Source type. Usually that will require to add fields to
	 * 		  a static array.
	 *  \param $fieldname Name of the attribute which must be mapped
	 *  \param $source The Source for which the mapping is wanted
	 *  \return The name of attribute \p $fieldname in Source \p $source
	 *  \throw WrongClassException If the mapping for class \p $source is not  
	 * 							   configured
	 */
	static function mapFieldToSourceField($fieldname, tx_newspaper_Source $source);
		
	/// \return List of attributes which make up an Extra implementation
	static function getAttributeList();
	
}
?>

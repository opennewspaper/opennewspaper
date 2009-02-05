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
	public function render($template = '');

	/// returns an actual member of the Extra
	/** \param $fieldname Name of the attribute which is wanted
	 *  \return The value of attribute \p $fieldname
	 */
	public function getAttribute($attribute);
	/// sets a member
	/** \param $fieldname Name of the attribute which is to be set
	 *  \param $value New value for attribute \p $fieldname
	 */
	public function setAttribute($attribute, $value);

	/// \return the name of the Extra = the name of the database table the Extra data is stored in
	public function getName();

	/// \return the title of the Extra (using the language set in BE)
	static function getTitle();

	/// \return String the name of the extra module -> needed to fill varchar(10) field "module" in table pages to find/create a storage folder for an Extra etc.
	static function getModuleName(); 

	/// \return Array with tx_newspaper_Extra data for given uid
	static function readExtraItem($uid, $table);
}
?>

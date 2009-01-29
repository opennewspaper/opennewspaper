<?php
/**
 *  \file interface.tx_newspaper_extra.php
 *
 *  \author Helge Preuss <helge.preuss@gmx.net>
 *  \date Oct 27, 2008
 */

/// An object which can be read from a tx_newspaper_Source
/** Not all Extras are associated with a source. In particular, 
 *  tx_newspaper_PageZone can not be read from a Source, because it exists only
 *  in the Typo3 system, not in an external editing system. There are probably
 *  other Extras for which this is true.
 * 
 *  Articles, on the other hand, should always have a representation in the
 *  newspaper's editing system. That is why interface tx_newspaper_Article must
 *  extend this interface. 
 */
 interface tx_newspaper_WithSource {

	/// \return The Source object associated with this Article (if any)
	function getSource();
	/// Set the Source object associated with this Article
	/** \param $source The new Source */
	function setSource(tx_newspaper_Source $source);

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

	/// Additional info needed to instantiate an Extra, such as a SQL table
	static function sourceTable(tx_newspaper_Source $source);
}
?>

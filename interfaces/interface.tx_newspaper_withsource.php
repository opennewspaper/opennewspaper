<?php
/**
 *  \file interface.tx_newspaper_withsource.php
 *
 *  \author Lene Preuss <lene.preuss@gmx.net>
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

	///  The tx_newspaper_Source object associated with this object.
	/** \return The tx_newspaper_Source object associated with this object (if any)
	 */
	public function getSource();
	/// Set the tx_newspaper_Source object associated with this object
	/** \param $source array (
	 * 			The new tx_newspaper_Source object,
	 * 			UID of current object in the source
	 * 		)
	 */
	public function setSource(array $source);

	/// Definition of attributes and their mapping to implementation fields for all supported Sources
	/** \note For every new tx_newspaper_Source that is defined, if the
	 * 		  tx_newspaper_WithSource must support that Source, you must alter
	 * 		  this function to contain the mapping to the new Source type.
	 * 		  Usually that will require to add fields to a static array.
	 *  \param $fieldname Name of the attribute which must be mapped
	 *  \param $source The tx_newspaper_Source for which the mapping is wanted
	 *  \return The name of attribute \p $fieldname in tx_newspaper_Source \p $source
	 *  \throw WrongClassException If the mapping for class \p $source is not
	 * 							   configured
	 */
	static public function mapFieldToSourceField($fieldname, tx_newspaper_Source $source);

	/// Additional info needed to instantiate a tx_newspaper_WithSource object, such as a SQL table
	static public function sourceTable(tx_newspaper_Source $source);
}
?>

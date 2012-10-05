<?php
/**
 *  \file interface.tx_newspaper_storedobject.php
 *
 *  \author Lene Preuss <lene.preuss@gmail.com>
 *  \date Feb 09, 2009
 */

/// A persistent object which is stored in the DB
/** Almost all business objects belonging to the newspaper extension are stored
 *  in Typo3 SysFolders. To find out which SysFolder an object belongs to, it
 *  must implement the method getModuleName().
 * 
 *  Of course, every stored object resides in a DB table and has a UID. Getters
 *  for DB table and UID and a setter for the UID must be provided.
 *
 *  Additional functionality is supplied for printing the type name of the
 *  object in a user-readable way via getTitle(). 
 */
 interface tx_newspaper_StoredObject {

	/// Returns an attribute
	/** \param $attribute Name of the requested attribute
	 *	\return Value of the requested attribute
	 *  \throw tx_newspaper_WrongAttributeException If \p $attribute does
	 * 		not exist.
	 */
	public function getAttribute($attribute);

	///	Sets an attribute
	/** No tx_newspaper_WrongAttributeException here. We want to be able to set
	 *  attributes, even if they don't exist beforehand.
	 *
	 *  \param $attribute Name of the requested attribute
	 *	\param $value New value of the requested attribute
	 */
	public function setAttribute($attribute, $value);

	/// Write or overwrite object data in DB
	/** Of course, a StoredObject needs a store() procedure ;-)
	 * 
	 *  If the object is new, creates the record and sets the UID to the result.
	 *  Else overwrites the existing record.
	 *
	 *  Associated records, such as relation tables and sub-records, are written
	 *  too.
	 *
	 *  \return UID of the written record
	 */
	public function store();

	/// UID of the record that contains the object in the DB
	/** \return UID of the record that contains the object in the DB */
	public function getUid();

	/// Set the UID of the record that contains the object in the DB
	/** \param $uid new UID of the record that contains the object in the DB */
	public function setUid($uid);
	
	/// Name of the database table the object's data are stored in
	/** \return Name of the database table the object's data are stored in */
	public function getTable();

	///	Short description of the object type
	/** This is (probably) used only in the BE, where the user needs to know
	 *	which kind of object she is handling.
	 *
	 *  \return (Internationalized) short description if the object type
	 */
	public function getTitle();

	/// The name of the Typo3 SysFolder used to store objects of this type
	/** Needed to fill field \c tx_newspaper_module in table \c pages to
	 *  find/create a storage folder for a storable object.
	 * 
	 *  \b Specification: \c np_* (max. 255 char); must not contain \c "phpunit"
	 * 
	 *  \return The name of the newspaper sysfolder
	 */
	public static function getModuleName(); 

}
?>

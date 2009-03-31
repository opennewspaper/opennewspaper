<?php
/**
 *  \file interface.tx_newspaper_extra.php
 *
 *  \author Helge Preuss <helge.preuss@gmail.com>
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
 *  Additional functionality is supplied for 
 */
 interface tx_newspaper_StoredObject {

	/** \param $attribute_name Name of the requested attribute
	 *	\return Value of the requested attribute
	 */
	public function getAttribute($attribute_name);
	/** \param $attribute_name Name of the requested attribute
	 *	\param $value New value of the requested attribute
	 */
	public function setAttribute($attribute_name, $value);

	/// Write or overwrite Extra data in DB
	/** Of course, a StoredObject needs a store() procedure ;-)
	/*  If the object is new, creates the record and sets the UID to the result.
	 *  Else overwrites the existing record.
	 *
	 *  Associated records, such as relation tables and sub-records, are written
	 *  too.
	 *
	 *  \return UID of the written record
	 */
	public function store();

	/// \return UID of the object
	public function getUid();

	/// \param $uid new UID of the object
	public function setUid($uid);
	
	/// \return Name of the database table the object's data are stored in
	public function getTable();

	/// \return (Internationalized) short description if the object type
	/** This is (probably) used only in the BE, where the user needs to know
	 *	which kind of object she is handling.
	 */
	public function getTitle();

	/// \return String the name of the newspaper sysfolder
	/** Needed to fill field "tx_newspaper_module" in table pages to find/create
	 *  a storage folder for an Extra etc.
	 *  Specification: np_* (max. 255 char); should not contain "phpunit"
	 */
	public static function getModuleName(); 

}
?>

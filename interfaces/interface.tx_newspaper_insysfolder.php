<?php
/**
 *  \file interface.tx_newspaper_extra.php
 *
 *  \author Helge Preuss <helge.preuss@gmail.com>
 *  \date Feb 09, 2009
 */

/// An object which is stored in a Typo3 SysFolder
/** Almost all business objects belonging to the newspaper extension are stored
 *  in Typo3 SysFolders. To find out which SysFolder an object belongs to, it
 *  must implement the method getModuleName().
 */
 interface tx_newspaper_InSysFolder {

	/// \return String the name of the extra module -> needed to fill varchar(10) field "module" in table pages to find/create a storage folder for an Extra etc.
	public static function getModuleName(); 


}
?>

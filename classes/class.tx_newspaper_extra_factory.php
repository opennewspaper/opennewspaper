<?php
/**
 *  \file class.tx_newspaper_extra_factory.php
 * 
 *  This file is part of the TYPO3 extension "newspaper".
 * 
 *  Copyright notice
 *
 *  (c) 2008 Helge Preuss, Oliver Schroeder, Samuel Talleux <helge.preuss@gmail.com, oliver@schroederbros.de, samuel@talleux.de>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 *  
 *  \author Helge Preuss <helge.preuss@gmail.com>
 *  \date Jan 8, 2009
 */

/// \todo All extra definitions must be known to this class, even those which are not part of tx_newspaper 
require_once(PATH_typo3conf . 'ext/newspaper/classes/extra/class.tx_newspaper_extra_ad.php');
require_once(PATH_typo3conf . 'ext/newspaper/classes/extra/class.tx_newspaper_extra_articlelist.php');
require_once(PATH_typo3conf . 'ext/newspaper/classes/extra/class.tx_newspaper_extra_bio.php');
require_once(PATH_typo3conf . 'ext/newspaper/classes/extra/class.tx_newspaper_extra_controltagzone.php');
require_once(PATH_typo3conf . 'ext/newspaper/classes/extra/class.tx_newspaper_extra_combolinkbox.php');
require_once(PATH_typo3conf . 'ext/newspaper/classes/extra/class.tx_newspaper_extra_container.php');
require_once(PATH_typo3conf . 'ext/newspaper/classes/extra/class.tx_newspaper_extra_displayarticles.php');
require_once(PATH_typo3conf . 'ext/newspaper/classes/extra/class.tx_newspaper_extra_externallinks.php');
require_once(PATH_typo3conf . 'ext/newspaper/classes/extra/class.tx_newspaper_extra_freeformimage.php');
require_once(PATH_typo3conf . 'ext/newspaper/classes/extra/class.tx_newspaper_extra_image.php');
require_once(PATH_typo3conf . 'ext/newspaper/classes/extra/class.tx_newspaper_extra_generic.php');
require_once(PATH_typo3conf . 'ext/newspaper/classes/extra/class.tx_newspaper_extra_html.php');
require_once(PATH_typo3conf . 'ext/newspaper/classes/extra/class.tx_newspaper_extra_mostcommented.php');
require_once(PATH_typo3conf . 'ext/newspaper/classes/extra/class.tx_newspaper_extra_searchresults.php');
require_once(PATH_typo3conf . 'ext/newspaper/classes/extra/class.tx_newspaper_extra_sectionlist.php');
require_once(PATH_typo3conf . 'ext/newspaper/classes/extra/class.tx_newspaper_extra_sectionteaser.php');
require_once(PATH_typo3conf . 'ext/newspaper/classes/extra/class.tx_newspaper_extra_textbox.php');
require_once(PATH_typo3conf . 'ext/newspaper/classes/extra/class.tx_newspaper_extra_typo3_ce.php');


class ErrorExtra extends tx_newspaper_Extra {
    public function __construct($message = '') {
        $this->message = $message;
    }
    public function getDescription() {
        return tx_newspaper_BE::renderIcon('gfx/icon_warning.gif', '') .
               $this->message;
    }
    public function getAttribute($x) {
        throw new tx_newspaper_IllegalUsageException('ErrorExtra::getAttribute()', true);
    }
    public static function dependsOnArticle() { return false; }

    private $message;
}

/// Factory class to create the correct kind of tx_newspaper_Extra from a UID
/** Problem: The tx_newspaper_Extra is stored in a table for the abstract
 *  parent type. At the time of creation, given only a UID, the concrete type
 *  of the Extra pointed to by that UID is not known yet.
 * 
 *  Solution: This factory class.
 * 
 *  This class is implemented as a Singleton.
 */
class tx_newspaper_Extra_Factory {
	
	/** @return tx_newspaper_Extra_Factory The only instance of the tx_newspaper_Extra_Factory Singleton
     */
	public static function getInstance() {
		if (self::$instance == null) {
			self::$instance = new tx_newspaper_Extra_Factory();
		}
		return self::$instance;
	}
	
	/// Create a concrete tx_newspaper_Extra object from an abstract UID
	/** Reads the concrete Extra pointed at by the abstract record \p $uid, 
	 *  instantiates and returns it.
	 *  
	 *  \param $uid The UID of the tx_newspaper_Extra in the abstract Extra table
	 *  @return tx_newspaper_Extra A fully instantiated concrete Extra of the correct type
	 *  \throw tx_newspaper_DBException If a DB operation goes wrong, e.g. when
	 * 		the abstract UID is not present or hidden by 
	 * 		tx_newspaper::enableFields(), or if the abstract record is corrupted
	 *  \throw tx_newspaper_WrongClassException If the class stored in the 
	 * 		abstract record does not exist
	 *  
	 *  \todo rename: instantiate()?
	 *  \todo honor deleted and hidden flags
	 */
	public function create($uid) {

        try {
            /// Read actual type and UID of the Extra to instantiate from DB
            $row =  tx_newspaper::selectOneRow(
                'extra_table, extra_uid', self::$extra_table, "uid = $uid"
            );
        } catch (tx_newspaper_Exception $e) {
            return new ErrorExtra("Extra with UID $uid was not found in DB");
        }

        if (!$row['extra_table']) {
            return new ErrorExtra('No extra_table in result');
        }

        if (!class_exists($row['extra_table'])) {
            return new ErrorExtra('Extra class ' . $row['extra_table'] . ' does not exist');
        }

        if (!$row['extra_uid']) {
            return new ErrorExtra('No extra_uid in result');
        }

        $extra = new $row['extra_table']($row['extra_uid']);
        $extra->setExtraUid($uid);
		return $extra;
	}
	
	///	Table in which tx_newspaper_Extra s are stored
	static function getExtraTable() { return self::$extra_table; } 

	///	MM association table linking tx_newspaper_Extra to tx_newspaper_Article
	static function getExtra2ArticleTable() { return self::$extra2article_table; } 

	///	MM association table linking tx_newspaper_Extra to tx_newspaper_PageZone_Page
	static function getExtra2PagezoneTable() { return self::$extra2pagezone_table; } 
	
	/// Protected constructor, tx_newspaper_Extra_Factory cannot be created freely
	protected function __construct() { }
	
	/// Cloning tx_newspaper_Extra_Factory is prohibited by making __clone private
	private function __clone() {}
	
	// attributes
	
	/// The only instance of the tx_newspaper_Extra_Factory Singleton
	private static $instance = null;
	
	/// Extra table must be defined here because tx_newspaper_Extra is an interface
	private static $extra_table = 'tx_newspaper_extra';
	///	MM association table linking tx_newspaper_Extra to tx_newspaper_Article
	private static $extra2article_table =  'tx_newspaper_article_extras_mm';
	///	MM association table linking tx_newspaper_Extra to tx_newspaper_PageZone_Page
	private static $extra2pagezone_table =  'tx_newspaper_pagezone_page_extras_mm';
 	
}

// The following must be included AFTER the definition of tx_newspaper_Extra_Factory
 
?>
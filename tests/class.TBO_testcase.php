<?php
/*
 * Created on Oct 27, 2008
 *
 * Author: helge
 */

require_once(BASEPATH.'/typo3conf/ext/newspaper/classes/class.tx_newspaper_taz_redsyssource.php');
require_once(BASEPATH.'/typo3conf/ext/newspaper/classes/class.tx_newspaper_articleimpl.php');

/// testsuite for class tx_newspaper_pi1 (also known as "The Big One" or "TBO")
class test_TBO_testcase extends tx_phpunit_testcase {

	function setUp() {
		$this->pi = new tx_newspaper_pi1();
	}

	public function test_createPlugin() {
		$temp = new tx_newspaper_pi1();
		$this->assertTrue(is_object($temp));
		$this->assertTrue($temp instanceof tx_newspaper_pi1);
	}	
	
	private $pi = null;					///< the plugin object
}
?>

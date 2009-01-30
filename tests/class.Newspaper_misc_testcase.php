<?php
/*
 * Created on Oct 27, 2008
 *
 * Author: helge
 */

require_once(BASEPATH.'/typo3conf/ext/newspaper/classes/class.tx_newspaper_extra_factory.php');

/// testsuite for all extras belonging to the newspaper extension
class test_Newspaper_misc_testcase extends tx_phpunit_testcase {

	function setUp() {
	}

	public function test_NoResException() {
		$this->setExpectedException('tx_newspaper_NoResException');
		throw new tx_newspaper_NoResException('');
	}

	public function test_EmptyResultException() {
		$this->setExpectedException('tx_newspaper_EmptyResultException');
		throw new tx_newspaper_EmptyResultException('');
	}

}
?>

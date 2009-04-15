<?php
/*
 * Created on Oct 27, 2008
 *
 * Author: helge
 */

require_once(PATH_typo3conf . 'ext/newspaper/classes/class.tx_newspaper_extra_factory.php');

/// testsuite for classes which don't warrant a full testsuite on their own
class test_Newspaper_misc_testcase extends tx_phpunit_testcase {

	function setUp() {
	}

	//	exceptions
	
	public function test_NoResException() {
		$this->setExpectedException('tx_newspaper_NoResException');
		throw new tx_newspaper_NoResException('');
	}

	public function test_EmptyResultException() {
		$this->setExpectedException('tx_newspaper_EmptyResultException');
		throw new tx_newspaper_EmptyResultException('');
	}

	public function test_SourceOpenFailedException() {
		$this->setExpectedException('tx_newspaper_SourceOpenFailedException');
		throw new tx_newspaper_SourceOpenFailedException('');
	}
	public function test_InconsistencyException() {
		$this->setExpectedException('tx_newspaper_InconsistencyException');
		throw new tx_newspaper_InconsistencyException('');
	}
	public function test_ArticleNotFoundException() {
		$this->setExpectedException('tx_newspaper_ArticleNotFoundException');
		throw new tx_newspaper_ArticleNotFoundException('');
	}
	public function test_SysfolderNoPidsFoundException() {
		$this->setExpectedException('tx_newspaper_SysfolderNoPidsFoundException');
		throw new tx_newspaper_SysfolderNoPidsFoundException('');
	}

	//	smarty
	
	public function test_getAvailableTemplateSets() {
		$basepath = PATH_site . '/fileadmin/templates/newspaper/';
		
		$template_sets_to_test = array('default', 'test_templateset');
		foreach ($template_sets_to_test as $ts) {
#			if (!file_exists($basepath . $ts)) mkdir ($basepath . $ts);
			t3lib_div::debug($basepath . 'template_sets/' . $ts);
		}
		
				
	}
}
?>

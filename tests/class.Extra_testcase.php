<?php
/*
 * Created on Oct 27, 2008
 *
 * Author: helge
 */

require_once(BASEPATH.'/typo3conf/ext/newspaper/classes/class.tx_newspaper_extra_factory.php');

/// testsuite for all extras belonging to the newspaper extension
class test_Extra_testcase extends tx_phpunit_testcase {

	function setUp() {
		$this->source = new tx_newspaper_DBSource();
		
	}

	public function test_getExtraTable() {
		$this->assertEquals(tx_newspaper_Extra_Factory::getInstance()->getExtraTable(), 'tx_newspaper_extra');	
	}

	public function test_nonexistentExtra() {
		$this->setExpectedException('tx_newspaper_DBException');
		tx_newspaper_Extra_Factory::getInstance()->create($this->bad_extra_uid);
	}
	
	public function test_createArticleRenderer() {
		$temp = new tx_newspaper_Extra_ArticleRenderer(1);
		$this->assertTrue(is_object($temp));
		$this->assertTrue($temp instanceof tx_newspaper_Extra);
		$this->assertTrue($temp instanceof tx_newspaper_Extra_ArticleRenderer);
		$this->assertEquals($temp->getTitle(), 'ArticleRenderer');
		$this->assertEquals($temp->getModuleName(), 'npe_rend');
	}

	public function test_createImage() {
		$temp = new tx_newspaper_Extra_Image(1);
		$this->assertTrue(is_object($temp));
		$this->assertTrue($temp instanceof tx_newspaper_Extra);
		$this->assertTrue($temp instanceof tx_newspaper_Extra_Image);
		$this->assertEquals($temp->getTitle(), 'Image');
		$this->assertEquals($temp->getModuleName(), 'npe_image');
	}
	
	public function test_getAttribute() {
		foreach($this->extras_to_test as $extra_class) {
			$temp = new $extra_class(1);
			$this->assertEquals($temp->getAttribute('uid'), 1);
		}

		$this->setExpectedException('tx_newspaper_WrongAttributeException');
		$temp->getAttribute('es gibt mich nicht, schmeiss ne exception!');
	}

	public function test_setAttribute() {
		$this->setExpectedException('tx_newspaper_NotYetImplementedException');
		foreach($this->extras_to_test as $extra_class) {
			$temp = new $extra_class(1);
			$temp->setAttribute('uid', 100);
			$this->assertEquals($temp->getAttribute('uid'), 100);
		}

		$this->setExpectedException('tx_newspaper_WrongAttributeException');
		$temp->setAttribute('es gibt mich nicht, schmeiss ne exception!', 1);
	}

	public function test_getSource() {
		$this->setExpectedException('tx_newspaper_NotYetImplementedException');
		foreach($this->extras_to_test as $extra_class) {
			$temp = new $extra_class(1);
			$this->assertNull($temp->getSource());
			$temp->setSource($this->source);
			$this->assertEquals($temp->getSource(), $this->source);
		}
	}

	public function test_mapFieldToSourceField() {
		$this->setExpectedException('tx_newspaper_NotYetImplementedException');
		foreach($this->extras_to_test as $extra_class) {
			$temp = new $extra_class(1);
			/// \todo find generic fieldnames to map
			$temp->mapFieldToSourceField($fieldname, $this->source);
		}
	}

	public function test_sourceTable() {
		$this->setExpectedException('tx_newspaper_NotYetImplementedException');
		foreach($this->extras_to_test as $extra_class) {
			$temp = new $extra_class(1);
			$temp->sourceTable($this->source);
		}
	}	

	public function test_getExtraPid() {
		foreach($this->extras_to_test as $extra_class) {
			$temp = new $extra_class(1);
			$temp->getExtraPid();
		}
	}	

	public function test_isRegisteredExtra() {
		foreach($this->extras_to_test as $extra_class) {
			$temp = new $extra_class(1);
			$this->assertTrue(tx_newspaper_ExtraImpl::isRegisteredExtra($temp));
		}
	}	

	public function test_registerExtra() {
		foreach($this->extras_to_test as $extra_class) {
			$temp = new $extra_class(1);
			tx_newspaper_ExtraImpl::registerExtra($temp);
			$this->assertTrue(tx_newspaper_ExtraImpl::isRegisteredExtra($temp));
		}
	}	

	public function test_render() {
		foreach($this->extras_to_test as $extra_class) {
			$temp = new $extra_class(1);
			$temp->render();
			/// \todo test the output... how can i do that generically?
		}
	}	
	
	public function test_createExtraRecord() {
		tx_newspaper_ExtraImpl::createExtraRecord(0, '');
	}
	
	private $source = null;
	private $bad_extra_uid = 2000000000;	///< extra that does not exist
	private $extras_to_test = array(
		'tx_newspaper_Extra_ArticleRenderer',
		'tx_newspaper_Extra_Image'
	);
}
?>

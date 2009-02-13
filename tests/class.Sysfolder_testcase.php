<?php
/*
 * Created on Feb 12, 2009
 *
 * Author: Oliver Schröder
 */


require_once(BASEPATH.'/typo3conf/ext/newspaper/interfaces/interface.tx_newspaper_insysfolder.php');
require_once(BASEPATH.'/typo3conf/ext/newspaper/classes/class.tx_newspaper_sysfolder.php');


/// testsuite for class tx_newspaper_Sysfolder
class test_Sysfolder_testcase extends tx_phpunit_testcase {


	function setUp() {
		// delete sysfolder for np_phpunit_testcase_4 (mustn't be there) and np_phpunit_testcase_5 (so we can create new without checking)
		$GLOBALS['TYPO3_DB']->exec_DELETEquery(
			'pages',
			'tx_newspaper_module="np_phpunit_testcase_4" OR tx_newspaper_module="np_phpunit_testcase_5"'
		);
	}


//	function tearDown() {
//		// delete sysfolder for np_phpunit_testcase_4 and np_phpunit_testcase_5 (so they don't bother when developing)
//		$GLOBALS['TYPO3_DB']->exec_DELETEquery(
//			'pages',
//			'tx_newspaper_module="np_phpunit_testcase_4" OR tx_newspaper_module="np_phpunit_testcase_5"'
//		);
//	}
	

	public function testNameTooShort() {
		$t = new tx_newspaper_Sysfolder_test('123');
		$sf = tx_newspaper_Sysfolder::getInstance();
		try {
			$sf->getPid($t);
			$this->fail('Module name is too short - but wasn\'t noticed');
		} catch (tx_newspaper_SysfolderIllegalModulenameException $e) {}
	}
	
	public function testNameTooLong() {
		$t = new tx_newspaper_Sysfolder_test(str_repeat('t', 256));
		$sf = tx_newspaper_Sysfolder::getInstance();
		try {
			$sf->getPid($t);
			$this->fail('Module name is too long - but wasn\'t noticed');
		} catch (tx_newspaper_SysfolderIllegalModulenameException $e) {}
	}
	
	public function testNoNpUnderscore() {
		$t = new tx_newspaper_Sysfolder_test('phpunit');
		$sf = tx_newspaper_Sysfolder::getInstance();
		try {
			$sf->getPid($t);
			$this->fail('Invalid module name wasn\'t noticed');
		} catch (tx_newspaper_SysfolderIllegalModulenameException $e) {}
	}
	
	public function testCreateSysfolder() {
		
		// delete sysfolder for np_phpunit_testcase_4 (test must create this folder)
		$GLOBALS['TYPO3_DB']->exec_DELETEquery(
			'pages',
			'tx_newspaper_module="np_phpunit_testcase_4"'
		);
		
		$t = new tx_newspaper_Sysfolder_test('np_phpunit_testcase_4');
		$sf = tx_newspaper_Sysfolder::getInstance();
		$pid = $sf->getPid($t); // get pid (sysfolder should have been created by this getPid call)
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'uid',
			'pages', 
			'tx_newspaper_module="np_phpunit_testcase_4" AND title="np_phpunit_testcase_4" AND module="newspaper" AND doktype=254'
		);
		if (!$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$this->fail('sysfolder for module "np_phpunit_testcase_4" wasn\'t created.');
		}
	}
	
	public function testUseSysfolder() {
		
		// create sysfolder for np_phpunit_testcase_5 (test should use this sysfolder without creating it)
		$data = array(
			'tx_newspaper_module' => 'np_phpunit_testcase_5', 
			'title' => 'np_phpunit_testcase_5', 
			'module' => 'newspaper', 
			'pid' => 0, 
			'doktype' => 254
		);
		$GLOBALS['TYPO3_DB']->exec_INSERTquery('pages', $data);
		$pid_np_phpunit_testcase_5 = $GLOBALS['TYPO3_DB']->sql_insert_id();

		$t = new tx_newspaper_Sysfolder_test('np_phpunit_testcase_5');
		$sf = tx_newspaper_Sysfolder::getInstance();
		$pid = $sf->getPid($t); // get pid of sysfolder (sysfolder exists)
		$this->assertEquals($pid_np_phpunit_testcase_5, $pid);
	}


	public function testRootSysfolder() {
		$t = new tx_newspaper_Sysfolder_test(tx_newspaper_Sysfolder::getRootSysfolderModuleName());
		$sf = tx_newspaper_Sysfolder::getInstance();
		$pid = $sf->getPid($t); // get pid of root sysfolder
		if ($pid <= 0)
			$this->fail('Couldn\'t access root sysfolder');
	}

}



class tx_newspaper_Sysfolder_test implements tx_newspaper_InSysFolder {
	
	private static $module_name;

	public function getUid() {return false;}
	public function setUid($uid) {return false;}
	public function getTable() {return false;}

	function __construct($module_name) {
		self::$module_name = $module_name;
	}
	
	public static function getModuleName() {
		return self::$module_name;
	}
	
	
}

?>

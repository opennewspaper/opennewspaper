<?php
/*
 * Created on Feb 12, 2009
 *
 * Author: Oliver Schr�der
 */


require_once(PATH_typo3conf . 'ext/newspaper/interfaces/interface.tx_newspaper_storedobject.php');
require_once(PATH_typo3conf . 'ext/newspaper/Classes/class.tx_newspaper_sysfolder.php');
require_once(PATH_typo3conf . 'ext/newspaper/tests/class.tx_newspaper_database_testcase.php');

/// testsuite for class tx_newspaper_Sysfolder
class test_Sysfolder_testcase extends tx_newspaper_database_testcase {


	function setUp() {
		// delete sysfolder for np_phpunit_testcase_4 (mustn't be there) and np_phpunit_testcase_5 (so we can create new without checking)
		$GLOBALS['TYPO3_DB']->exec_DELETEquery(
			'pages',
			'tx_newspaper_module="np_phpunit_testcase_4" OR tx_newspaper_module="np_phpunit_testcase_5"'
		);
	}


	function tearDown() {
		// delete sysfolder for np_phpunit_testcase_4 and np_phpunit_testcase_5 (so they don't bother when developing)
		$GLOBALS['TYPO3_DB']->exec_DELETEquery(
			'pages',
			'tx_newspaper_module="np_phpunit_testcase_4" OR tx_newspaper_module="np_phpunit_testcase_5"'
		);
	}
	
	
	/// this test must be run as first test because tx_newspaper_Sysfolder uses the Singleton pattern
	// if it's run later, the sysfolders are already read and the object won't notice the new sysfolder created in this test
	/** \to move to a separate test suite because this test does NOT run first
	 *  this testsuit must run before all others
	 */
/*	 
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
*/
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
		
		$t = new tx_newspaper_Sysfolder_test('np_phpunit_testcase_4');
		$sf = tx_newspaper_Sysfolder::getInstance();
		$pid = $sf->getPid($t); // get pid (sysfolder should have been created by this getPid call)
		
		$created = tx_newspaper::selectOneRow('tx_newspaper_module, title, module, doktype', 'pages', 'uid ='.$pid);
		
		$this->assertEquals('np_phpunit_testcase_4', $created['tx_newspaper_module'], 'Field tx_newspaper_module wrong');
		$this->assertEquals('newspaper', $created['module'], 'Field module wrong');
		$this->assertEquals('254', $created['doktype'], 'Field doktype wrong');
		$this->assertEquals('np_phpunit_testcase_4', $created['title'], 'Field title wrong');
	}
	



	public function testRootSysfolder() {
		$t = new tx_newspaper_Sysfolder_test(tx_newspaper_Sysfolder::getRootSysfolderModuleName());
		$sf = tx_newspaper_Sysfolder::getInstance();
		$pid = $sf->getPid($t); // get pid of root sysfolder
		if ($pid <= 0)
			$this->fail('Couldn\'t access root sysfolder');
	}

}



class tx_newspaper_Sysfolder_test implements tx_newspaper_StoredObject {
	
	private static $module_name;

	public function getUid() {return false;}
	public function setUid($uid) {return false;}
	public function getTable() {return false;}
	public function getAttribute($attribute) {return false;}
	public function setAttribute($attribute, $value) {return false;}
	public function store() {return false;}
	public function getTitle() {return false;}

	function __construct($module_name) {
		self::$module_name = $module_name;
	}
	
	public static function getModuleName() {
		return self::$module_name;
	}
	
	
}

?>

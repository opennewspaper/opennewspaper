<?php
/*
 * Baseclass for tests that need a database backend.
 * It creates a DB  named like the current one but postfixed with '_test'.
 * Data from tx_newspaper_hirachy is imported and deleted between all tests.
 * The datastructure used is imported from the newspapers ext_table.sql 
 * 
 * @Author ramon
 */
 
class tx_newspaper_database_testcase extends tx_phpunit_database_testcase {

    const fallback_test_db = 'onlinetaz_2_hel_test';

 	function setUp($createFixture = true) {
 			
 		if (self::$skip_setup_because_no_data_have_changed) return;

        $this->testDatabase = $this->getTestDB();

 		$this->createDatabase();
 		$this->cleanDatabase();
		$this->useTestDatabase();
 		$this->importTables(PATH_typo3conf . 'ext/newspaper/tests/typo3.newspaper.basis1.sql');
 		$this->importExtensions(array('newspaper', 'devlog'));
// 			$this->importData(PATH_typo3conf . 'ext/newspaper/tests/typo3.newspaper.basis1.inserts.sql');

        if($createFixture) {
            $this->fixture = new tx_newspaper_fixture();
        }
 	}

    private function getTestDB() {

        $db = $this->getFallbackTestDB();
        
        if (in_array($db, $GLOBALS['TYPO3_DB']->admin_get_dbs())) {
            return $db;
        }

        $GLOBALS['TYPO3_DB']->admin_query('CREATE DATABASE ' . $db);
        if (in_array($db, $GLOBALS['TYPO3_DB']->admin_get_dbs())) {
            return $db;
        }

        throw new tx_newspaper_IllegalUsageException(
            "Test DB $db not present and could not be created.
            Please create $db and make it writable or set TSConfig variable newspaper.test.test_db to a usable DB.
            Otherwise you will not be able to run the unit tests for newspaper.
            The test DB will be overwritten with every run of the test suite!"
        );
    }

    private function getFallbackTestDB() {
        $tsconfig = tx_newspaper::getTSConfig();
        if (isset($tsconfig['newspaper.']['test.']['test_db'])) {
            return $tsconfig['newspaper.']['test.']['test_db'];
        }

        return strtolower(TYPO3_db.'_test');

    }

    function tearDown() {
 		//clearing data in setUp so it can be inspected after running a single test.
 	}
 		
 	public function importTables($filename) {
		$this->importSQL($filename, ';');
	}
		
	/**
	 * INSERT-Statement has to be a single line
	 */
	private function importData($filename) {
		$this->importSQL($filename, PHP_EOL);
	}
		
	private function importSQL($filename, $seperator) {
		$content = file_get_contents($filename);
//		$content = $this->removeSqlComments($content);
		$statements = explode($seperator, $content);
		foreach($statements as $i => $stmt) {
			if(stristr($stmt, '--') == false && count($stmt) > 0) {
				$GLOBALS['TYPO3_DB']->admin_query($stmt);
			}
		}
	}
		
	private function removeSqlComments($sqlFileContent) {
		$statements = explode(PHP_EOL, $sqlFileContent);
		foreach($statements as $i => $stmt) {
			if(ereg('^(--)|(DROP)', $stmt)) {
				unset($statements[$i]);
			}
		}
		$content = implode($statements);
		return $content;
	}

    /**
	 * Drops tables in test database
	 *
	 * @return void
	 */
	protected function clearDatabase() {
		$db = $GLOBALS['TYPO3_DB'];
		$databaseNames = $db->admin_get_dbs();

		if (in_array($this->testDatabase, $databaseNames)) {
			$db->sql_select_db($this->testDatabase);

			// drop all tables
			$tables = $this->getDatabaseTables();
			foreach ($tables as $tableName) {
				$db->admin_query('TRUNCATE TABLE '. $tableName);
			}
		}
	}
		
	/**
	 * Because this class lives inside the folder Tests it is listed as a tests by tx_phpunit.
	 * Tests without testcases generate warnings which a dummy test avoids.
	 */
	public function test_dummyTestToAvoidWarningUntilTestsForThisClassAreWritten() {
		$this->assertTrue(true);
	}
		
    /** @var tx_newspaper_fixture */
 	protected  $fixture = null ;		//< Testdata
 		
 	/// If you want to run setUp() only once per testcase, set this variable in the testcase's setUp().
 	protected static $skip_setup_because_no_data_have_changed = false;
 		
 }
?>

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
 	
 		function setUp($createFixture = true) {
 			echo "tx_newspaper_database_testcase::setUp()";
 			$this->createDatabase();
 			$this->cleanDatabase();
			$this->useTestDatabase();             
 			$this->importTables(PATH_typo3conf . 'ext/newspaper/tests/typo3.newspaper.basis1.sql');
 			$this->importExtensions(array('newspaper', 'devlog'));             
// 			$this->importData(PATH_typo3conf . 'ext/newspaper/tests/typo3.newspaper.basis1.inserts.sql');

             if($createFixture) {
                $this->fixture = new tx_newspaper_hierarchy();
             }
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
//			$content = $this->removeSqlComments($content);
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
		
 		
 		protected  $fixture = null ;		//< Testdata
 		
 }
?>

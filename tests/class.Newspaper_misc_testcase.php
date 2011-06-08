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
		$basepath = PATH_site . 'fileadmin/templates/newspaper/';

		$template_sets_to_test = array('default', 'test_templateset');
		foreach ($template_sets_to_test as $ts) {
			if (!file_exists($basepath . 'template_sets/' . $ts)) mkdir ($basepath . 'template_sets/' . $ts, 0777, true);
		}

		$available_template_sets = tx_newspaper_Smarty::getAvailableTemplateSets();
		foreach ($template_sets_to_test as $ts) {
			if (!in_array($ts, $available_template_sets)) {
				$this->fail("$ts should be in available template_sets, but isn't: " . print_r($available_template_sets, 1));
			}
		}

		/// \todo If directories are empty, delete them
		foreach ($template_sets_to_test as $ts) {
			//	checking if a dir is empty is a PITA in PHP. leave it for now.
			if (is_dir($basepath . 'template_sets/' . $ts) &&
			 	false) rmdir ($basepath . 'template_sets/' . $ts);
		}

	}




	// database functions

	public function test_EnableFieldsTcaTable() {
		$this->assertEquals(tx_newspaper::enableFields('tx_newspaper_article'), ' AND tx_newspaper_article.deleted=0');
	}
	public function test_EnableFieldsTcaTableAlias() {
		$this->assertEquals(tx_newspaper::enableFields('tx_newspaper_article AS a'), ' AND tx_newspaper_article.deleted=0');
	}
	public function test_EnableFields2TcaTables() {
		$this->assertEquals(tx_newspaper::enableFields('tx_newspaper_article a, tx_newspaper_section'), ' AND tx_newspaper_article.deleted=0 AND tx_newspaper_section.deleted=0');
	}
	public function test_EnableFields2TcaTablesAlias() {
		$this->assertEquals(tx_newspaper::enableFields('tx_newspaper_article a, tx_newspaper_article_sections_mm mm'), ' AND tx_newspaper_article.deleted=0');
	}
	public function test_EnableFieldsNonExistingTable() {
		$this->assertEquals(tx_newspaper::enableFields('nonexistingtable'), '');
	}
	public function test_EnableFieldsJoin() {
		$this->assertEquals(
            tx_newspaper::enableFields(
			    'tx_newspaper_pagezone_page_extras_mm INNER JOIN tx_newspaper_extra ON tx_newspaper_pagezone_page_extras_mm.uid_foreign=tx_newspaper_extra.uid'
            ),
			' AND tx_newspaper_extra.deleted=0'
		);
	}

    public function test_ExplodeByList_Array() {
        $this->assertTrue(is_array(self::executeExplodeByList()));
    }

    public function test_ExplodeByList_Size() {
        $this->assertEquals(4, sizeof(self::executeExplodeByList()));
    }

    const explode_by_list_string = 'dot dot, comma, dash';
    public function test_ExplodeByList_Content() {
        $this->assertContains('dot', self::executeExplodeByList());
        $this->assertContains('comma', self::executeExplodeByList());
        $this->assertContains('dash', self::executeExplodeByList());
    }

    public function test_TableDescription_simple() {
        $description = new TableDescription('tx_newspaper_article');
        $this->compareName($description, 'tx_newspaper_article');
        $this->compareAlias($description, 'tx_newspaper_article');
    }

    public function test_TableDescription_alias_as() {
        $description = new TableDescription('tx_newspaper_article as a');
        $this->compareName($description, 'tx_newspaper_article');
        $this->compareAlias($description, 'a');

        $description = new TableDescription('tx_newspaper_article AS a');
        $this->compareName($description, 'tx_newspaper_article');
        $this->compareAlias($description, 'a');
    }

    public function test_TableDescription_alias() {
        $description = new TableDescription('tx_newspaper_article a');
        $this->compareName($description, 'tx_newspaper_article');
        $this->compareAlias($description, 'a');
    }

    public function test_splitOnJoin_NoJoin() {
        foreach (array('tx_newspaper_article') as $table) {
            $this->assertEquals(
                1,
                sizeof(TableDescription::createDescriptions($table)),
                $table . ' yields ' . sizeof(TableDescription::createDescriptions($table)) . ' descriptions'
            );
        }
    }
    ////////////////////////////////////////////////////////////////////////////

    private function compareName(TableDescription $description, $expected) {
        $this->assertEquals($description->getTableName(), $expected, 'Name is ' . $description->getTableName());
    }

    private function compareAlias(TableDescription $description, $expected) {
        $this->assertEquals($description->getTableAlias(), $expected, 'Alias is ' . $description->getTableAlias());
    }

    private static function executeExplodeByList() {
        $separators = array(',', ' ');
        return tx_newspaper::explodeByList($separators, self::explode_by_list_string);
    }

}
?>

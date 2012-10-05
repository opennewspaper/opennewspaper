<?php
/*
 * Created on Oct 27, 2008
 *
 * Author: lene
 */

require_once(PATH_typo3conf . 'ext/newspaper/Classes/class.tx_newspaper_section.php');
require_once(PATH_typo3conf . 'ext/newspaper/tests/class.tx_newspaper_database_testcase.php');
/// testsuite for class tx_newspaper_department
class test_Section_testcase extends tx_newspaper_database_testcase {

	function setUp() {
		parent::setUp();
//		$this->fixture = new tx_newspaper_hierarchy();
		$this->section = new tx_newspaper_Section($this->fixture->getParentSectionUid());
	}
	

	public function test_createSection() {
		$temp = new tx_newspaper_Section($this->section_uid);
		$this->assertTrue(is_object($temp));
		$this->assertTrue($temp instanceof tx_newspaper_Section);
	}
	
	public function test_getAttribute() {
		$this->assertEquals($this->section->getAttribute('uid'), $this->fixture->getParentSectionUid());
		$this->assertEquals($this->section->getAttribute('pid'), $this->fixture->getParentSectionPid());
		$this->assertEquals($this->section->getAttribute('section_name'), $this->fixture->getParentSectionName());
		$this->setExpectedException('tx_newspaper_Exception');
		$this->section->getAttribute('es gibt mich nicht, schmeiss ne exception!');
	}
	
	public function test_setAttribute() {
		$this->section->setAttribute('uid', -1);
		$this->assertEquals($this->section->getAttribute('uid'), -1);
		$this->section->setAttribute('pid', -1);
		$this->assertEquals($this->section->getAttribute('pid'), -1);
		$this->section->setAttribute('section_name', 'my unique section name');
		$this->assertEquals($this->section->getAttribute('section_name'), 'my unique section name');
		$this->section->setAttribute('es gibt mich nicht', 'aber jetzt gibt es mich');
		$this->assertEquals($this->section->getAttribute('es gibt mich nicht'), 'aber jetzt gibt es mich');
	}

	public function test_store() {
		$this->setExpectedException('tx_newspaper_NotYetImplementedException');
		$this->section->store();
	}
	
	public function test_Title() {
		global $LANG;
		$LANG->lang = 'default';
		$this->assertEquals($this->section->getTitle(), 'Section');
		/*
		//  setting the language does not work this way. disabled the test until i know how to do it.
		$LANG->lang = 'de';
		$this->assertEquals($this->section->getTitle(), 'Ressort');
		*/
	}
	
	public function test_getArticleList() {
		$list = $this->section->getArticleList();
		$this->assertEquals($list, 
							tx_newspaper_ArticleList_Factory::getInstance()->create($this->fixture->getAbstractArticlelistUid(), $this->section));
		
		$this->assertEquals($list->getTitle(), 'Semiautomatic article list');
		$this->assertEquals($list->getAbstractUid(), $this->fixture->getAbstractArticlelistUid());
		
		// section 1 has currently 1 article associated with it.
		$articles = $list->getArticles(1);
		$this->assertEquals(1, sizeof($articles), "Less than expected articles in article list");
		foreach ($articles as $article) {
			$this->assertTrue($article instanceof tx_newspaper_Article);
			t3lib_div::debug($article->getAttribute('title'));
		}
		
		$article = $list->getArticle(0);
		$this->assertTrue($article instanceof tx_newspaper_Article);
		$this->assertEquals($article->getAttribute('title'), $this->fixture->article_data['title']);
		
		$list->store();
		t3lib_div::debug('to do: test storing a list');
	}
	
	public function test_ArticleList() {
		$registered = tx_newspaper_ArticleList::getRegisteredArticleLists();
		$this->assertTrue($registered[0] instanceof tx_newspaper_ArticleList_Manual);
		
		$list = $this->section->getArticleList();
		$list->setAttribute('new attribute', 1);
		$this->assertEquals(1, $list->getAttribute('new attribute'), '\'new attribute\' not set');
		$this->assertEquals($this->fixture->getAbstractArticlelistUid(),  $list->getAttribute('uid'), "uids do not match");
		$this->setExpectedException('tx_newspaper_WrongAttributeException');
		$list->getAttribute('wrong attribute');
	}
	
	public function test_getParentSection() {
		$parent = $this->section->getParentSection();
		$this->assertEquals($parent, null);
		
		foreach ($this->section->getChildSections() as $child_section) {
			$this->assertEquals($child_section->getParentSection()->getUid(), $this->section->getUid());
		}
	}
	
	public function test_getSubPages() {
		$subpages = $this->section->getSubPages();
		foreach ($subpages as $page) {
			$this->assertTrue($page instanceof tx_newspaper_Page);
			$this->assertEquals($page->getAttribute('section'), $this->section->getUid());
		}
	}

    public function test_rootLine() {
        $children = $this->section->getChildSections();
        $section = $children[0];

        $rootline = $section->getRootLine();
        $section_path = $section->getSectionPath();

        $this->assertEquals(
            sizeof($section_path)-1, sizeof($rootline),
            "rootline " . sizeof($rootline) . " != section_path " . sizeof($section_path)
        );
        for ($i = 0; $i < sizeof($rootline); $i++) {
            $this->assertEquals(
                $rootline[$i]->getUid(), $section_path[$i+1]->getUid(),
                "rootline [$i]" . $rootline[$i]->getUid() . " != section_path[$i] " . $section_path[$i+1]->getUid()
            );
        }
    }

    public function test_getAllSections() {
        $sections = tx_newspaper_Section::getAllSections(false, 'sorting', false);
        $this->assertTrue(in_array($this->section, $sections));
        foreach ($this->section->getChildSections() as $child_section) {
            $this->assertTrue(in_array($child_section, $sections));
   		}
    }

    /** @var tx_newspaper_Section */
	private $section = null;					///< the object
	
}
?>

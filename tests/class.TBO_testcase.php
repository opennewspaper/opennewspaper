<?php
/*
 * Created on Oct 27, 2008
 *
 * Author: helge
 */

require_once(PATH_typo3conf . 'ext/newspaper/pi1/class.tx_newspaper_pi1.php');
require_once(PATH_typo3conf . 'ext/newspaper/tests/class.tx_newspaper_database_testcase.php');
/// testsuite for class tx_newspaper_pi1 (also known as "The Big One" or "TBO")
class test_TBO_testcase extends tx_newspaper_database_testcase {

	function setUp() {
		$this->old_page = $GLOBALS['TSFE']->page;
		$GLOBALS['TSFE']->page['uid'] = $this->plugin_page;
		parent::setUp();		
		$this->section_uid = $this->fixture->getParentSectionUid();
		$this->article_uid = $this->fixture->getArticleUid();
		$GLOBALS['TSFE']->page['tx_newspaper_associated_section'] = $this->section_uid;
		$this->pi = new tx_newspaper_pi1();
		
	}
	
	function tearDown() {
		$GLOBALS['TSFE']->page = $this->old_page;
		/// Make sure $_GET is clean
		unset($_GET['art']);
		unset($_GET['page']);
		parent::tearDown();
	}

	public function test_createPlugin() {
		$temp = new tx_newspaper_pi1();
		$this->assertTrue(is_object($temp));
		$this->assertTrue($temp instanceof tx_newspaper_pi1);
	}
	
	/// \todo finish
	public function test_getSection() {
		$this->assertTrue($this->pi->getSection() instanceof tx_newspaper_Section);
	}

	/// \todo finish
	public function test_getPage() {
		$this->assertTrue($this->pi->getPage(new tx_newspaper_Section($this->section_uid)) instanceof tx_newspaper_page);
	}
	
	/// Test whether the PI returns the correct pages.
	/** Content of the page zones and extras is tested below */
//	public function test_main() {
//		$this->fail('test not yet ready!');
//		$this->doTestContains($this->pi->main('', null), 'Testressort');
//		
//		$this->doTestContains($this->pi->main('', null), 'Ressortseite');
//
//		$_GET['page'] = 100;
//		$this->doTestContains($this->pi->main('', null), 'RSS');
//		unset($_GET['page']);
//
//		$_GET['art'] = $this->article_uid;
//		$this->doTestContains($this->pi->main('', null), 'Artikelseite');
//	}
	
//	public function test_PageZone_ressort() {
//		$this->fail('test not yet ready!');
//		$output = $this->pi->main('', null);
//		$this->doTestContains($output, 'Test-Seitenbereich auf Ressortseite - 1');
//		$this->doTestContains($output, 'Class: tx_newspaper_PageZone_Page');
//		$this->doTestContains($output, 'uid: 1');
//		$this->doTestContains($output, 'name: Test-Seitenbereich auf Ressortseite - 1');
//		$this->doTestContains($output, 'pagezone_id: X');
//
//		$this->doTestContains($output, 'Test-Seitenbereich auf Ressortseite - 2');
//		$this->doTestContains($output, 'Class: tx_newspaper_PageZone_Page');
//		$this->doTestContains($output, 'uid: 2');
//		$this->doTestContains($output, 'name: Test-Seitenbereich auf Ressortseite - 2');
//		$this->doTestContains($output, 'pagezone_id: Y');
//	}
	
//	public function test_PageZone_rss() {
//		$this->fail('test not yet ready!');
//		$_GET['page'] = 100;
//		// The following tests page header, not zone, and is therefore disabled
//		// $this->doTestContains($this->pi->main('', null), 'Class: tx_newspaper_PageZone_Page');
//		$output = $this->pi->main('', null);
//		$this->doTestContains($output, 'Test-Seitenbereich RSS');
//		$this->doTestContains($output, 'Class: tx_newspaper_PageZone_Page');
//		unset($_GET['page']);
//	}
	
//	public function test_PageZone_article() {
//		$_GET['art'] = $this->fixture->getArticleUid();
//		$output = $this->pi->main('', null);
//
//		$this->doTestContains($output, 'Test-Seitenbereich auf Artikelseite - 1');
//		$this->doTestContains($output, 'Class: tx_newspaper_PageZone_Page');
//		$this->doTestContains($output, 'uid: 3');
//		$this->doTestContains($output, 'name: Test-Seitenbereich auf Artikelseite - 1');
//		$this->doTestContains($output, 'pagezone_id: Z');
///*
//		$this->doTestContains($output, 'Artikel als Seitenbereich');
//		$this->doTestContains($output, 'Class: tx_newspaper_PageZone_Article');
//		$this->doTestContains($output, 'uid: 1');
//		$this->doTestContains($output, 'name: Artikel als Seitenbereich');
//		$this->doTestContains($output, 'pagezone_id: A');
//*/		
//		unset($_GET['art']);
//	}
	
//	public function test_Extras_ressort() {
//		$this->fail('test not yet ready!');
//		$output = $this->pi->main('', null);
//		$this->doTestContains($output, 'Image 1');
//		$this->doTestContains($output, 'img src="uploads\/tx_newspaper\/BSD_-_Daemon_tux_thumb');
//		$this->doTestContains($output, 'Caption for image 1');
//
//		$this->doTestContains($output, 'Image 2 Titel');
//		$this->doTestContains($output, 'img src="uploads\/tx_newspaper\/kari.080524.gif"');
//		$this->doTestContains($output, 'Image 2 Caption');
//	}
	
//	public function test_Extras_article() {
//		$_GET['art'] = $this->article_uid;
//		$output = $this->pi->main('', null);
//		
//		$this->doTestContains($output, 'Image 4');
//		$this->doTestContains($output, 'img src="uploads\/tx_newspaper\/120px-GentooFreeBSD-logo.svg_02.png"');
//		$this->doTestContains($output, 'Daemonic Gentoo');
//		
//		$this->doTestContains($output, 'title\[5\]');
//		$this->doTestContains($output, 'img src="uploads\/tx_newspaper\/lolcatsdotcomoh5o6d9hdjcawys6.jpg"');
//		$this->doTestContains($output, 'caption\[5\]');
//		
//	}

//	public function test_Article() {
//		$_GET['art'] = $this->article_uid;
//		$output = $this->pi->main('', null);
//		$this->doTestContains($output, 'Neuer Artikel');
//		$this->doTestContains($output, 'Nummer eins');
//		$this->doTestContains($output, 'Artikel ist im Lande');
//		$this->doTestContains($output, 'Test Text');
//		$this->doTestContains($output, 'Nicht ein einziges sinnvolles Wort');
//	}
	
	////////////////////////////////////////////////////////////////////////////
		
	protected  function doTestContains($string, $word) {
		$this->assertRegExp("/.*$word.*/", $string, 
							"Plugin output (expected $word): " .
							preg_replace('/"data:image\/png;base64,.*?"/', '"data:image/png;base64,..."', $string));
	}
	
	private $plugin_page = 2472;		///< a Typo3 page containing the Plugin
	private $section_uid = 1;
	private $article_uid = 1;			///< The article we use as test object
	private $pi = null;					///< the plugin object
	private $old_page; 					///< temp storage for $GLOBALS['TSFE']->page
	private $page_table = 'tx_newspaper_page';
	private $page_uid = null;			///< id of created page
}
?>

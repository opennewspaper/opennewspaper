<?php
/*
 * Created on Oct 27, 2008
 *
 * Author: helge
 */

require_once(BASEPATH.'/typo3conf/ext/newspaper/classes/class.tx_newspaper_taz_redsyssource.php');
require_once(BASEPATH.'/typo3conf/ext/newspaper/classes/class.tx_newspaper_articleimpl.php');

/// testsuite for class taz_RedsysSource
class test_RedsysSource_testcase extends tx_phpunit_testcase {

	function setUp() {
		$this->source = new tx_newspaper_taz_RedsysSource($this->red_cfg);
		$this->article = new tx_newspaper_ArticleImpl;
		$this->field = 'text';
		$this->fieldList = array('title', 'text');
		// "Wie geht es uns..." from Oct 27 '08
		$this->uid = new tx_newspaper_SourcePath('2008/10/27/a0105');
		// Three rather randomly selected articles
		$this->uidList = array(
			new tx_newspaper_SourcePath('2008/10/27/a0105'), 
			new tx_newspaper_SourcePath('2008/10/27/a0125')
		);
		// manually defined as the required fields for an article object
		$this->reqFields = array('title', 'teaser', 'text', 'ressort');
	}

	public function test_createSource() {
		$temp = new tx_newspaper_taz_RedsysSource($this->red_cfg);
		$this->assertTrue(is_object($this->source));
		$this->assertTrue(is_a($this->source, 'tx_newspaper_Source'));
		$this->setExpectedException('tx_newspaper_SourceOpenFailedException');
		$temp = new tx_newspaper_taz_RedsysSource('es gibt mich nicht, schmeiss ne exception!');
	}

	public function test_readField() {
		$this->source->readField($this->article, $this->field, $this->uid);
		$this->assertRegExp('/.*Ackermann.*/', $this->article->getAttribute('text'),
						  'readField(Text) returned text: '.$this->article->getAttribute('text'));
	}

	public function test_readFields() {
		$this->source->readFields($this->article, $this->fieldList, $this->uid);
		$this->assertRegExp('/.*Wie geht es uns.*/', $this->article->getAttribute('title'),
						  'readFields(Titel, Text) returned title: '.$this->article->getAttribute('title'));
		$this->assertRegExp('/.*Ackermann.*/', $this->article->getAttribute('text'),
						  'readFields(Titel, Text) returned text: '.$this->article->getAttribute('text'));
	}

	public function test_Attributes() {
		$attrs = tx_newspaper_ArticleImpl::getAttributeList();
		foreach ($this->reqFields as $field) {
			if (!in_array($field, $attrs)) 
				$this->fail("Required attribute $field not in Article::getRequiredAttributes()");
		}	
	}

	public function test_readArticle() {
		$this->article = $this->source->readArticle('tx_newspaper_ArticleImpl', $this->uid);
		$this->doTestIfArticleValid($this->article, 'source->readArticle()');
		
		$this->setExpectedException('tx_newspaper_WrongClassException');
		$this->source->readArticle('es gibt mich nicht, schmeiss ne exception!', $this->uid);
	}

	public function test_readArticleWithObject() {
		$this->article = $this->source->readArticle($this->article, $this->uid);
		$this->doTestIfArticleValid($this->article, 'source->readArticle()');
	}

	public function test_readArticles() {
		$articles = $this->source->readArticles('tx_newspaper_ArticleImpl', $this->uidList);
		$attrs = tx_newspaper_ArticleImpl::getAttributeList();
		$failed = array();
		foreach ($articles as $art) {
			foreach ($attrs as $req) {
				if (!$art->getAttribute($req)) $failed[] = array($art->getUid(), $req);
			}		
		}
		if ($failed) {
			$err = '';
			foreach ($failed as $fail) 
				$err .= 'attribute '.$fail[1].' in Article '.$fail[0].', ';
			$this->fail("Required attribute(s): $err".
						" not in article read via source->readArticles()");
		}			
	}

	public function test_readExtra() {
		$this->setExpectedException('tx_newspaper_NotYetImplementedException');
		$this->source->readExtra("", new tx_newspaper_SourcePath(""));
	}

	public function test_readExtras() {
		$this->setExpectedException('tx_newspaper_NotYetImplementedException');
		$this->source->readExtras("", array());
	}
	
	public function test_readPartialArticles() {
		$this->setExpectedException('tx_newspaper_NotYetImplementedException');
		$articles = $this->source->readPartialArticles('tx_newspaper_ArticleImpl', 
													   $this->fieldList, 
													   $this->uidList);
	}

	public function test_writeArticle() {
		$this->setExpectedException('tx_newspaper_NotYetImplementedException');
		$this->source->writeArticle($this->article, $this->uid);
		/// \todo actually write an article and compare the written article to the original
	}
	
	public function test_writeExtra() {
		$this->setExpectedException('tx_newspaper_NotYetImplementedException');
		$extra_uid = new tx_newspaper_SourcePath(1);
		$extra = tx_newspaper_Extra_Factory::getInstance()->create($extra_uid);
		$this->source->writeExtra($extra, $extra_uid);
		/// \todo actually write an extra and compare the written extra to the original
	}
	
	public function test_browse() {
		$year = date('Y');
		
		$months = $this->source->browse(new tx_newspaper_SourcePath($year));
		$this->assertTrue(is_array($months), 
						  'browse() dind\'t even bother to return an array');
		$this->assertTrue(sizeof($months) > 0, 
						  "you should find at least one month in $year. " );

		foreach ($months as $month) {
			$this->assertTrue($month instanceof tx_newspaper_SourcePath,
							  'good try! but '.$month.' is not a SourcePath!');
			$tmp = explode('/', $month->getID());
			$month_num = $tmp[1];
			$this->assertTrue($month_num > 0 && $month_num <= 12,
							  $month->getID() . ': ' . $month_num . ' is a weird number for a month...');
			
			// browse $month
			$days = $this->source->browse($month);
			$this->assertTrue(is_array($days), 
						  	  'browse() dind\'t even bother to return an array');
			$this->assertTrue(sizeof($days) > 0, 
							  "you should find at least one day in $month. " );

			foreach ($days as $day) {
				$this->assertTrue($day instanceof tx_newspaper_SourcePath,
								  'good try! but '.$day.' is not a SourcePath!');
				$tmp = explode('/', $day->getID());
				$day_num = $tmp[2];
				$this->assertTrue($day_num > 0 && $day_num <= 31,
								  $day->getID() . ': ' . $day_num . ' is a weird number for a day...');
				$articles = $this->source->browse($day);
				
				$this->assertTrue(is_array($articles), 
							  	  'browse() dind\'t even bother to return an array');
				$this->assertTrue(sizeof($articles) > 0, 
								  "you should find at least one article in $day. " );

				$count = 0;
				foreach ($articles as $article_path) {
					$count++;
					if ($count%10 == 0) {
						$article = $this->source->readArticle('tx_newspaper_ArticleImpl', $article_path);
						$this->doTestIfArticleValid($article, "source->browse() with path $article_path", array('teaser', 'ressort'));
					}
				}
			}			
		}		
	}
	
	public function test_browseCurrentProduction() {
		$this->source = new tx_newspaper_taz_RedsysSource($this->akt_cfg);
		$articles_tested = 0;
		$dates = $this->source->browse(new tx_newspaper_SourcePath('/'));
		foreach($dates as $date){
			$seitenbereiche = $this->source->browse(new tx_newspaper_SourcePath($date));
			$this->assertTrue(is_array($seitenbereiche), 
							  'browse() dind\'t even bother to return an array');
			$this->assertTrue(sizeof($seitenbereiche) > 0, 
							  "you should find at least one seitenbereich in $date. " );
			foreach ($seitenbereiche as $seitenbereich) {
				$this->assertTrue($seitenbereich instanceof tx_newspaper_SourcePath,
								  'good try! but ' . $seitenbereich . ' is not a SourcePath!');
				$articles = $this->source->browse($seitenbereich);
				if (is_array($articles) && sizeof($articles) > 0) foreach ($articles as $article_path) {
					$article = $this->source->readArticle('tx_newspaper_ArticleImpl', $article_path);
					$this->doTestIfArticleValid($article, "source->browse() with path $article_path", array('teaser', 'ressort'));
				}
				$articles_tested += sizeof($articles);
			}
		}		
		t3lib_div::debug($articles_tested.' articles tested ');						  
	}
	
	private function doTestIfArticleValid($article, $message, $unneeded_fields = array()) {
		$attrs = array_diff(tx_newspaper_ArticleImpl::getAttributeList(), $unneeded_fields);
		$failed = array();
		foreach ($attrs as $req) {
			if (!$article->getAttribute($req)) $failed[] = $req;
		}		
		if ($failed) {
			$this->fail("Required attribute(s) ".implode(', ', $failed).
						" not in article read via $message");
		}
		
	}
	
	private $source = null;				///< the local RedsysSource
	private $field = null;				///< single article field to read
	private $fieldList = array();		///< list of article fields to read
	private $uid = null;				///< unique key of article to read
	private $uidList = array();			///< unique keys of articles to read
	
	private $red_cfg = '/redonline/digitaz/etc/redonline.cfg';
	private $akt_cfg = '/redonline/akt/etc/redonline.cfg';
	private $article;
	private $reqFields = array();
}
?>

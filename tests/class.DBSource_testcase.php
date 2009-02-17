<?php
/*
 * Created on Oct 27, 2008
 *
 * Author: helge
 */

require_once(BASEPATH.'/typo3conf/ext/newspaper/classes/class.tx_newspaper_dbsource.php');
require_once(BASEPATH.'/typo3conf/ext/newspaper/classes/class.tx_newspaper_articleimpl.php');

/// testsuite for class taz_RedsysSource
class test_DBSource_testcase extends tx_phpunit_testcase {

	function setUp() {
		$this->source = new tx_newspaper_DBSource();
		$this->article = new tx_newspaper_ArticleImpl();
		$this->field = 'text';
		$this->fieldList = array('title', 'text');
		// "Wie geht es uns..." from Oct 27 '08
		$this->uid = new tx_newspaper_SourcePath('2008');
		// Three rather randomly selected articles
		$this->uidList = array(
			new tx_newspaper_SourcePath('2008'), 
			new tx_newspaper_SourcePath('2008/10/27/a0125'), 
			new tx_newspaper_SourcePath('2008/07/09/a0003'), 
			new tx_newspaper_SourcePath('2008/10/27/a0118'),
		);
		// manually defined as the required fields for an article object
		$this->reqFields = array('title', 'teaser', 'text', 'ressort');
	}

	public function test_createSource() {
		$temp = new tx_newspaper_DBSource();
		$this->assertTrue(is_object($this->source));
		$this->assertTrue(is_a($this->source, 'tx_newspaper_Source'));
	}

	public function test_readField() {
		$this->source->readField($this->article, $this->field, $this->uid);
		$this->assertRegExp('/.*einzigen Bushaltestelle im Umkreis von zwei Kilometern.*/', 
						  $this->article->getAttribute('text'),
						  'readField(Text) returned text: '.$this->article->getAttribute('text'));
	}

	public function test_readFields() {
		$this->source->readFields($this->article, $this->fieldList, $this->uid);
		$this->assertRegExp('/.*Diktatur des Proletariats.*/', $this->article->getAttribute('title'),
						  'readFields(Titel, Text) returned title: '.$this->article->getAttribute('title'));
		$this->assertRegExp('/.*einzigen Bushaltestelle im Umkreis von zwei Kilometern.*/', 
						  $this->article->getAttribute('text'),
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
		$attrs = tx_newspaper_ArticleImpl::getAttributeList();
		$failed = array();
		foreach ($attrs as $req) {
			if (!$this->article->getAttribute($req)) $failed[] = $req;
		}		
		if ($failed) {
			$this->fail("Required attribute(s) ".implode(', ', $failed).
						" not in article read via source->readArticle()");
		}
		
		$this->setExpectedException('tx_newspaper_WrongClassException');
		$this->source->readArticle('es gibt mich nicht, schmeiss ne exception!', $this->uid);
	}

	public function test_readArticleWithObject() {
		$this->article = $this->source->readArticle($this->article, $this->uid);
		$attrs = $this->article->getAttributeList();
		$failed = array();
		foreach ($attrs as $req) {
			if (!$this->article->getAttribute($req)) $failed[] = $req;
		}		
		if ($failed) {
			$this->fail("Required attribute(s) ".implode(', ', $failed).
						" not in article read via source->readArticle()");
		}
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

	public function test_readPartialArticles() {
		$this->setExpectedException('tx_newspaper_NotYetImplementedException');
		$articles = $this->source->readPartialArticles('tx_newspaper_ArticleImpl', 
													   $this->fieldList, 
													   $this->uidList);
	}
	
	public function test_readExtra() {
		$this->setExpectedException('tx_newspaper_NotYetImplementedException');
		$this->source->readExtra("", new tx_newspaper_SourcePath(""));
	}

	public function test_readExtras() {
		$this->setExpectedException('tx_newspaper_NotYetImplementedException');
		$this->source->readExtras("", array());
	}
	
	public function test_mapSourceFieldToField() {
		$attrs = $this->article->getAttributeList();
		
		$query = $GLOBALS['TYPO3_DB']->SELECTquery(
			'*',
			$this->article->sourceTable($this->source),
			"uid = ".intval($this->uid)
		);
		$res =  $GLOBALS['TYPO3_DB']->sql_query($query);
        if (!$res) $this->fail("Couldn't retrieve article #$uid");

        $row =  $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
        if (!$row) $this->fail("Article #$uid has no article_id field");

		$failed = array();
        foreach ($row as $field => $value) {
         	try {
         		if ($field != $this->article->mapFieldToSourceField(
         			$this->source->mapSourceFieldToField($this->article, $field),
         			$this->source)) { 
	        		$failed[] = $field." ".$this->source->mapSourceFieldToField($this->article, $field);
				}
	        } catch (tx_newspaper_IllegalUsageException $e) { }
        }
		
		if ($failed) {
			$this->fail("Mapping source field to field failed for".implode(', ', $failed));
		}
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

	private $source = null;				///< the local RedsysSource
	private $field = null;				///< single article field to read
	private $fieldList = array();		///< list of article fields to read
	private $uid = null;				///< unique key of article to read
	private $uidList = array();			///< unique keys of articles to read
	
	private $red_cfg = '/redonline/digitaz/etc/redonline.cfg';
	private $article;
	private $reqFields = array();
}
?>

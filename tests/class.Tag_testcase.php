<?php
/**
 * Created by IntelliJ IDEA.
 * User: Ramon
 * Date: 06.03.2010
 * Time: 14:11:39
 * To change this template use File | Settings | File Templates.
 */
require_once(PATH_typo3conf.'ext/newspaper/classes/class.tx_newspaper_exception.php');

class test_Tag_testcase extends tx_newspaper_database_testcase {

    public function setUp() {
        parent::setUp(false);
    }

    /**
     * Checks that a tag is read correctly from database
     * @return void
     */
    public function test_readTag() {
        $tag = tx_newspaper_Tag::createControlTag(1,'test-tag');
		self::storeExpectingExceptionInGetDossierPage($tag);
		$actualTag = new tx_newspaper_Tag($tag->getUid());
        $this->assertEquals($tag->getUid(), $actualTag->getUid());
        $this->assertEquals($tag->getAttribute('tag'), $actualTag->getAttribute('tag'), 'Tags does not match');
        $this->assertEquals($tag->getAttribute('tag_type'), $actualTag->getAttribute('tag_type'), 'Tagtype does not match');
        $expectedPid = tx_newspaper_Sysfolder::getInstance()->getPid($actualTag);
        $this->assertEquals($expectedPid, $actualTag->getAttribute('pid'), 'Sysfolder does not match');
    }

    private static function storeExpectingExceptionInGetDossierPage(tx_newspaper_Tag $tag) {
        try {
            $tag->store();
        } catch(tx_newspaper_IllegalUsageException $e) {
            if (strpos($e->getMessage(), ' is not associated with a newspaper section') === false) {
                throw $e;
            }
        }
    }

    public function test_storeEmptyTagNoAttributes() {
        $this->setExpectedException('tx_newspaper_EmptyResultException');

            $aTag = new tx_newspaper_Tag();
            self::storeExpectingExceptionInGetDossierPage($aTag);
            $this->fail('tx_newspaper_EmptyResultException expected, missing content and tagtype not spotted');
    }


    public function test_storeEmptyTagOnlyTagType() {
        $this->setExpectedException('tx_newspaper_IllegalUsageException');

            //only tag_type set
            $aTag = new tx_newspaper_Tag();
            $aTag->setAttribute('tag_type', 1);
            self::storeExpectingExceptionInGetDossierPage($aTag);
            $this->fail('tx_newspaper_IllegalUsageException expected, content not set');
    }

    public function test_storeEmptyTagOnlyContent() {
        $this->setExpectedException('tx_newspaper_IllegalUsageException');

            //only content set
            $aTag = new tx_newspaper_Tag();
            $aTag->setAttribute('tag', 'test-tag');
            self::storeExpectingExceptionInGetDossierPage($aTag);
            $this->fail('tx_newspaper_IllegalUsageException expected, missing tag-type not spotted');
    }

    public function test_storeEmptyTagEmptyContent() {
        $this->setExpectedException('tx_newspaper_IllegalUsageException');

            //check empty content set
            $aTag = new tx_newspaper_Tag();
            $aTag->setAttribute('tag_type', 1);
            $aTag->setAttribute('tag', '');
            self::storeExpectingExceptionInGetDossierPage($aTag);
            $this->fail('tx_newspaper_IllegalUsageException expected, empty string in content not spotted');
    }

    public function test_storeEmptyTagNullContent() {
        $this->setExpectedException('tx_newspaper_IllegalUsageException');

            //check empty content set
            $aTag = new tx_newspaper_Tag();
            $aTag->setAttribute('tag_type', 1);
            $aTag->setAttribute('tag', null);
            self::storeExpectingExceptionInGetDossierPage($aTag);
            $this->fail('tx_newspaper_IllegalUsageException expected, null as content not spotted');
    }

    /**
     * Ensure tags are olny stored when tag and tag_type are set.
     * @return void
     */
    public function test_storeEmptyTagWhitespaceOnlyContent() {

        $this->setExpectedException('tx_newspaper_IllegalUsageException');

        //check empty content set
        $aTag = new tx_newspaper_Tag();
        $aTag->setAttribute('tag_type', 1);
        $aTag->setAttribute('tag', ' ');
        self::storeExpectingExceptionInGetDossierPage($aTag);
    }


    public function test_storeTagOnlyOnce() {

        $aTag = new tx_newspaper_Tag();
        $aTag->setAttribute('tag', 'test-tag');
        $aTag->setAttribute('tag_type', 1);
        self::storeExpectingExceptionInGetDossierPage($aTag);

        $duplicateTag = new tx_newspaper_Tag();
        $duplicateTag->setAttribute('tag', 'test-tag');
        $duplicateTag->setAttribute('tag_type', 1);
        self::storeExpectingExceptionInGetDossierPage($duplicateTag);

        $this->assertEquals($aTag->getUid(), $duplicateTag->getUid(), 'Uids did not match. Duplicated tag in database.');

		$aCtrlTag = tx_newspaper_Tag::createControlTag(1, 'test-tag');
        self::storeExpectingExceptionInGetDossierPage($aCtrlTag);

		$duplicateCtrlTag = tx_newspaper_Tag::createControlTag(1, 'test-tag');
        self::storeExpectingExceptionInGetDossierPage($duplicateCtrlTag);

		$this->assertEquals($aCtrlTag->getUid(), $duplicateCtrlTag->getUid(), 'Uids did not match. Duplicated tag in database.');


    }

	public function test_storeCtrlTagOnlyOnce() {
		$aTag = tx_newspaper_Tag::createControlTag(1,'test-tag');
        self::storeExpectingExceptionInGetDossierPage($aTag);

        $duplicateTag = tx_newspaper_Tag::createControlTag(1,'test-tag');
        self::storeExpectingExceptionInGetDossierPage($duplicateTag);

        $this->assertEquals($aTag->getUid(), $duplicateTag->getUid(), 'Uids did not match. Duplicated tag in database.');
	}

	public function test_storeCtrlTagTwiceInDifferentCategories() {
		$aTag = tx_newspaper_Tag::createControlTag(1,'test-tag');
        self::storeExpectingExceptionInGetDossierPage($aTag);

        $duplicateTag = tx_newspaper_Tag::createControlTag(2,'test-tag');
        self::storeExpectingExceptionInGetDossierPage($duplicateTag);

        $this->assertNotEquals($aTag->getUid(), $duplicateTag->getUid(), 'Uids do match. Same tag in different categories should be allowed.');
	}

    public function test_createContentTag() {
        $tag = tx_newspaper_Tag::createContentTag();
        $tag->setAttribute('tag', 'test');
        self::storeExpectingExceptionInGetDossierPage($tag);
        $this->assertEquals('test', $tag->getAttribute('tag'));
        $this->assertEquals(tx_newspaper_tag::getContentTagType(), $tag->getAttribute('tag_type'));

        $tag = tx_newspaper_Tag::createContentTag('test');
        self::storeExpectingExceptionInGetDossierPage($tag);
        $this->assertEquals('test', $tag->getAttribute('tag'));
        $this->assertEquals(tx_newspaper_tag::getContentTagType(), $tag->getAttribute('tag_type'));
    }

    public function test_createControlTag() {
        $tagCatId = 1;
		$tag = tx_newspaper_Tag::createControlTag($tagCatId, 'test');
        $tag->setAttribute('tag', 'test');
        self::storeExpectingExceptionInGetDossierPage($tag);
        $this->assertEquals('test', $tag->getAttribute('tag'));
        $this->assertEquals(tx_newspaper_tag::getControlTagType(), $tag->getAttribute('tag_type'));
		$this->assertEquals($tagCatId, $tag->getAttribute('ctrltag_cat'));

        $tag = tx_newspaper_Tag::createControlTag($tagCatId, 'test2');
        self::storeExpectingExceptionInGetDossierPage($tag);
        $this->assertEquals('test2', $tag->getAttribute('tag'));
        $this->assertEquals(tx_newspaper_tag::getControlTagType(), $tag->getAttribute('tag_type'));
		$this->assertEquals($tagCatId, $tag->getAttribute('ctrltag_cat'));

		try {
			$tag = new tx_newspaper_Tag();
			$tag->setAttribute('tag', 'test');
			$tag->setAttribute('tag_type', tx_newspaper_Tag::getControlTagType());
            self::storeExpectingExceptionInGetDossierPage($tag);
			$this->fail('store a ctrl tag without category was possible');
		} catch(Exception $e) {
			//expected
		}
    }


    public function test_storeSameValueDifferentType() {
        $controlTag = tx_newspaper_Tag::createControlTag(1, 'test');
        $contentTag = tx_newspaper_Tag::createContentTag('test');

        self::storeExpectingExceptionInGetDossierPage($contentTag);
        self::storeExpectingExceptionInGetDossierPage($controlTag);

        $this->assertNotEquals(0, $contentTag->getUid(), 'content tag was not stored');
        $this->assertNotEquals(0, $controlTag->getUid(), 'control tag was not stored');

        $this->assertNotEquals($controlTag->getUid(), $contentTag->getUid(), 'Tags of different type and same content should be allowed');
    }

}

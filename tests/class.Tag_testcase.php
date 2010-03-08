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
        parent::setUp();
        $this->tag = new tx_newspaper_Tag();
        $this->tag->setAttribute('tag', 'test-tag-1');
        $this->tag->setAttribute('tag_type', tx_newspaper::getControlTagType());
        $this->tag->store();
    }

    /**
     * Checks that a tag is read correctly from database
     * @return void
     */
    public function test_readTag() {
        $actualTag = new tx_newspaper_Tag($this->tag->getUid());
        $this->assertEquals($this->tag->getUid(), $actualTag->getUid());
        $this->assertEquals($this->tag->getAttribute('tag'), $actualTag->getAttribute('tag'), 'Tags does not match');
        $this->assertEquals($this->tag->getAttribute('tag_type'), $actualTag->getAttribute('tag_type'), 'Tagtype does not match');
        $expectedPid = tx_newspaper_Sysfolder::getInstance()->getPid($actualTag);
        $this->assertEquals($expectedPid, $actualTag->getAttribute('pid'), 'Sysfolder does not match');
    }

    /**
     * Ensure tags are olny stored when tag and tag_type are set.
     * @return void
     */
    public function test_storeEmptyTag() {

        //stored with no attributes set
        try {
            $aTag = new tx_newspaper_Tag();
            $aTag->store();
            $this->fail('tx_newspaper_EmptyResultException expected, missing content and tagtype not spotted');
        } catch(tx_newspaper_EmptyResultException $e) {
            //expected
        }

        try {
            //only tag_type set
            $aTag = new tx_newspaper_Tag();
            $aTag->setAttribute('tag_type', 1);
            $aTag->store();
            $this->fail('tx_newspaper_IllegalUsageException expected, content not set');
        } catch(tx_newspaper_IllegalUsageException $e) {
            //expected
        }

        try {
            //only content set
            $aTag = new tx_newspaper_Tag();
            $aTag->setAttribute('tag', 'test-tag');
            $aTag->store();
            $this->fail('tx_newspaper_IllegalUsageException expected, missing tag-type not spotted');
        } catch(tx_newspaper_IllegalUsageException $e) {
            //expected
        }

        try {
            //check empty content set
            $aTag = new tx_newspaper_Tag();
            $aTag->setAttribute('tag_type', 1);
            $aTag->setAttribute('tag', '');
            $aTag->store();
            $this->fail('tx_newspaper_IllegalUsageException expected, empty string in content not spotted');
        } catch(tx_newspaper_IllegalUsageException $e) {
            //expected
        }

        try {
            //check empty content set
            $aTag = new tx_newspaper_Tag();
            $aTag->setAttribute('tag_type', 1);
            $aTag->setAttribute('tag', null);
            $aTag->store();
            $this->fail('tx_newspaper_IllegalUsageException expected, null as content not spotted');
        } catch(tx_newspaper_IllegalUsageException $e) {
            //expected
        }

        try {
            //check empty content set
            $aTag = new tx_newspaper_Tag();
            $aTag->setAttribute('tag_type', 1);
            $aTag->setAttribute('tag', ' ');
            $aTag->store();
            $this->fail('tx_newspaper_IllegalUsageException expected, blank as content not spotted');
        } catch(tx_newspaper_IllegalUsageException $e) {
            //expected
        }
    }

    public function test_storeTagOnlyOnce() {

        $aTag = new tx_newspaper_Tag();
        $aTag->setAttribute('tag', 'test-tag');
        $aTag->setAttribute('tag_type', 1);
        $aTag->store();

        $duplicateTag = new tx_newspaper_Tag();
        $duplicateTag->setAttribute('tag', 'test-tag');
        $duplicateTag->setAttribute('tag_type', 1);
        $duplicateTag->store();
        
        $this->assertEquals($aTag->getUid(), $duplicateTag->getUid(), 'Uids did not match. Duplicated tag in database.');

    }

    public function test_createContentTag() {
        $tag = tx_newspaper_Tag::createContentTag();
        $tag->setAttribute('tag', 'test');
        $tag->store();
        $this->assertEquals('test', $tag->getAttribute('tag'));
        $this->assertEquals(tx_newspaper::getContentTagType(), $tag->getAttribute('tag_type'));

        $tag = tx_newspaper_Tag::createContentTag('test');        
        $tag->store();
        $this->assertEquals('test', $tag->getAttribute('tag'));
        $this->assertEquals(tx_newspaper::getContentTagType(), $tag->getAttribute('tag_type'));
    }

    public function test_createControlTag() {
        $tag = tx_newspaper_Tag::createControlTag();
        $tag->setAttribute('tag', 'test');
        $tag->store();
        $this->assertEquals('test', $tag->getAttribute('tag'));
        $this->assertEquals(tx_newspaper::getControlTagType(), $tag->getAttribute('tag_type'));

        $tag = tx_newspaper_Tag::createControlTag('test');
        $tag->store();
        $this->assertEquals('test', $tag->getAttribute('tag'));
        $this->assertEquals(tx_newspaper::getControlTagType(), $tag->getAttribute('tag_type'));
    }


    private $tag;
}

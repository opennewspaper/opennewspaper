<?php
/*
 * Created on Oct 27, 2008
 *
 * Author: lene
 */

require_once(PATH_typo3conf . 'ext/newspaper/Classes/class.tx_newspaper_pagezone.php');
require_once(PATH_typo3conf . 'ext/newspaper/tests/class.tx_newspaper_database_testcase.php');
/// testsuite for class tx_newspaper_pagezone
class test_ArticleTextParagraphs_testcase extends tx_newspaper_database_testcase {


    function setUp() {
    }

    function tearDown() {
    }

    public function test_splitIntoParagraphs() {
        $splitter = new tx_newspaper_ArticleTextParagraphs(
            tx_newspaper_Article::createFromArray(self::$article_data)
        );
        $paragraphs = $splitter->toArray();

        foreach ($paragraphs as $paragraph) {
            $this->assertGreaterThan(
                0, preg_match('#^<p class="bodytext">(.*)</p>$#', $paragraph['text']),
                $paragraph['text']
            );
        }
    }

    public function test_splitIntoParagraphsWithWrap() {
        $splitter = new tx_newspaper_ArticleTextParagraphs(
            tx_newspaper_Article::createFromArray(self::$article_data),
            '<h1>', '</h1>'
        );
        $paragraphs = $splitter->toArray();

        foreach ($paragraphs as $paragraph) {
            $this->assertGreaterThan(
                0, preg_match('#^<h1>(.*)</h1>$#', $paragraph['text']),
                $paragraph['text']
            );
        }
    }

    public function test_splitIntoParagraphsHeadersAreNotWrapped() {
        $splitter = new tx_newspaper_ArticleTextParagraphs(
            tx_newspaper_Article::createFromArray(
                array('bodytext' => "first paragraph\n<h1>subheading</h1>\nsecond paragraph")
            )
        );
        $paragraphs = $splitter->toArray();

        foreach ($paragraphs as $paragraph) {
            if (!preg_match('#subheading#', $paragraph['text'])) continue;
            $this->assertEquals(
                0, intval(preg_match('#^<p class="bodytext">(.*)</p>$#', $paragraph['text'])),
                $paragraph['text']
            );
        }
    }


    private static $article_data = array('bodytext' => "first paragraph\nsecond paragraph");
}
?>

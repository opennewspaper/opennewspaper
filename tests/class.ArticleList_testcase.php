<?php

require_once(PATH_typo3conf . 'ext/newspaper/tests/class.tx_newspaper_database_testcase.php');
/// testsuite for class tx_newspaper_pagezone
class test_ArticleList_testcase extends tx_newspaper_database_testcase {

    public function test_StoreArticleList() {
        $sectionUid = tx_newspaper::insertRows('tx_newspaper_section', array('section_name' => 'dunmy'));
        $dummySection = new tx_newspaper_Section($sectionUid);
        $al = new tx_newspaper_ArticleList_Semiautomatic(0, $dummySection);
        $al->setAttribute('notes', 'dummy-section-al');
        $al->store();
    }

}

?>
<?php

require_once(PATH_typo3conf . 'ext/newspaper/Classes/class.tx_newspaper_extra.php');
require_once(PATH_typo3conf . 'ext/newspaper/Classes/extra/class.tx_newspaper_extra_externallinks.php');

/// tx_newspaper_Extra displaying a box of links of various type
/** Contains links:
 *  - to Articles which are grouped with the current article automatically
 *  - to manually selected Articles
 *  - to links on the same site which are not to Articles
 *  - to external URLs.
 *  Articles which are grouped as "related" are selected in the GUI for every
 *  Article. Internal and external links are technically the same, but separated
 *  for layout reasons.
 *
 *  Attributes:
 *  - \p show_related_articles (bool)
 *  - \p manually_selected_articles (comma-separated list of article UIDs)
 *  - \p internal_links (comma-separated list of tx_newspaper_ExternalLink UIDs)
 *  - \p external_links (comma-separated list of tx_newspaper_ExternalLink UIDs)
 */
class tx_newspaper_Extra_ComboLinkBox extends tx_newspaper_Extra {

    const article_table = 'tx_newspaper_article';

    public function __construct($uid = 0) {
        if ($uid) {
            parent::__construct($uid);
        }
    }

    public function __toString() {
        try {
        return 'Extra: UID ' . $this->getExtraUid() . ', Combo link box Extra: UID ' . $this->getUid();
        } catch(Exception $e) {
            return "ComboLinkBox: Exception thrown!" . $e;
        }
    }

    /// Assigns Articles and Links to the smarty template and renders it.
    /** Smarty template:
     *  \include res/templates/tx_newspaper_extra_combolinkbox.tmpl
     */
    public function render($template_set = '') {

        tx_newspaper_ExecutionTimer::start();

        $this->getAttribute('uid');

        $this->prepare_render($template_set);

        if ($this->getAttribute('show_related_articles') &&
            intval(t3lib_div::_GP(tx_newspaper::GET_article()))) {

            $this->smarty->assign('related_articles', $this->getRelatedArticles());
        }

        if ($this->getAttribute('manually_selected_articles')) {
            $this->smarty->assign('manually_selected_articles', $this->getManuallySelectedArticles());
        }

        if ($this->getAttribute('internal_links')) {
            $this->smarty->assign('internal_links', $this->getInternalLinks());
        }

        if ($this->getAttribute('external_links')) {
            $this->smarty->assign('external_links', $this->getExternalLinks());
        }

        $rendered = $this->smarty->fetch($this->getSmartyTemplate());

        tx_newspaper_ExecutionTimer::logExecutionTime();

        return $rendered;
    }

    /// Displays the Tag Zone operating on.
    public function getDescription() {
        return "<p><strong>" . $this->getAttribute('title') . "</strong></p>" . "<p>" . $this->getAttribute('short_description') . "</p>";
    }

    public static function getModuleName() {
        return 'np_combo_link_box';
    }

    public static function dependsOnArticle() { return true; }

    ////////////////////////////////////////////////////////////////////////////

    public function getRelatedArticles() {
        $current_article = new tx_newspaper_Article(t3lib_div::_GP(tx_newspaper::GET_article()));

        return $current_article->getRelatedArticles();
    }

    public function getManuallySelectedArticles() {
        $articles = array();
        foreach ($this->getManuallySelectedArticleUids() as $article_uid) {
            $articles[] = new tx_newspaper_Article(intval(trim($article_uid)));
        }
        return $articles;
    }

    public function getManuallySelectedArticleUids() {
        $rows = tx_newspaper::selectRows(
            'uid',
            'tx_newspaper_article',
            'uid IN (' . $this->getAttribute('manually_selected_articles') . ')',
            '', 'publish_date DESC'
        );

        return array_map('array_pop', $rows);
    }

    public function getInternalLinks() {
        return self::getLinks($this->getAttribute('internal_links'));
    }

    public function getExternalLinks() {
        return self::getLinks($this->getAttribute('external_links'));
    }

    private static function getLinks($links_csv) {
        $links = array();
        foreach (explode(',', trim($links_csv)) as $link_uid) {
            $links[] = new tx_newspaper_ExternalLink(intval(trim($link_uid)));
        }
        return $links;
    }


    // Typo3 hooks

    /**
     * Typo3 hook, see class.t3lib_tceforms.php
     * @param $table string Table name
     * @param $field string Field name
     * @param $row array Record
     * @param $altName
     * @param $palette
     * @param $extra
     * @param $pal
     * @param $pObj
     */
    public static function getSingleField_preProcess($table, $field, $row, $altName, $palette, $extra, $pal, $pObj) {
        self::checkFieldManuallySelectedArticles($table, $field, $row);
    }

    /**
     * Hides field manually_selected_articles if extra hasn't been stored and displays a message.
     * @param $table string Table name
     * @param $field string Field name
     * @param $row array Record
     */
    private static function checkFieldManuallySelectedArticles($table, $field, $row) {
        if ($table != 'tx_newspaper_extra_combolinkbox' || $field != 'manually_selected_articles') {
            return; // Wrong field, nothing to do ...
        }

        if (intval($row['uid'])) {
            return; // uid found, so this extra has been stored already
        }

        // New extra, so newspaper can't assign articles to this extra (becuase the uid is missing)
        unset($GLOBALS['TCA']['tx_newspaper_extra_combolinkbox']['columns']['manually_selected_articles']['config']);

        // Render message
        $message = t3lib_div::makeInstance('t3lib_FlashMessage', tx_newspaper::getTranslation('flashMessage_extra_combolinkbox_new'), 'Combo link box', t3lib_FlashMessage::WARNING);
        t3lib_FlashMessageQueue::addMessage($message);

    }

}

tx_newspaper_Extra::registerExtra(new tx_newspaper_Extra_ComboLinkBox());

?>
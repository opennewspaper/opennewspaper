<?php

require_once(PATH_typo3conf . 'ext/newspaper/Classes/class.tx_newspaper_extra.php');

/// A tx_newspaper_Extra that can display a tx_newspaper_ArticleList
/** A rather generic class that displays an article list using a specified
 *  smarty template.
 *
 *  This Extra must be inserted on a Page Zone wherever a list of Articles is
 *  displayed, except for the cases which have specialized Extras:
 *  - tx_newspaper_Extra_SectionList: The list of articles belonging to a tx_newspaper_Section
 *
 *  Attributes:
 *  - \p description (string)
 *  - \p articlelist (UID of abstract record for displayed article list)
 *  - \p first_article (int)
 *  - \p num_articles (int)
 *  - \p template (string)
 */
class tx_newspaper_extra_ArticleList extends tx_newspaper_Extra {

    public function __construct($uid = 0) {
        if (intval($uid)) parent::__construct($uid);
    }

    function getDescription() {
        try {
            $this->readArticleList();
            return $this->getAttribute('short_description') . '<br />' . $this->articlelist->getDescription();
        } catch (tx_newspaper_DBException $e) {
            return '
                <table>
                    <tr>
                        <td valign="middle">' . tx_newspaper_BE::renderIcon('gfx/icon_warning2.gif', '') .'</td>
                        <td>' .
                            tx_newspaper::getTranslation('message_articlelist_missing_deleted') . '<br />' .
                            tx_newspaper::getTranslation('message_select_another_articlelist') . '</td>
                    </tr>
                </table>';
        }
    }

    /**
     *  Assign the list of articles to a Smarty template. The template must contain all
     *  logic to display the articles.
     *
     *  @param string $template_set Template set to use
     *
     *  Smarty template:
     *  @include res/templates/tx_newspaper_extra_articlelist.tmpl
     */
    public function render($template_set = '') {

        tx_newspaper_ExecutionTimer::start();

        $this->prepare_render($template_set);

        try {
            $this->readArticleList();
        } catch (tx_newspaper_EmptyResultException $e) {
            return $this->smarty->fetch('error_articlelist_missing.tmpl');
        }

        $num = $this->getAttribute('num_articles')? $this->getAttribute('num_articles'): 1;
        $first = $this->getAttribute('first_article')? $this->getAttribute('first_article')-1: 0;
        $articles = $this->articlelist->getArticles($num, $first);

        $this->smarty->assign('articles', $articles);

        $rendered = $this->smarty->fetch($this->getSmartyTemplate());

        tx_newspaper_ExecutionTimer::logExecutionTime();

        return $rendered;
    }

    public static function getModuleName() { return 'np_artlist'; }

    public static function dependsOnArticle() { return false; }

    private function readArticlelist() {

        if ($this->articlelist instanceof tx_newspaper_ArticleList) return;

        $this->articlelist = tx_newspaper_ArticleList_Factory::getInstance()->create(
            $this->getAttribute('articlelist')
        );
    }

    /** @var tx_newspaper_ArticleList */
    private $articlelist;

}

tx_newspaper_Extra::registerExtra(new tx_newspaper_extra_ArticleList());

?>
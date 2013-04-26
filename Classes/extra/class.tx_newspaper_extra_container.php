<?php

require_once(PATH_typo3conf . 'ext/newspaper/Classes/class.tx_newspaper_extra.php');

/// tx_newspaper_Extra displaying other Extras inside it.
/**
 *  Attributes:
 *  - \p extras (UIDs of tx_newspaper_Extra records)
 */
class tx_newspaper_Extra_Container extends tx_newspaper_Extra implements tx_newspaper_ContainerExtra {

    public function __construct($uid = 0) {
        if ($uid) parent::__construct($uid);
    }

    public function __toString() {
        $ret = "<p>Container:</p>\n";
        foreach ($this->getExtras() as $extra) {
            $ret .= '<p>' . $extra->__toString() . "</p>\n";
        }
        return $ret;
    }

    /// Assigns extras to be rendered to the smarty template and renders it.
    /** If no Extras match, returns nothing.
     *
     *  Smarty template:
     *  \include res/templates/tx_newspaper_extra_container.tmpl
     */
    public function render($template_set = '') {

        $extras = $this->getExtras();
        if (empty($extras)) return '';

        $rendered_extras = array();
        foreach ($extras as $extra) {
            $rendered_extras[] = $extra->render($template_set);
        }

        $this->prepare_render($template_set);

        $this->smarty->assign('extras', $extras);
        $this->smarty->assign('rendered_extras', $rendered_extras);

        $rendered = $this->smarty->fetch($this->getSmartyTemplate());

        return $rendered;
    }

    /// Displays the Tag Zone operating on.
    public function getDescription() {
        $ret = ($this->getAttribute('short_description')? $this->getAttribute('short_description') . '<br />' : '');
        foreach ($this->getExtras() as $extra) {
            $ret .= '<p>' . $extra->getDescription() . "</p>\n";
        }
        return $ret;
    }

    public function contains(tx_newspaper_Extra $extra) {
        return in_array($extra, $this->getExtras());
    }

    public static function getExtraWhichContains(tx_newspaper_Extra $extra) {
        $uids = tx_newspaper_DB::getInstance()->selectZeroOrOneRows(
            'uid', 'tx_newspaper_extra_container', 'extras LIKE "%' . $extra->getExtraUid() .'%"'
        );
        $uid = intval($uids['uid']);
        if ($uid == 0) return null;
        return new tx_newspaper_Extra_Container($uid);
    }

    public static function getModuleName() {
        return 'np_extra_container';
    }

    public static function dependsOnArticle() { return false; }

    ////////////////////////////////////////////////////////////////////////////

    /**
     *  @return tx_newspaper_Extra[] the Extras displayed in this Extra
     */
    private function getExtras() {
        $extra = array();

        foreach (explode(',', $this->getAttribute('extras')) as $uid) {
            if (intval($uid)) {
                $extra[] = tx_newspaper_Extra_Factory::getInstance()->create($uid);
            }
        }

        return $extra;
    }

}

tx_newspaper_Extra::registerExtra(new tx_newspaper_Extra_Container());

?>
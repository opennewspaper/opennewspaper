<?php

require_once(PATH_typo3conf . 'ext/newspaper/Classes/class.tx_newspaper_extra.php');

/// tx_newspaper_Extra displaying another Extra depending on Control Tags.
/** An article can have control tags associated with it. Depending on these tags
 *  Extras can be displayed exclusively with Articles which have these tags.
 *
 *  Every Control Tag Zone Extra is associated to one Control Tag Zone, so the
 *  backend user knows where the Extra is placed. These Zones are named so the
 *  user recognized where they are. The user must take care to place Control Tag
 *  Extras only in appropriate places.
 *
 *  \em Example: A Zone is called "Below Article". The user can place a Control
 *  Tag Zone Extra anywhere on any Page Zone, and \em should of course place it
 *  only below an Article.
 *
 *  The correlation from Extras to Control Tags is done in the Backend Module
 *  \p mod6, tx_newspaper_module6.
 *
 *  Attributes:
 *  - \p tag_zone (UID of tx_newspaper_tag_zone record)
 *  - \p tag_type (string)
 *  - \p default_extra (UIDs of tx_newspaper_Extra records)
 */
class tx_newspaper_Extra_ControlTagZone extends tx_newspaper_Extra implements tx_newspaper_ContainerExtra {

    ///    SQL table matching tx_newspaer_Extra s to Control Tags and Tag Zones
    const controltag_to_extra_table = 'tx_newspaper_controltag_to_extra';
    ///    SQL table n which Tag Zones are stored
    const tag_zone_table = 'tx_newspaper_tag_zone';
    ///    SQL table in which tx_newspaper_Tag s are stored
    const tag_table = 'tx_newspaper_tag';
    ///    SQL table associating tx_newspaper_Tag s with tx_newspaper_Article s
    const article_tag_mm_table = 'tx_newspaper_article_tags_mm';

    public function __construct($uid = 0) {
        if ($uid) {
            parent::__construct($uid);
        }
    }

    public function __toString() {
        try {
        return 'Extra: UID ' . $this->getExtraUid() . ', Control tag Extra: UID ' . $this->getUid() .
                ' (Tag zone: ' . $this->getAttribute('tag_zone') . ')';
        } catch(Exception $e) {
            return "ControlTagZone: Exception thrown!" . $e;
        }
    }

    /// Assigns extras to be rendered to the smarty template and renders it.
    /** If no Extras match, returns nothing.
     *
     *  Smarty template:
     *  \include res/templates/tx_newspaper_extra_controltagzone.tmpl
     */
    public function render($template_set = '') {

        $extras = $this->getExtras($this->getControlTags());

        if (!$extras) return;

        $rendered_extras = array();
        foreach ($extras as $tag => $extra) {
            $extra->assignSmartyVar(array('dossier_link' => self::getDossierLink($tag)));
            $rendered_extras[] = $extra->render();
        }

        $this->prepare_render($template_set);

        $this->smarty->assign('extras', $rendered_extras);

        $rendered = $this->smarty->fetch($this->getSmartyTemplate());

        return $rendered;
    }

    /// Displays the Tag Zone operating on.
    public function getDescription() {
        try {
            $tag_zone = tx_newspaper::selectOneRow(
                'name', self::tag_zone_table,
                'uid = ' . $this->getAttribute('tag_zone')
            );
            return ($this->getAttribute('short_description')? $this->getAttribute('short_description') . '<br />' : '') .
                ' (' . $tag_zone['name'] . ')';
        } catch (tx_newspaper_DBException $e) {
            return ($this->getAttribute('short_description')? $this->getAttribute('short_description') . '<br />' : '') . ' (' .
                   tx_newspaper::getTranslation('message_no_controltag_selected') .
                   ')';
        }

    }

    public function contains(tx_newspaper_Extra $extra) {
        return in_array($extra, $this->getExtras($this->getControlTags()));
    }

    public static function getExtraWhichContains(tx_newspaper_Extra $extra) {

        $tag_zones = tx_newspaper_DB::getInstance()->selectRows(
            'tag_zone, tag_type', self::controltag_to_extra_table, 'extra = ' . $extra->getExtraUid()
        );
        if (empty($tag_zones)) return null;

        foreach ($tag_zones as $tag_zone) {
            $uids = tx_newspaper_DB::getInstance()->selectRows(
                'uid', 'tx_newspaper_extra_controltagzone',
                'tag_zone = "' . $tag_zone['tag_zone'] .'" AND tag_type = "' . $tag_zone['tag_type'] . '"'
            );
            $uid = intval($uids['uid']);
            if ($uid) return new tx_newspaper_Extra_ControlTagZone($uid);
        }

        return null;
    }

    public static function getModuleName() {
        return 'np_control_tag_extra';
    }

    public static function dependsOnArticle() { return false; }

    ////////////////////////////////////////////////////////////////////////////

    /// Find out which control tags are currently active
    /** Reads the Control Tags associated with the currently displayed Article
     *  from the article_tag_mm_table.
     *  \return UIDs of control tags for the currently displayed Article
     */
    private function getControlTags() {
        $tag_uids = array();

        if (intval(t3lib_div::_GP(tx_newspaper::GET_article()))) {
            $article = new tx_newspaper_article(t3lib_div::_GP(tx_newspaper::GET_article()));
            $tags = tx_newspaper::selectRows(
                self::tag_table . '.uid',
                self::tag_table .
                    ' JOIN ' . self::article_tag_mm_table .
                    ' ON ' . self::tag_table . '.uid = ' . self::article_tag_mm_table . '.uid_foreign',
                self::article_tag_mm_table . '.uid_local = ' . $article->getUid() .
                ' AND ' . self::tag_table . '.tag_type = \'' . tx_newspaper_tag::getControlTagType() .'\''
            );

            foreach ($tags as $tag) $tag_uids[] = $tag['uid'];
        } else {
            if (intval(t3lib_div::_GP('dossier'))) {
                $tag_uids[] = intval(t3lib_div::_GP('dossier'));
            }
        }

        return $tag_uids;
    }

    /// Returns the Extras displayed for the Tag Zone of the object
    /** @param $control_tags Control tags present
     *  @return tx_newspaper_Extra[] Array of Extras which have been set up for the Tag
     *      Zone of the tx_newspaper_Extra_ControlTagZone object and any of the control
     *      tags in \p $control_tags. Extras for the first matching tag are returned,
     *      the following tags are ignored.
     */
    private function getExtras(array $control_tags) {
        $extra = array();

        ///    Check if an Extra is assigned for the current tag zone for any control tag
        foreach ($control_tags as $control_tag) {
            $extras_data = tx_newspaper::selectRows(
                'extra', self::controltag_to_extra_table,
                'tag = ' . $control_tag .
                ' AND tag_zone = ' . $this->getAttribute('tag_zone'),
                '',
                'sorting'
            );

            if ($extras_data) {
                foreach ($extras_data as $extra_data) {
                    $extra[$control_tag] = tx_newspaper_Extra_Factory::getInstance()->create($extra_data['extra']);
                }
            }
        }

        ///    Check if default Extra(s) are set
        if (!$extra) {
            if ($this->getAttribute('default_extra')) {
                foreach (explode(',', $this->getAttribute('default_extra')) as $extra_uid) {
                    $extra[] = tx_newspaper_Extra_Factory::getInstance()->create($extra_uid);
                }
            }
        }
        return $extra;
    }

    /// Returns the link to the dossier page for the referenced tag
    /** \param $tag $uid of the tag for which the dossier is assembled
     *  \return Link to the Typo3 page containing the dossier
     *  \throw tx_newspaper_IllegalUsageException if no dossier page is defined
     *       in TSConfig
     *  \todo make GET parameter 'dossier' configurable
     */
    static public function getDossierLink($tag) {

        if ($tag instanceof tx_newspaper_Tag) $tag = $tag->getUid();

        $dossier_page = tx_newspaper::getDossierPageID();

        $url = tx_newspaper::typolink_url(
            array(
                'id' => $dossier_page,
                tx_newspaper::getDossierGETParameter() => $tag
            ));
        return $url;
    }

}

tx_newspaper_Extra::registerExtra(new tx_newspaper_Extra_ControlTagZone());

?>
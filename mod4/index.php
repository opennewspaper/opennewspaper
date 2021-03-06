<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009 Lene Preuss, Oliver Schröder, Samuel Talleux <lene.preuss@gmail.com, typo3@schroederbros.de, samuel@talleux.de>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 * Hint: use extdeveval to insert/update function index above.
 */


/// \todo:
/**
 * Inconsistency check f�r Extras:
 * alle PZs auslesen
 * dazu alle Extra auslesen und indexOfExtra() aufrufen (try catch)
 */



/// Class to generate a BE module with 100% width
class fullWidthDoc_mod4 extends template {
    var $divClass = 'typo3-fullWidthDoc';    ///< Sets width to 100%
}



$LANG->includeLLFile('EXT:newspaper/mod4/locallang.xml');
require_once(PATH_t3lib . 'class.t3lib_scbase.php');
$BE_USER->modAccess($MCONF,1);    // This checks permissions and exits if the users has no permission for entry.
    // DEFAULT initialization of a module [END]



/**
 * Module 'Administration' for the 'newspaper' extension.
 *
 * @author    Lene Preuss, Oliver Schr�der, Samuel Talleux <lene.preuss@gmail.com, typo3@schroederbros.de, samuel@talleux.de>
 */
class  tx_newspaper_module4 extends t3lib_SCbase {
    var $pageinfo;

    /// Root of the Typo3 installation for links in the BE
    const INSTALLATION_ROOT = '';

    private static $mmTables = array(
        'tx_newspaper_articlelist_manual_articles_mm',
        'tx_newspaper_article_extras_mm',
        'tx_newspaper_article_related_mm',
        'tx_newspaper_article_sections_mm',
        'tx_newspaper_article_tags_mm',
        'tx_newspaper_extra_image_tags_mm',
        'tx_newspaper_pagezone_page_extras_mm'
    );

    /**
     * Initializes the Module
     * @return    void
     */
    function init()    {
        global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;

        // display db errors
        $GLOBALS['TYPO3_DB']->debugOutput=true;

        parent::init();

        /*
        if (t3lib_div::_GP('clear_all_cache'))    {
            $this->include_once[] = PATH_t3lib.'class.t3lib_tcemain.php';
        }
        */
    }

    /**
     * Adds items to the ->MOD_MENU array. Used for the function menu selector.
     *
     * @return    void
     */
    function menuConfig()    {
        global $LANG;
        $this->MOD_MENU = Array (
            'function' => Array (
                '1' => $LANG->getLL('mod4_newspaper_details'),
                '2' => $LANG->getLL('mod4_record_info'),
                '3' => $LANG->getLL('mod4_db_consistency_checks'),
                '4' => 'Vererbung reparieren',
                '5' => 'Re-submit articles to VG Wort',
            )
        );
        parent::menuConfig();
    }

    /**
     * Main function of the module. Write the content to $this->content
     * If you chose "web" as main module, you will need to consider the
     * \c $this->id parameter which will contain the uid-number of the page
     * clicked in the page tree
     */
    function main()    {
        global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;

        // Access check!
        $access = $BE_USER->user['uid']? true : false; // \todo: better check needed

        if ($access) {

            // Draw the header.
            $this->doc = t3lib_div::makeInstance('fullWidthDoc_mod4');
            $this->doc->backPath = $BACK_PATH;
            $this->doc->form='<form action="" method="post" enctype="multipart/form-data">';

            $basePath = (tx_newspaper::getBasePath() != '/')? tx_newspaper::getBasePath() . '/' : '/';

            // JavaScript
            $this->doc->JScode = '
                <script language="javascript" type="text/javascript">
                    script_ended = 0;
                    function jumpToUrl(URL)    {
                        document.location = URL;
                    }
                </script>
                <script type="text/javascript" src="contrib/prototype/prototype.js"> </script>
                <script type="text/javascript" src="' . $basePath . 'typo3conf/ext/newspaper/mod4/res/mod4.js"> </script>
            ';
            $this->doc->postCode='
                <script language="javascript" type="text/javascript">
                    script_ended = 1;
                    if (top.fsMod) top.fsMod.recentIds["web"] = 0;
                </script>
            ';

            $headerSection = $this->doc->getHeader('pages',$this->pageinfo,$this->pageinfo['_thePath']).'<br />'.$LANG->sL('LLL:EXT:lang/locallang_core.xml:labels.path').': '.t3lib_div::fixed_lgd_pre($this->pageinfo['_thePath'],50);

            $this->content.=$this->doc->startPage($LANG->getLL('title'));
            $this->content.=$this->doc->header($LANG->getLL('title'));
            $this->content.=$this->doc->spacer(5);
            $this->content.=$this->doc->section('',$this->doc->funcMenu('', t3lib_BEfunc::getFuncMenu($this->id,'SET[function]',$this->MOD_SETTINGS['function'],$this->MOD_MENU['function'])));
            $this->content.=$this->doc->divider(5);


            // Render content:
            $this->moduleContent();


            // ShortCut
            if ($BE_USER->mayMakeShortcut())    {
                $this->content.=$this->doc->spacer(20).$this->doc->section('',$this->doc->makeShortcutIcon('id',implode(',',array_keys($this->MOD_MENU)),$this->MCONF['name']));
            }

            $this->content.=$this->doc->spacer(10);
        } else {
            // If no access or if ID == zero

            $this->doc = t3lib_div::makeInstance('mediumDoc');
            $this->doc->backPath = $BACK_PATH;

            $this->content.=$this->doc->startPage($LANG->getLL('title'));
            $this->content.=$this->doc->header($LANG->getLL('title'));
            $this->content.=$this->doc->spacer(5);
            $this->content.=$this->doc->spacer(10);
        }

    }

    /**
     * Prints out the module HTML
     *
     * @return    void
     */
    function printContent()    {
        $this->content.=$this->doc->endPage();
        echo $this->content;
    }

    /// Generates the module content
    function moduleContent() {

        global $LANG;

        $content = '
            <style type="text/css">
            body#typo3-alt-doc-php, body#typo3-db-list-php, body#typo3-mod-web-perm-index-php, body#typo3-mod-web-info-index-php, body#typo3-mod-web-func-index-php, body#typo3-mod-user-ws-index-php, body#typo3-mod-user-ws-workspaceforms-php, body#typo3-mod-php, body#typo3-mod-tools-em-index-php, body#typo3-pagetree, body#typo3-db-new-php, body#typo3-move-el-php, body#typo3-show-rechis-php, body#ext-cms-layout-db-layout-php, body#ext-tstemplate-ts-index-php, body#ext-version-cm1-index-php, body#ext-setup-mod-index-php, body#ext-tsconfig-help-mod1-index-php, body#ext-lowlevel-dbint-index-php, body#ext-lowlevel-config-index-php, body#ext-cms-layout-db-new-content-el-php {
              overflow: auto;
            }
            </style>
            The database is checked for inconsistent data.
            <hr />';
//                                GET:'.t3lib_div::view_array($_GET).'<br />'.
//                                'POST:'.t3lib_div::view_array($_POST).'<br />';

        switch((string)$this->MOD_SETTINGS['function'])    {
            case 1:
                // Newspaper details
                $content = '<div align=center><strong>...</strong></div>';
                $this->content .= $this->doc->section($LANG->getLL('mod4_newspaper_details'), $this->renderNewspaperDetails(), 0, 1);
                break;
            case 2:
                $content .= '<div align=center><strong>' . $LANG->getLL('mod4_record_info') . '</strong></div>';
                $content .= self::getInfoForm();

                $result = t3lib_div::_GP('tx_newspaper_mod4');
                if ($result) $content .= self::getInfo($result);

                $this->content .= $this->doc->section($LANG->getLL('mod4_record_info'), $content, 0, 1);
                break;
            case 3:
                //@todo: make list of test configurable, all test may be too long or may need to much memory
                $f = $this->getListOfDbConsistencyChecks();
                for ($i = 0; $i < sizeof($f); $i++) {
                    $content .= '<br /><b>' . $f[$i]['title'] . '</b><br />';
                    $tmp = call_user_func_array($f[$i]['class_function'], $f[$i]['param']);
                    if ($tmp === true) {
                        $content .= 'No problems found<br />';
                    } else {
                        $content .= $tmp;
                    }
                }
                $this->content .= $this->doc->section('Newspaper: db consistency check', $content, 0, 1);
                break;
            case 4:
                $content .= self::getRepairForm();

                $result = t3lib_div::_GP('tx_newspaper_mod4');
                if ($result) $content .= self::getRepairResult($result);

                $this->content .=$this->doc->section('Vererbung reparieren', $content, 0, 1);
                break;
            case 5:
                $content .= self::resubmitVGWort();
                $this->content .=$this->doc->section('VG Wort reparieren', $content, 0, 1);
                break;
        }
    }

    private static function resubmitVGWort() {

    // as this is a run-once-only tool, the uids it works on are managed in
    // the most primitive way possible: by editing the source code.

        /* 
       first run: articles imported from the redsys, found via 
       SELECT DISTINCT a.uid 
       FROM messages AS m 
       JOIN participants ON m.id = participants.message_id 
       JOIN tx_newspaper_article AS a ON tx_newspapertaz_unique_key = taz_id 
       WHERE m.status = 'faulty' AND m.deleted_at IS NULL 
       AND NOT taz_namespace = 'taz-blogs' AND m.url IS NULL AND NOT a.hidden AND NOT a.deleted 
       ORDER BY a.uid;
    */
    $uids = array(
73617, 73812, 73821, 80481, 84713, 84719, 84720, 84724, 84725, 84727, 84729, 84730, 84731, 84732, 84733, 84734, 84735, 84736, 84739, 84740, 84742, 84743, 84744, 84745, 84746, 84747, 84748, 84749, 84750, 84751, 84752, 84753, 84754, 84756, 84758, 84759, 84760, 84762, 84767, 84769, 84771, 84772, 84774, 84775, 84778, 84788, 84790, 84791, 84797, 84798, 84799, 84800, 84801, 84802, 84803, 84804, 84805, 84806, 84807, 84808, 84810, 84811, 84813, 84814, 84815, 84817, 84818, 84819, 84820, 84822, 84823, 84825, 84826, 84827, 84828, 84829, 84830, 84831, 84833, 84835, 84837, 84838, 84839, 84840, 84841, 84842, 84843, 84844, 85976, 86884, 86898, 86906, 86915, 86916, 86919, 86925, 86926, 86927, 86931, 86945, 86946, 86954, 86964, 86966, 86993, 86996, 86997, 86998, 87005, 87006, 87021, 87033, 87035, 87049, 87050, 87051, 87054, 87056, 87058, 87060, 87063, 87073, 87075, 87077, 87081, 87082, 87084, 87096, 87124, 87127, 87136, 87145, 87147, 87151, 87153, 87154, 87159, 87161, 87163, 87165, 87171, 87189, 87203, 87204, 87207, 87212, 87213, 87225, 87231, 87232, 92675, 92679, 92681, 92683, 92684, 92687, 92688, 92693, 92695, 92698, 92699, 92700, 92701, 92702, 92704, 92706, 92708, 92709, 92710, 92711, 92712, 92713, 92714, 92715, 92716, 92717, 92718, 92719, 92720, 92725, 92728, 92730, 92731, 92738, 92741, 92745, 103169, 104703, 104720, 104725, 104761, 104762, 104763, 104764, 104765, 104769, 104775, 104779, 104784, 104787, 104791, 104796, 104797, 104799, 104800, 104801, 104802, 104803, 104805, 104806, 104809, 104810, 104811, 104812, 104813, 104814, 104818, 104819, 104820, 104821, 104822, 104823, 104824, 104825, 104826, 104827, 104828, 104829, 104830, 104831, 104832, 104833, 104851, 104852, 104854, 104856, 104859, 104860, 104862, 104863, 104864, 104865, 104867, 104868, 104869, 104870, 104871, 104872, 104873, 104874, 104875, 104876, 104878, 104879, 104882, 104883, 104884, 104885, 104886, 104887, 104888, 104889, 104890, 104892, 104898, 104899, 104900, 104902, 104906, 104907, 104909, 104911, 104912, 104914, 104915, 104918, 104919, 104920, 104925, 104927, 104928, 104930, 104931, 104932, 104933, 104934, 104935, 104937, 104938, 104939, 104941, 104942, 104944, 104945, 104946, 104948, 104951, 104952, 104953, 104954, 104955, 104956, 104957, 104958, 104959, 104960, 104961, 104962, 104963, 104964, 104965, 104966, 104967, 104968, 104969, 104975, 104977, 104982, 104985, 104987, 104990, 104991, 104994, 104996, 104998, 105000, 105001, 105002, 105003, 105004, 105006, 105007, 105008, 105009, 105010, 105011, 105012, 105014, 105016, 105017, 105018, 105019, 105020, 105021, 105023, 105024, 105025, 105026, 105027, 105028, 105029, 105030, 105031, 105033, 105034, 105035, 105036, 105043, 105049, 105055, 105063, 105064, 105068, 105071, 105072, 105081, 105091, 105093, 105094, 105095, 105097, 105099, 105100, 105102, 105103, 105104, 105105, 105106, 105107, 105108, 105109, 105110, 105111, 105113, 105115, 105116, 105117, 105118, 105121, 105122, 105123, 105125, 105134, 105135, 105137, 105141, 105142, 105145, 105147, 105148, 105161, 105162, 105163, 105164, 105165, 105166, 105168, 105171, 105172, 105174, 105175, 105176, 105178, 105181, 105182, 105183, 105184, 105185, 105186, 105189, 105190, 105191, 105192, 105193, 105194, 105195, 105196, 105198, 105202, 105203, 105206, 105207, 105209, 105212, 105214, 105219, 105222, 105225, 105226, 105228, 105233, 105234, 105235, 105236, 105237, 105238, 105240, 105241, 105245, 105246, 105247, 105248, 105249, 105250, 105251, 105252, 105253, 105254, 105255, 105257, 105259, 105260, 105261, 105262, 105263, 105264, 105265, 105266, 105267, 105268, 105269, 105270, 105271, 105272, 105273, 105274, 105290, 105291, 105293, 105294, 105295, 105296, 105297, 105298, 105299, 105300, 105301, 105302, 105305, 105306, 105307, 105308, 105309, 105310, 105312, 105313, 105314, 105315, 105316, 105317, 105318, 105319, 105320, 105321, 105322, 105323, 105326, 105328, 105336, 105342, 105344, 105345, 105346, 105354
        );

    /* 
       second run: articles exclusive to newspaper, found via
       SELECT a.uid 
       FROM messages AS m 
       JOIN participants ON m.id = participants.message_id 
       JOIN tx_newspaper_article AS a ON CONCAT('np_art_', a.uid) = taz_id 
       WHERE m.status = 'faulty' AND m.deleted_at IS NULL 
       AND NOT taz_namespace = 'taz-blogs' AND m.url IS NULL AND NOT a.deleted 
       ORDER BY a.uid;
        */
    $uids = array(
19884, 43152, 73174, 73511, 73678, 73702, 84656, 84763, 84764, 84816, 84854, 86810, 86930, 86937, 86939, 86958, 86959, 86960, 86975, 86978, 86981, 87009, 87018, 87036, 87038, 87040, 87047, 87098, 87111, 87118, 87708, 87711, 92723, 92734, 95489, 104636, 104689, 104788, 104793, 104795, 104804, 104808, 104816, 104837, 104881, 104893, 104893, 104908, 104916, 104921, 104921, 104940, 104947, 104970, 104981, 104986, 104997, 105005, 105022, 105022, 105037, 105038, 105041, 105045, 105046, 105052, 105054, 105058, 105058, 105061, 105070, 105077, 105079, 105080, 105080, 105084, 105084, 105085, 105086, 105088, 105101, 105101, 105114, 105124, 105139, 105151, 105157, 105159, 105187, 105188, 105210, 105216, 105221, 105224, 105224, 105230, 105231, 105244, 105278, 105279, 105284, 105286, 105303, 105335, 105350, 105350, 110563
    );

    // disabling the tool to avoid accidental resubmits
    $uids = array();

    $return = '';

    foreach ($uids as $uid) {
        $article = new tx_newspaper_Article($uid);

        // shamelessly calling a private method on tx_newspaper_taz_VGWortHandler
        // $pixel = tx_newspaper_taz_VGWortHandler::getVGWortPixel($article);
            $reflection_class = new ReflectionClass("tx_newspaper_taz_VGWortHandler");
            $method = $reflection_class->getMethod("getVGWortPixel");
            $method->setAccessible(true);
        $pixel = $method->invoke(new tx_newspaper_taz_VGWortHandler(), $article);

        $return .= "<p>$uid " . $article->getAttribute('title') . " $pixel</p>";
    }

        return $return;
    }

    private static function getRepairForm() {

        return "Sicherheitshalber deaktiviert. Reaktivieren durch Editieren des Quellcodes!";

        $mod_post = t3lib_div::_GP('tx_newspaper_mod4');
        $ret = '
        <form>
          <table>
            <tr>
              <td>Pagezone ID</td><td><input name="tx_newspaper_mod4[pagezone_id]" value="' . $mod_post['pagezone_id'] .'" /></td>
               </tr>
               <tr>
                 <td>Excluded Extra(s)</td><td><input name="tx_newspaper_mod4[extra_ids]" value="' . $mod_post['extra_ids'] .'" /></td>
               </tr>
               <tr>
                 <td>Replace with Origin Extra</td><td><input name="tx_newspaper_mod4[replace_id]" value="' . $mod_post['replace_id'] .'" /></td>
               </tr>
          </table>

          <input type="submit" value=" Go ">

        </form>
        <hr />
        ';
        return $ret;
    }

    private static function getRepairResult() {

        return "Sicherheitshalber deaktiviert. Reaktivieren durch Editieren des Quellcodes!";

        $mod_post = t3lib_div::_GP('tx_newspaper_mod4');

        $ret = "";
        try {
            if (!intval($mod_post['pagezone_id'])) throw new Exception("pagezone_id not set");
            $pz = tx_newspaper_PageZone_Factory::getInstance()->create(intval($mod_post['pagezone_id']));
            if (!$pz instanceof tx_newspaper_PageZone_Page) throw new Exception("pagezone " . $mod_post['pagezone_id'] . " is not a pagezone_page: " . print_r($pz, 1));

            $ret = "$pz";

            foreach ($pz->getExtras() as $extra) {
                if (self::isInList($extra, $mod_post['extra_ids'])) continue;

                $data = tx_newspaper_DB::getInstance()->selectOneRow(
                    '*', 'tx_newspaper_extra', 'uid = ' . $extra->getExtraUid()
                );
                $mm = tx_newspaper_DB::getInstance()->selectOneRow(
                    '*', 'tx_newspaper_pagezone_page_extras_mm',
                    'uid_local = ' . $pz->getUid() . ' AND uid_foreign = ' . $extra->getExtraUid()
                );

                $ret .= '<hr /><p>' . print_r($data, 1) . '</p><p>' . print_r($mm,1) . '</p>';

                unset($data['uid']);

                if (intval($mod_post['replace_id']) && $extra->getOriginUid() == 0) {

                    // we have checks and balances, balances and checks...

                    $origin_extra = tx_newspaper_Extra_Factory::getInstance()->create($mod_post['replace_id']);
                    if (!$origin_extra instanceof tx_newspaper_Extra || $origin_extra instanceof ErrorExtra) {
                        throw new Exception("replace origin extra " . $mod_post['replace_id'] . " is not a valid extra uid");
                    }
                    $replace_pz = $origin_extra->getPageZone();
                    $parent_pz = $pz->getParentForPlacement();
                    if ($replace_pz->getPageZoneUid() != $parent_pz->getPageZoneUid()) {
                        throw new Exception("replace origin extra " . $mod_post['replace_id'] . " is not on parent of current pagezone (" . $parent_pz->getPageZoneUid() . ") but on ".$replace_pz->getPageZoneUid());
                    }

                    // phew!
                    $data['origin_uid'] = $mod_post['replace_id'];
                }

                $new_extra_id = tx_newspaper_DB::getInstance()->insertRows('tx_newspaper_extra', $data);
                $ret .= '<hr />' . tx_newspaper_DB::getQuery() . "<br />";
                tx_newspaper_DB::getInstance()->deleteRows(
                    'tx_newspaper_pagezone_page_extras_mm',
                    'uid_local = ' . $mm['uid_local'] . ' AND uid_foreign = ' . $mm['uid_foreign']
                );
                $ret .= tx_newspaper_DB::getQuery() . "<br />";
                $mm['uid_foreign'] = $new_extra_id;
                tx_newspaper_DB::getInstance()->insertRows('tx_newspaper_pagezone_page_extras_mm', $mm);
                $ret .= tx_newspaper_DB::getQuery() . "<br />";
            }

        } catch (Exception $e) {
            $ret .= "<hr /><span style='color:red' >" . $e->getMessage()."<br />".print_r($mod_post, 1) . "</span>";
        }

        return $ret;
    }

    private static function isInList(tx_newspaper_Extra $e, $list) {
        foreach (explode(',', $list) as $uid) {
            if (intval($uid) == $e->getExtraUid()) return true;
        }
        return false;
    }

    /**
     * Render newspaper details
     * - List of registered sources
     * @return string Newspaper details
     */
    private function renderNewspaperDetails() {
        $content = '';
        $content .= $this->renderRegisteredSources();
        return $content;

    }

    /**
     * Render a list of registered sources (starts with source "new" which is actually not a registered source but always
     * available as a default source (= create an article without importing from a source)
     * @return string
     */
    private function renderRegisteredSources() {
        $content = '<ul><li>new</li>'; // Source "new" -> create a new article without importing. This "source" is always available.
        foreach(tx_newspaper::getRegisteredSources() as $key => $source) {
            $content .= "<li>$key</li>";
        }
        $content = '<div><strong>' . $GLOBALS['LANG']->getLL('mod4_registered_sources') . ':</strong><br />' . $content . '</ul></div>';
        return $content;
    }


    static function getInfoForm() {
        $mod_post = t3lib_div::_GP('tx_newspaper_mod4');
        $ret = '
        <form>
          <table>
            <tr>
              <td>Section ID</td><td><input name="tx_newspaper_mod4[section_id]" value="' . $mod_post['section_id'] .'" /></td>
            </tr>
            <tr>
              <td>Article ID</td><td><input name="tx_newspaper_mod4[article_id]" value="' . $mod_post['article_id'] .'" /></td>
            </tr>
            <tr>
              <td>Extra ID</td><td><input name="tx_newspaper_mod4[extra_id]" value="' . $mod_post['extra_id'] .'" /></td>
            </tr>
            <tr>
              <td>Article list ID</td><td><input name="tx_newspaper_mod4[articlelist_id]" value="' . $mod_post['articlelist_id'] .'" /></td>
            </tr>
            <tr>
              <td>Page zone ID</td><td><input name="tx_newspaper_mod4[pagezone_id]" value="' . $mod_post['pagezone_id'] .'" /></td>
            </tr>
          </table>

          <input type="submit" value=" Go ">

        </form>
        ';
        return $ret . '<hr />';
    }

    /// Return information about the desired records
    static function getInfo(array $mod_post) {
        $ret = '';
        if ($mod_post['section_id']) {
            $ret .= self::getSectionInfo($mod_post['section_id']);
        }
        if ($mod_post['article_id']) {
            $ret .= self::getArticleInfo($mod_post['article_id'], true);
        }
        if ($mod_post['extra_id']) {
            $ret .= self::getExtraInfo($mod_post['extra_id']);
        }
        if ($mod_post['articlelist_id']) {
            $ret .= self::getArticleListInfo($mod_post['articlelist_id']);
        }
        if ($mod_post['pagezone_id']) {
            $ret .= self::getPageZoneInfo($mod_post['pagezone_id'], true);
        }
        return $ret;
    }

    static function getSectionInfo($section_id) {
        $ret = '';
        foreach (explode(',', $section_id) as $uid) {

            // ... section
            try {
                $section = new tx_newspaper_Section(intval(trim($uid)));
                $ret .= '<p>' .
                    'Section ' . self::getRecordLink('tx_newspaper_section', $section->getUID()) .
                    ' (' . $section->getAttribute('section_name') . ')' .
                '</p>' . '<hr />';
            } catch (tx_newspaper_DBException $e) {
                $ret .= '<p><strong>No such section: ' . $uid . '.</strong></p>';
                continue;
            }

            // ... article list
            try {
                $articlelist = $section->getArticleList();
                $ret .= self::getArticleListInfo($articlelist->getAbstractUid());
                $ret .=  '<hr />';
            } catch (tx_newspaper_DBException $e) {
                $ret .= '<p>' . '<strong>' . 'No associated article list.' . '</strong>' . '</p>';
            }

            // ... default article type
            try {
                $default_article_type = new tx_newspaper_ArticleType($section->getAttribute('default_articletype'));
                $ret .= '<p>Default article type: ' . self::getRecordLink('tx_newspaper_articletype', $default_article_type->getUID()) .
                     ' (' . $default_article_type->getAttribute('title') . ')</p>' . '<hr />';
            } catch (tx_newspaper_DBException $e) {
                $ret .= '<p>' . '<strong>' . 'No default article type.' . '</strong>' . '</p>';
            }

            // ... default article
            if ($default_article = $section->getDefaultArticle()) {
                $ret .= '<p>Default article:</p>' .
                  self::getArticleInfo($default_article->getUid()) . '<hr />';
            } else {
                $ret .= '<p>' . '<strong>' . 'No default article.' . '</strong>' . '</p>';
            }

            // ... pages
            $pages = $section->getActivePages();
            if ($pages) {
                foreach ($pages as $page) {
                   $ret .= self::getPageInfo($page->getUid());
                }
                $ret .=  '<hr />';
            } else {
                $ret .= '<p>' . '<strong>' . 'No pages.' . '</strong>' . '</p>';
            }

            // ... articles. usually, lots.
            $uids = tx_newspaper::selectRows(
                'uid_local',
                'tx_newspaper_article_sections_mm',
                'uid_foreign = ' . $section->getUid(),
                '',
                'uid_local ASC'
            );
            if ($uids) {
                $ret .= '<p>Associated articles:</p>';
                foreach ($uids as $uid) {
                    $ret .= self::getArticleInfo($uid['uid_local']);
                }
            } else {
                $ret .= '<p><strong>No articles associated with section ' . $section->getUid() . '.</strong></p>';
            }

        }
        return $ret;
    }

    /**
     * \param $article_id article uid
     * \param $showExtraInfo whether to show Extra detial sinformation or not
     * \return html code
     */
    static function getArticleInfo($article_id, $showExtraInfo=false) {
        $ret = '';
        foreach (explode(',', $article_id) as $uid) {
            try {
                $article = new tx_newspaper_Article($uid);
                $ret .= '<p>' .
                            'Article: ' . self::getRecordLink('tx_newspaper_article', $article->getUid()) .
                            ' - ' . $article->getAttribute('title');
                        '</p>';
            } catch (tx_newspaper_DBException $e) {
                $ret .= '<p><strong>No such article: ' . $uid . '.</strong></p>';
                continue;
            }

            if ($showExtraInfo) {
                foreach ($article->getExtras() as $extra) {
                    $ret .= self::getExtraInfo($extra->getExtraUid());
                }
            }

        }
        return $ret;
    }

    static function getExtraInfo($extra_id) {
        $ret = '';
        foreach (explode(',', $extra_id) as $uid) {
            try {
                $extra = tx_newspaper_Extra_Factory::getInstance()->create(intval(trim($uid)));
                $ret .= '<p>' .
                            'Extra: ' . self::getRecordLink('tx_newspaper_extra', $extra->getExtraUid()) .
                            ' (' . $extra->getTable() . ' ' . self::getRecordLink($extra->getTable(), $extra->getUid()) . ') ' .
                            $extra->getDescription() .
                        '</p>';
            } catch (tx_newspaper_DBException $e) {
                $ret .= '<p><strong>No such extra: ' . $uid . '.</strong></p>';
                continue;
            }

            // is extra placed in an article?
            $row = tx_newspaper::selectZeroOrOneRows(
                'uid_local',
                'tx_newspaper_article_extras_mm',
                'uid_foreign=' . $uid
            );
            if ($row) {
                $a = new tx_newspaper_Article(intval($row['uid_local']));
                $ret .= '<p>Placed in article #' . $row['uid_local'] . ' (';
                $ret .= $a->getAttribute('kicker') . ': ' . $a->getAttribute('title');
                $ret .= ')<p>';
            }

            // is extra placed on a pagezone_page?
            $row = tx_newspaper::selectZeroOrOneRows(
                'uid_local',
                'tx_newspaper_pagezone_page_extras_mm',
                'uid_foreign=' . $uid
            );
            if ($row) {
                $pz = new tx_newspaper_PageZone_Page(intval($row['uid_local']));
                $ret .= '<p>Placed on pagezone page #' . $row['uid_local'] . ' (';
                $ret .= $pz->getPageZoneType()->getAttribute('type_name') . ' <- ';
                $ret .= $pz->getParentPage()->getPageType()->getAttribute('type_name') . ' (#' . $pz->getParentPage()->getUid() . ') <- ';
                $ret .= $pz->getParentPage()->getParentSection()->getAttribute('section_name') . ' (#' . $pz->getParentPage()->getParentSection()->getUid() . ')';
                $ret .= ')<p>';
            }

            // is extra placed in a tagzone?
            $row = tx_newspaper::selectZeroOrOneRows(
                '*',
                'tx_newspaper_controltag_to_extra',
                'extra=' . $uid
            );
            if ($row) {
                $tag = new tx_newspaper_tag(intval($row['tag']));
                $ret .= '<p>Placed in tagzone #' . $row['tag_zone'] . ' (' . tx_newspaper_tag::getTagZoneName($row['tag_zone']) . ') for ';
                $ret .= '<i>';
                if ($tag->getAttribute('tag_type') == tx_newspaper_tag::getContentTagType()) {
                    $ret .= 'content tag ';
                } elseif ($tag->getAttribute('tag_type') == tx_newspaper_tag::getControlTagType()) {
                    $ret .= 'control tag ';
                } else {
                    $ret .= ' UNKNOWN TAG TYPE ';
                }
                $ret .= '</i>';
                $ret .= '<b>' . addslashes($tag->getAttribute('tag')) . '</b> (#' . $tag->getUid() . ')';
                if ($tag->getAttribute('tag_type') == tx_newspaper_tag::getControlTagType()) {
                    $ret .= ' in dossier <b>' . $tag->getAttribute('title') . '</b>';
                    $ret .= ' with control tag category <b>' . $tag->getCategoryName() . '</b> (#' . $tag->getAttribute('ctrltag_cat') . ')';

                }

            }

        }

        return $ret;
    }

    static function getArticleListInfo($articlelist_id) {
        $ret = '';
        foreach (explode(',', $articlelist_id) as $uid) {
            try {
                $concrete_list = tx_newspaper_ArticleList_Factory::getInstance()->create(intval(trim($uid)));
                $ret .= '<p>' .
                            'Article list: ' . self::getRecordLink('tx_newspaper_articlelist', $concrete_list->getAbstractUid()) .
                            ' (' . $concrete_list->getTable() . ' ' . self::getRecordLink($concrete_list->getTable(), $concrete_list->getUid()) . ')' .
                        '</p>';
            } catch (tx_newspaper_DBException $e) {
                $ret .= '<p><strong>No such article list: ' . $uid . '.</strong></p>';
                continue;
            }

            $articles = $concrete_list->getArticles(10);
            if ($articles) {
                foreach ($articles as $article) {
                    $ret .= '<p>&nbsp;&nbsp;Article #' . self::getRecordLink('tx_newspaper_article', $article->getUid()) .
                        ' - ' . $article->getAttribute('title') . ': ' . $article->getAttribute('title') . '</p>';
                }
            } else {
                $ret .= '<p>&nbsp;&nbsp;No articles.</p>';
            }
        }
        return $ret;
    }

    static protected function getPageInfo($page_id) {
        $ret = '';
        foreach (explode(',', $page_id) as $uid) {
            try {
                $page = new tx_newspaper_Page(intval(trim($uid)));
            } catch (tx_newspaper_DBException $e) {
                $ret .= '<p><strong>No such page: ' . $uid . '.</strong></p>';
                continue;
            }

            $associated_section = new tx_newspaper_Section($page->getAttribute('section'));
            $page_type = new tx_newspaper_PageType(intval($page->getAttribute('pagetype_id')));
            $ret .= '<p>' . 'Page: ' .
                        self::getRecordLink('tx_newspaper_page', $page->getUid()) .
                        ' associated section: ' . self::getRecordLink('tx_newspaper_section', $associated_section->getUid()) .
                            ' (' . $associated_section->getAttribute('section_name') . ')' .
                        ' page type: ' . self::getRecordLink('tx_newspaper_pagetype', $page_type->getUid()) .
                            ' (' . $page_type->getAttribute('type_name') . ')' .
                    '</p>';

            $pagezones = $page->getPageZones();
            if ($pagezones) {
                foreach ($pagezones as $pagezone) {
                    $ret .= self::getPageZoneInfo($pagezone->getAbstractUid());
                }
                $ret .= '<p>&nbsp;</p>';
            } else {
                $ret .= '<p>&nbsp;&nbsp;No page zones.</p>';
            }
        }
        return $ret;
    }

    static protected function getPageZoneInfo($pagezone_id, $with_page_info = false) {
        $ret = '';
        foreach (explode(',', $pagezone_id) as $uid) {
            try {
                $pagezone = tx_newspaper_PageZone_Factory::getInstance()->create(intval(trim($uid)));
            } catch (tx_newspaper_DBException $e) {
                $ret .= '<p><strong>No such page zone: ' . $uid . '.</strong></p>';
                continue;
            }
            $pagezone_type = $pagezone->getPageZoneType();
            $ret .= '<p>Page Zone: ' . self::getRecordLink('tx_newspaper_pagezone', $pagezone->getAbstractUid()) .
                ' (' . $pagezone->getTable() . ' ' . self::getRecordLink($pagezone->getTable(), $pagezone->getUid()) . ')' .
                ' Type: ' . self::getRecordLink('tx_newspaper_pagezonetype', $pagezone_type->getUid()) .
                ' (' .$pagezone_type->getAttribute('type_name') . ')' .
                '</p>';
            if ($with_page_info) {
                $ret .= self::getPageInfo($pagezone->getParentPage()->getUid());
            }
        }
        return $ret;
    }

    static function getRecordLink($table, $id) {
        return
            '<strong>' .
                '<a href="' .
                    self::INSTALLATION_ROOT .
                       '/typo3/alt_doc.php?returnUrl=db_list.php%3Fid%3D6%26table%3D&edit[' .
                    $table .
                    '][' .
                    $id .
                    ']=edit">' .
                    $id .
                '</a>' .
            '</strong>' ;
    }

    function getListOfDbConsistencyChecks() {
/**
 * \todo
 * concrete undelete pagezone without a matching undeleted abstract pagezone
 */
        $f = array(
            array(
                'title' => 'mm tables: uid_local or uid_foreign  equals 0',
                'class_function' => array('tx_newspaper_module4', 'checkMmUidZero'),
                'param' => array()
            ),
            array(
                'title' => 'Abstract extra: concrete extra missing',
                'class_function' => array('tx_newspaper_module4', 'checkAbstractExtraConcreteExtraMissing'),
                'param' => array()
            ),
            array(
                'title' => 'Abstract extra: extra_table or extra_uid missing',
                'class_function' => array('tx_newspaper_module4', 'checkAbstractExtraTable'),
                'param' => array()
            ),
            array(
                'title' => 'Orphaned Extras: Extras which belong to no PageZone, no Article, no Tagzone, no default extra in Extra Controltagzone',
                'class_function' => array('tx_newspaper_module4', 'checkOrphanedExtras'),
                'param' => array()
            ),
            array(
                'title' => 'Abstract article list: concrete article list missing',
                'class_function' => array('tx_newspaper_module4', 'checkAbstractArticleListConcreteArticleListMissing'),
                'param' => array()
            ),
            array(
                'title' => 'Concrete article list: abstract article list missing',
                'class_function' => array('tx_newspaper_module4', 'checkConcreteArticleListAbstractArticleListMissing'),
                'param' => array()
            ),
            array(
                'title' => 'Section: multiple pages with same page type for a section',
                'class_function' => array('tx_newspaper_module4', 'checkSectionWithMultipleButSamePageType'),
                'param' => array()
            ),
            array(
                'title' => 'Section: no or deleted (abstract) article list',
                'class_function' => array('tx_newspaper_module4', 'checkSectionWithActiveArticleList'),
                'param' => array()
            ),
            array(
                'title' => 'Extra in Article: article or pagezone set as Extra',
                'class_function' => array('tx_newspaper_module4', 'checkExtraInArticleIsArticleOrPagezone'),
                'param' => array()
            ),
            array(
                'title' => 'Extra on Pagezone Page: mm-linked Extras',
                'class_function' => array('tx_newspaper_module4', 'checkLinksToDeletedExtrasPagezone'),
                'param' => array("tx_newspaper_pagezone_page_extras_mm")
            ),
            array(
                'title' => 'Extra on Article: mm-linked Extras',
                'class_function' => array('tx_newspaper_module4', 'checkLinksToDeletedExtrasPagezone'),
                'param' => array("tx_newspaper_article_extras_mm")
            ),
            array(
                'title' => 'Unknow workflow_status / role',
                'class_function' => array('tx_newspaper_module4', 'checkUnknownWorkflowStatus'),
                'param' => array()
            ),
            array(
                'title' => 'Check if all template_set fields are set to "default"',
                'class_function' => array('tx_newspaper_module4', 'checkForDefaultTemplateSet'),
                'param' => array()
            ),
            array(
                'title' => 'Check if pages are unique for section and page type',
                'class_function' => array('tx_newspaper_module4', 'checkPageUniqueness'),
                'param' => array()
            ),
            array(
                'title' => 'Check if pagezone_pages are unique for page and pagezone type',
                'class_function' => array('tx_newspaper_module4', 'checkPagezonePageUniqueness'),
                'param' => array()
            ),
            array(
                'title' => 'List articles with no section assigned',
                'class_function' => array('tx_newspaper_module4', 'checkArticleWithoutSection'),
                'param' => array()
            ),
        );
        return $f;
    }


    static function checkArticleWithoutSection() {
        // get article uids for article without a section
        $rows = tx_newspaper::selectRows(
            'tx_newspaper_article.uid',
            'tx_newspaper_article LEFT JOIN tx_newspaper_article_sections_mm ON tx_newspaper_article.uid=tx_newspaper_article_sections_mm.uid_local',
            'tx_newspaper_article_sections_mm.uid_foreign IS NULL AND tx_newspaper_article.deleted=0 AND tx_newspaper_article.is_template=0'
        );
        $errorCount = 0;
        $msg = '';
        foreach($rows as $row) {
            $errorCount++;
            $article = new tx_newspaper_Article(($row['uid']));
            $controlTags = $article->getTags(tx_newspaper_tag::getControlTagType());

            if (!$controlTags) {
                $msg .= '<span style="font-weight:bold;">';
            } else {
                $msg .= '<span>';
            }

            $msg .= $article->getAttribute('kicker') . ': ' . $article->getAttribute('title') . ' (#' . $article->getUid() . ') ';

            if ($controlTags) {
                $msg .= '<i>Control tag(s): ';
                foreach($controlTags as $controlTag) {
                    $msg .= $controlTag->getAttribute('tag') . ' ';
                }
                $msg .= '</i>';
            } else {
                $msg .= '<i>Not even a control tag is assigned.</i>';
            }
            $msg .= '</span><br (>';
        }

        if (!$errorCount) {
            return true;
        }
        $msg = $errorCount . ' problem(s) found<br />' . $msg;
        return $msg;

    }


    static function checkPagezonePageUniqueness() {
        $rows = tx_newspaper::selectRowsDirect(
            'pz.uid pz_uid, pz.page_id page_id, pz_p.pagezonetype_id pagezonetype_id',
            'tx_newspaper_pagezone pz, tx_newspaper_pagezone_page pz_p',
            'pz.pagezone_table="tx_newspaper_pagezone_page" AND pz.pagezone_uid=pz_p.uid AND pz.deleted=0'
        );
//t3lib_div::debug($rows);

        // the combination of page_id and pagezonetype_id needs to be unique
        $tmp = array();
        $errorCount = 0;
        $msg = '';
        foreach($rows as $idx => $row) {
            $key = $row['page_id'] . '|' . $row['pagezonetype_id'];
            $tmp[$key][] = $idx;
        }
//t3lib_div::debug($tmp);
        foreach($tmp as $row) {
//t3lib_div::debug($row);
            if (sizeof($row) > 1) {
                $msg .= 'Error in pagezone_page: page_id and pagezonetype_id not unique, see tables below';
                foreach($row as $bugIdx) {
                    $errorCount++;
                    $msg .= t3lib_div::view_array($rows[$bugIdx]);
                }
            }
        }
        if (!$errorCount) {
            return true;
        }
        return $msg;
    }

    static function checkPageUniqueness() {
        $rows = tx_newspaper::selectRowsDirect(
            'COUNT(pagetype_id) AS c, tx_newspaper_page.*',
            'tx_newspaper_page',
            '1',
            'section, pagetype_id HAVING deleted=0 AND c>1'
        );
        if (!sizeof($rows)) {
            return true;
        }
        $msg = sizeof($rows) . ' problem(s) found in tx_newspaper_page<br />';
        $msg .= '<table border="1" cellpadding="1" cellspacing="0"><tr><td>uid</td><td>section</td><td>pagetype_id</td><td>count</td></tr>';
        foreach($rows as $row) {
            $msg .= '<tr><td>' . $row['uid'] . '</td><td>' . $row['section'] . '</td><td>' . $row['pagetype_id'] . '</td><td>' . $row['c'] . '</td></tr>';
        }
        $msg .= '</table><br />';
        return $msg;
    }


    static function checkAbstractExtraTable() {
        $rows = tx_newspaper::selectRows(
            'uid,extra_table,extra_uid',
            'tx_newspaper_extra',
            '(extra_table="" OR extra_uid<=0)',
            '',
            'uid'
        );
        if (!sizeof($rows)) {
            return true;
        }
        $msg = sizeof($rows) . ' problem(s) found<br />';
        $msg .= '<table border="1" cellpadding="1" cellspacing="0"><tr><td>uid</td><td>extra_table</td><td>extra_uid</td></tr>';
        foreach($rows as $row) {
            $msg .= '<tr><td>' . $row['uid'] . '</td><td>' . $row['extra_table'] . '</td><td>' . $row['extra_uid'] . '</td></tr>';
        }
        $msg .= '</table><br />';
        return $msg;
    }

    static function checkMmUidZero() {
        $msg = '';
        foreach (self::$mmTables as $table) {
            $rows = tx_newspaper::selectRows(
                'uid_local, uid_foreign',
                $table,
                'uid_local=0 OR uid_foreign=0'
            );
            if ($rows) {
                $msg .= '<p><b>' . $table . '</b></p>';
                foreach ($rows as $row) {
                    $msg .= '<p>' . $row['uid_local'] . ', ' . $row['uid_foreign'] . '<p>';
                }
            }
        }
        if ($msg != '') {
            return $msg;
        }
        return true;
    }

    static function checkForDefaultTemplateSet() {

        // get tables to check
        $templateSetTables = tx_newspaper_be::getTemplateSetTables();

        $msg = '';
        foreach ($templateSetTables as $table) {
            $rows = tx_newspaper::selectRows(
                'DISTINCT template_set',
                $table,
                '(template_set<>"default" OR ISNULL(template_set)) AND deleted=0',
                '',
                'template_set'
            );
//t3lib_div::devlog('checkForDefaultTemplateSet()', 'newspaper', 0, array('q' => tx_newspaper::$query, 'rows' => $rows));
            if ($rows) {
                $msg .= '<p><b>' . $table . '</b></p>';
                foreach ($rows as $row) {
                    $msg .= '<p>Template-set: ' . $row['template_set'] . '<p>';
                }
            }
        }
        if ($msg != '') {
            $msg .= '<br /><i><a href="#" onclick="fixDefaultTemplateSet(); return false;">Set all template set fields to "default" &gt;&gt;</a></i> <span id="defaultTemplateSpinner"></span><br /><br />';
            return $msg;
        }
        return true;
    }

    static function checkSectionWithMultipleButSamePageType() {
        $msg = '';
        $GLOBALS['TYPO3_DB']->debugOutput = true;
//        $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
//            'section, pagetype_id, deleted, count(*) AS c',
//            'tx_newspaper_page',
//            '',
//            'section, pagetype_id, deleted',
//            '(c>1 AND deleted=0)'
//        );
        $res = $GLOBALS['TYPO3_DB']->sql_query('SELECT section, pagetype_id, deleted, count(*) AS c FROM tx_newspaper_page GROUP BY section, pagetype_id,deleted HAVING (c>1 AND deleted=0)');
        if (!$res)
            die('Could not read table tx_newspaper_page');
        while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
            $msg .= 'Section uid #' . $row['section'] . ' has ' . $row['c'] . ' pages of page type uid #' . $row['pagetype_id'] . '<br />';
        }
        $GLOBALS['TYPO3_DB']->sql_free_result($res);

        if ($msg != '')
            return $msg;
        return true;
    }

    /// searches abstract extras where the related concrete extra is missing or deleted
    static function checkAbstractExtraConcreteExtraMissing() {
        $msg = '';
        // get all concrete extra table where records should exist
        $abstract_extra_type_row = tx_newspaper::selectRows(
            'DISTINCT extra_table',
            'tx_newspaper_extra',
            'extra_table!=""'
        );
        for($i = 0; $i < sizeof($abstract_extra_type_row); $i++) {
//debug($abstract_extra_type_row[$i]['extra_table']);

            // get all concrete uids for this extra (from abstract table)
            $abstract_row = array();
            $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
                'uid,extra_uid',
                'tx_newspaper_extra',
                'deleted=0 AND extra_table="' . $abstract_extra_type_row[$i]['extra_table'] . '"'
            );
            if (!$res) {
                die('Could not read extra abstract rows for table ' . $abstract_extra_type_row[$i]['extra_table']);
            }
            while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
                $abstract_row[$row['extra_uid']] = $row['uid']; // key = uid of concrete extra, value = uid of abstract extra
            }
            $GLOBALS['TYPO3_DB']->sql_free_result($res);
//t3lib_div::debug($abstract_extra_type_row[$i]);

            $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
                'uid',
                $abstract_extra_type_row[$i]['extra_table'],
                'deleted=0'
            );
            if (!$res) {
                die('Could not read extra concrete rows for extra ' . $row[$i]['extra_table']);
            }
            while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
                if (isset($abstract_row[$row['uid']])) {
                    // so an abstract extra exists for this concrete extra
                    unset($abstract_row[$row['uid']]);
                }
            }
            $GLOBALS['TYPO3_DB']->sql_free_result($res);

            if (sizeof($abstract_row) > 0) {
                if ($msg != '')
                    $msg .= '<br /><br >';
                $msg = 'Problem(s) found for table ' . $abstract_extra_type_row[$i]['extra_table'] . ':<br />';
                foreach($abstract_row as $key => $value) {
                    $msg .= 'Abstract record uid #' . $value . ' is linked to non-existing concrete uid #' . $key . '<br />';
                }
            }

        }

        if ($msg != '')
            return $msg;
        return true; // no problems found
    }


    /// searches concrete article lists where the related abstract article list is missing or deleted
    static function checkConcreteArticleListAbstractArticleListMissing() {
        $msg = '';
        $article_lists = tx_newspaper_ArticleList::getRegisteredArticleLists();
        foreach($article_lists as $al) {
            // get all (undeleted) concrete article lists of current type
            $concrete_al_row = tx_newspaper::selectRows(
                'uid',
                $al->getTable(),
                'deleted=0'
            );
            for ($i = 0; $i < sizeof($concrete_al_row); $i++) {
                $abstract_al = tx_newspaper::selectRows(
                    'uid',
                    'tx_newspaper_articlelist',
                    'deleted=0 AND list_table="' . $al->getTable() . '" AND list_uid=' . $concrete_al_row[$i]['uid']
                );
                if (sizeof($abstract_al) == 0) {
                    $msg .= 'Concrete record uid #' . $concrete_al_row[$i]['uid'] . ' isn\'t linked to any abstract article list record.<br />';
                }
            }
        }
        if ($msg) {
            $msg = 'Problem(s) found:<br />' . $msg;
        }
        return $msg;
    }



    /// Searches published articles (hidden==0) with no publish date set (publish_date==0)
    static function checkArticleMissingPublishDate() {
        $msg = '';

        $rows = tx_newspaper::selectRows('*', 'tx_newspaper_article', 'deleted=0 AND hidden=0 AND publish_date=0');
        foreach($rows as $row) {
            $msg .= 'Article #' . $row['uid'] . '<br />';
        }
        if ($msg != '') {
            $msg .= '<br /><i><a href="#" onclick="fixPublishDate(); return false;">Fix all publish dates &gt;&gt;</a></i> <span id="pubDateSpinner"></span><br /><br />';
            return $msg;
        }
        return true; // no problems found
    }


    /// searches abstract article lists where the related concrete article list is missing or deleted
    static function checkAbstractArticleListConcreteArticleListMissing() {
        $msg = '';
        // get all concrete article list tables where records should exist
        $abstract_al_type_row = tx_newspaper::selectRows(
            'DISTINCT list_table',
            'tx_newspaper_articlelist'
        );
        for($i = 0; $i < sizeof($abstract_al_type_row); $i++) {
//debug($abstract_al_type_row[$i]['list_table']);

            // get all concrete uid for this article list (from abstract table)
            $abstract_row = array();
            $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
                'uid, list_uid',
                'tx_newspaper_articlelist',
                'deleted=0 AND list_table="' . $abstract_al_type_row[$i]['list_table'] . '"'
            );
            if (!$res) {
                die('Could not read article list abstract rows for table ' . $abstract_al_type_row[$i]['list_table']);
            }
            while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
                $abstract_row[$row['list_uid']] = $row['uid']; // key = uid of concrete article list, value = uid of abstract article list
            }
            $GLOBALS['TYPO3_DB']->sql_free_result($res);
//debug($abstract_row);

            $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
                'uid',
                $abstract_al_type_row[$i]['list_table'],
                'deleted=0'
            );
            if (!$res) {
                die('Could not read article list concrete rows for article list ' . $row[$i]['list_table']);
            }
            while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
                if (isset($abstract_row[$row['uid']])) {
                    unset($abstract_row[$row['uid']]); // so an abstract article list exists for this concrete article list
                }
            }
            $GLOBALS['TYPO3_DB']->sql_free_result($res);

            if (sizeof($abstract_row) > 0) {
                if ($msg != '')
                    $msg .= '<br /><br >';
                $msg = 'Problem(s) found for table ' . $abstract_al_type_row[$i]['list_table'] . ':<br />';
                foreach($abstract_row as $key => $value) {
                    $msg .= 'Abstract record uid #' . $value . ' is linked to non-existing concrete uid #' . $key . '<br />';
                }
            }

        }

        if ($msg != '')
            return $msg;
        return true; // no problems found
    }


    /// searches abstract extras where the related concrete extra is missing or deleted
    static function checkExtraInArticleIsArticleOrPagezone() {
        $msg = '';
        // get all concrete extra table where records should exist

        $row = tx_newspaper::selectRows(
            '*',
            'tx_newspaper_article_extras_mm mm, tx_newspaper_extra e',
            'mm.uid_foreign=e.uid AND (e.extra_table="tx_newspaper_pagezone_page" OR e.extra_table="tx_newspaper_article" OR e.extra_table="tx_newspaper_pagezone")'
        );

        $msg = '';
        for($i = 0; $i < sizeof($row); $i++) {
            $msg .= 'Article #' . $row[$i]['uid_local'] . ', abstract Extra #' . $row[$i]['uid_foreign'] .
                ' is stored in table ' . $row[$i]['extra_table'] . ' with #' . $row[$i]['extra_uid'] . '<br />';
        }

        if ($msg != '')
            return $msg;
        return true; // no problems found
    }




    /// searches section with no or deleted abstract article list
    static function checkSectionWithActiveArticleList() {
        $msg = '';
        $row = tx_newspaper::selectRows(
            'uid, articlelist',
            'tx_newspaper_section',
            'tx_newspaper_section.articlelist NOT IN (SELECT uid FROM tx_newspaper_articlelist WHERE tx_newspaper_articlelist.deleted=0)'
        );

        $msg = '';
        for($i = 0; $i < sizeof($row); $i++) {
            $msg .= 'Section #' . $row[$i]['uid'] . ', abstract article list #' . $row[$i]['articlelist'] . '<br />';
        }

        if ($msg != '') {
            return $msg;
        }
        return true; // no problems found
    }




    /// searches for extras which don't belong to either a pagezone or an article
    static function checkOrphanedExtras() {
        $row = tx_newspaper::selectRows(
            '*',
            'tx_newspaper_extra',
            'NOT uid in (SELECT uid_foreign FROM tx_newspaper_pagezone_page_extras_mm)
             AND NOT uid in (SELECT uid_foreign FROM tx_newspaper_article_extras_mm)
             AND NOT extra_table="tx_newspaper_article"
             AND NOT extra_table="tx_newspaper_pagezone_page" ',
             '',
             'uid'
        );

        if (!$row) return true; // no problems found

        $counter = 0;
        for($i = 0; $i < sizeof($row); $i++) {
            try {
                $concrete = tx_newspaper::selectOneRow(
                    '*', $row[$i]['extra_table'],
                    'uid = ' . $row[$i]['extra_uid']
                );
                if (!self::isExtraAssociatedWithTagzone($row[$i]['uid'])) {
                    $msg .= 'Abstract extra #' . $row[$i]['uid'] . ', concrete extra: ' . $row[$i]['extra_table'] .
                            ' #' . $row[$i]['extra_uid'] . '<br />';
                    $counter++;
                }
            } catch(tx_newspaper_EmptyResultException $e) {
                // handled in checkAbstractExtraConcreteExtraMissing()
            }
        }

        $msg = $counter . ' problem(s) found.<br />' . $msg;

        return $msg;
    }

    private static function isExtraAssociatedWithTagzone($abstractExtraUid) {
        $abstractExtraUid = intval($abstractExtraUid);

        // check if extra is assigned to a tagzone
        $row = tx_newspaper::selectZeroOrOneRows(
            'uid',
            'tx_newspaper_controltag_to_extra',
            'extra=' . $abstractExtraUid
        );
        if ($row['uid']) {
            return true; // extra is added to a tagzone
        }

        // check if extra if set as default extra to extra controltagzone
        $rows = tx_newspaper::selectRows(
            'default_extra',
            'tx_newspaper_extra_controltagzone',
            'default_extra LIKE "%' . $abstractExtraUid . '%"'
        );
        foreach($rows as $row) {
            $uids = t3lib_div::trimExplode(',', $row['default_extra']);
            if (in_array($abstractExtraUid, $uids)) {
                return true; // extra is set as default extra for an extra controltagzone
            }
        }

        return false; // no hit found ...
    }

    /// \param $mm_table typo3 mm table where extras are linked
    static function checkLinksToDeletedExtrasPagezone($mm_table) {
        $msg = '';
        $count = 0;

        // deleted flag set?
        $row = tx_newspaper::selectRows(
            $mm_table . '.*, tx_newspaper_extra.extra_table, tx_newspaper_extra.extra_uid',
            $mm_table . ' INNER JOIN tx_newspaper_extra ON ' . $mm_table . '.uid_foreign=tx_newspaper_extra.uid',
            '1',
            '',
            $mm_table . '.uid_foreign'
        );
//t3lib_div::debug(array(tx_newspaper::$query, $row));
        if (sizeof($row) > 0) {
            for($i = 0; $i < sizeof($row); $i++) {
                $msg .= 'Deleted flag set for Extra #' . $row[$i]['uid_foreign'] . '; concrete Extra ' . $row[$i]['extra_table'] . ' #' . $row[$i]['extra_uid'] . '; assigned to ' . $mm_table . ' uid_local #' . $row[$i]['uid_local'] . '<br />';
                $count++;
            }
        }

        // abstract extra deleted?
        $row = tx_newspaper::selectRows(
            $mm_table . '.*, tx_newspaper_extra.uid',
            $mm_table . ' LEFT JOIN tx_newspaper_extra ON ' . $mm_table . '.uid_foreign=tx_newspaper_extra.uid',
            'tx_newspaper_extra.uid IS NULL', // check if extra record is missing completely
            '',
            $mm_table . '.uid_foreign'
        );
//t3lib_div::debug(array(tx_newspaper::$query, $row));
        if (sizeof($row) > 0) {
            for($i = 0; $i < sizeof($row); $i++) {
                $msg .= 'Extra #' . $row[$i]['uid_foreign'] . ' is deleted; assigned to ' . $mm_table . ' uid_local #' . $row[$i]['uid_local'] . '<br />';
                $count++;
            }
        }

        if ($count > 0) {
            $msg = $count . ' problems found</br>' . $msg;
            return $msg;
        }

        return true; // no problems found
    }


    static function checkUnknownWorkflowStatus() {
        $msg = '';
        $count = 0;

        $role_ids = NP_ACTIVE_ROLE_EDITORIAL_STAFF . ',' . NP_ACTIVE_ROLE_DUTY_EDITOR . ',' . NP_ACTIVE_ROLE_POOL . ',' . NP_ACTIVE_ROLE_APPROVAL . ',' . NP_ACTIVE_ROLE_NONE;

        $row = tx_newspaper::selectRows(
            'uid,workflow_status',
            'tx_newspaper_article',
            'workflow_status NOT IN (' . $role_ids . ')',
            '',
            'uid'
        );
        if (sizeof($row) > 0) {
            for($i = 0; $i < sizeof($row); $i++) {
                $msg .= 'Article #' . $row[$i]['uid'] . ': unknown workflow_status ' . $row[$i]['workflow_status'] . '<br />';
                $count++;
            }
        } else {
            return true;
        }

        if ($count > 0) {
            $msg = $count . ' problems found</br>' . $msg;
        }

        return $msg;
    }


}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/newspaper/mod4/index.php'])    {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/newspaper/mod4/index.php']);
}




// Make instance:
$SOBE = t3lib_div::makeInstance('tx_newspaper_module4');
$SOBE->init();

// Include files?
foreach($SOBE->include_once as $INC_FILE)    include_once($INC_FILE);

$SOBE->main();
$SOBE->printContent();

?>
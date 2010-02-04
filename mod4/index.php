<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009 Helge Preuss, Oliver Schröder, Samuel Talleux <helge.preuss@gmail.com, typo3@schroederbros.de, samuel@talleux.de>
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




$LANG->includeLLFile('EXT:newspaper/mod4/locallang.xml');
require_once(PATH_t3lib . 'class.t3lib_scbase.php');
$BE_USER->modAccess($MCONF,1);	// This checks permissions and exits if the users has no permission for entry.
	// DEFAULT initialization of a module [END]



/**
 * Module 'Administration' for the 'newspaper' extension.
 *
 * @author	Helge Preuss, Oliver Schröder, Samuel Talleux <helge.preuss@gmail.com, typo3@schroederbros.de, samuel@talleux.de>
 */
class  tx_newspaper_module4 extends t3lib_SCbase {
	var $pageinfo;
	
	/// Root of the Typo3 installation for links in the BE
	const INSTALLATION_ROOT = '';

	/**
	 * Initializes the Module
	 * @return	void
	 */
	function init()	{
		global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;

		// display db errors
		$GLOBALS['TYPO3_DB']->debugOutput=true;

		parent::init();

		/*
		if (t3lib_div::_GP('clear_all_cache'))	{
			$this->include_once[] = PATH_t3lib.'class.t3lib_tcemain.php';
		}
		*/
	}

	/**
	 * Adds items to the ->MOD_MENU array. Used for the function menu selector.
	 *
	 * @return	void
	 */
	function menuConfig()	{
		global $LANG;
    	$this->MOD_MENU = Array (
			'function' => Array (
				'1' => $LANG->getLL('mod4_db_consistency_checks'),
				'2' => $LANG->getLL('mod4_record_info'),
//							'3' => $LANG->getLL('function3'),
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
	function main()	{
		global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;

tx_newspaper_Section::getAllSections(); // ???

		// Access check. The page will show only if there is a valid page and if
		// this page may be viewed by the user 
		$this->pageinfo = t3lib_BEfunc::readPageAccess($this->id,$this->perms_clause);
		$access = is_array($this->pageinfo) ? 1 : 0;
				
		if (($this->id && $access) || ($BE_USER->user['admin'] && !$this->id))	{

		    // Draw the header.
			$this->doc = t3lib_div::makeInstance('bigDoc');
			$this->doc->backPath = $BACK_PATH;
			$this->doc->form='<form action="" method="post" enctype="multipart/form-data">';

			// JavaScript
			$this->doc->JScode = '
				<script language="javascript" type="text/javascript">
					script_ended = 0;
					function jumpToUrl(URL)	{
						document.location = URL;
					}
				</script>
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
			if ($BE_USER->mayMakeShortcut())	{
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
				 * @return	void
				 */
				function printContent()	{

					$this->content.=$this->doc->endPage();
					echo $this->content;
				}

	/// Generates the module content
	function moduleContent() {
		
		global $LANG;
		
		switch((string)$this->MOD_SETTINGS['function'])	{
			case 1:
				$content = '
<style type="text/css">
body#typo3-alt-doc-php, body#typo3-db-list-php, body#typo3-mod-web-perm-index-php, body#typo3-mod-web-info-index-php, body#typo3-mod-web-func-index-php, body#typo3-mod-user-ws-index-php, body#typo3-mod-user-ws-workspaceforms-php, body#typo3-mod-php, body#typo3-mod-tools-em-index-php, body#typo3-pagetree, body#typo3-db-new-php, body#typo3-move-el-php, body#typo3-show-rechis-php, body#ext-cms-layout-db-layout-php, body#ext-tstemplate-ts-index-php, body#ext-version-cm1-index-php, body#ext-setup-mod-index-php, body#ext-tsconfig-help-mod1-index-php, body#ext-lowlevel-dbint-index-php, body#ext-lowlevel-config-index-php, body#ext-cms-layout-db-new-content-el-php {
  overflow: auto;
}
</style>
								The database is checked for inconsistent data.
								<hr />';
//								GET:'.t3lib_div::view_array($_GET).'<br />'.
//								'POST:'.t3lib_div::view_array($_POST).'<br />'.
								
								
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
			case 2:
				$content = '<div align=center><strong>' . $LANG->getLL('mod4_record_info') . '</strong></div>';
				
				$content .= self::getInfoForm();
				
				$result = t3lib_div::_GP('tx_newspaper_mod4');
				if ($result) $content .= self::getInfo($result);
				
				$this->content .= $this->doc->section($LANG->getLL('mod4_record_info'), $content, 0, 1);
			    break;
			case 3:
				$content='<div align=center><strong>Menu item #3...</strong></div>';
				$this->content.=$this->doc->section('Message #3:',$content,0,1);
				break;
		}
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
            $ret .= self::getArticleInfo($mod_post['article_id']);
        }
            if ($mod_post['extra_id']) {
            $ret .= self::getExtraInfo($mod_post['extra_id']);
        }
            if ($mod_post['articlelist_id']) {
            $ret .= self::getArticleListInfo($mod_post['articlelist_id']);
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
    		try {
    		    $default_article = $section->getDefaultArticle();
    		    $ret .= '<p>Default article:</p>' . 
    		      self::getArticleInfo($default_article->getUid()) . '<hr />';
    		} catch (tx_newspaper_IllegalUsageException $e) {
                $ret .= '<p>' . '<strong>' . 'No default article.' . '</strong>' . '</p>';
    		}
    		
    		// ... pages
    		$pages = $section->getActivePages();
            if ($pages) {
            	foreach ($pages as $page) {
            	   $ret .= self::getPageInfo($page->getUID());
            	}
            	$ret .=  '<hr />';
            } else {
            	$ret .= '<p>' . '<strong>' . 'No pages.' . '</strong>' . '</p>';
            }
            
            // ... articles. usually, lots.
            $uids = tx_newspaper::selectRows(
                'uid_local', 'tx_newspaper_article_sections_mm', 
                'uid_foreign = ' . $section->getUid(),
                '', 'uid_local ASC'
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

    static function getArticleInfo($article_id) {
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
            
            foreach ($article->getExtras() as $extra) {
            	$ret .= self::getExtraInfo($extra->getExtraUid());
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
	                        ' (' . $concrete_list->getTable() . ' ' . self::getRecordLink($concrete_list->getTable(), $concrete_list->getUid()) .
	                    '</p>';
        	} catch (tx_newspaper_DBException $e) {
                $ret .= '<p><strong>No such article list: ' . $uid . '.</strong></p>';
        		continue;
        	}
            
            $articles = $concrete_list->getArticles(10);
            if ($articles) {
            	foreach ($articles as $article) {
            		$ret .= '<p>&nbsp;&nbsp;Article' . self::getRecordLink('tx_newspaper_article', $article->getUid()) .
            		    ' - ' . $article->getAttribute('title') . '</p>';
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
            } else {
            	$ret .= '<p>&nbsp;&nbsp;No page zones.</p>';
            }
    	}
    	return $ret;
    }
    
    static protected function getPageZoneInfo($pagezone_id) {
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
		$f = array(
			array(
				'title' => 'Abstract extra: concrete extra missing',
				'class_function' => array('tx_newspaper_module4', 'checkAbstractExtraConcreteExtraMissing'),
				'param' => array()
			),
			array(
				'title' => 'Orphaned Extras: Extras which belong to no PageZone or Article',
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
		);
		return $f;
	}
	
	static function checkSectionWithMultipleButSamePageType() {
		$msg = '';
		$GLOBALS['TYPO3_DB']->debugOutput = true;
//		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
//			'section, pagetype_id, deleted, count(*) AS c',
//			'tx_newspaper_page',
//			'',
//			'section, pagetype_id, deleted',
//			'(c>1 AND deleted=0)'
//		);
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
			'tx_newspaper_extra'
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
//debug($abstract_row);
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
			'tx_newspaper_section s',
			's.articlelist NOT IN (SELECT uid FROM tx_newspaper_articlelist al WHERE al.deleted=0) AND deleted=0'
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
			'NOT uid in (SELECT uid_foreign FROM `tx_newspaper_pagezone_page_extras_mm`) 
			 AND NOT uid in (SELECT uid_foreign FROM `tx_newspaper_article_extras_mm`) 
			 AND NOT deleted 
			 AND NOT extra_table = "tx_newspaper_article" 
			 AND NOT extra_table = "tx_newspaper_pagezone_page" ',
			 '', 'uid'
		);
		
		if (!$row) return true; // no problems found
		
		$msg = sizeof($row) . ' problems found.<br />';
		for($i = 0; $i < sizeof($row); $i++) {
			$concrete = tx_newspaper::selectOneRow(
				'*', $row[$i]['extra_table'],
				'uid = ' . $row[$i]['extra_uid']
			);
			$msg .= 'Extra #' . $row[$i]['uid'] . '(concrete: ' . $row[$i]['extra_table'] . 
					' #' . $row[$i]['extra_uid'] . ')'. 
					' is not connected to either an article or a page zone:<br /> ' . 
					t3lib_div::view_array ($concrete) . '<br />';
		}
		
		return $msg;
	}
	
	/// \param $mm_table typo3 mm table where extras are linked
	static function checkLinksToDeletedExtrasPagezone($mm_table) {
		$msg = '';
		$count = 0;
			
		// deleted flag set?
		$row = tx_newspaper::selectRows(
			'mm.*',
			$mm_table . ' mm INNER JOIN tx_newspaper_extra e ON mm.uid_foreign=e.uid', 
			'e.deleted=1',
			'',
			'mm.uid_foreign'
		);
		if (sizeof($row) > 0) { 
			for($i = 0; $i < sizeof($row); $i++) {
				$msg .= 'Deleted flag set for Extra #' . $row[$i]['uid'] . '; concrete Extra ' . $row[$i]['extra_table'] . ' #' . $row[$i]['extra_uid'] . '; assigned to ' . $mm_table . ' #' . $row[$i]['uid_local'] . '<br />';
				$count++;
			}
		}
		
		// abstract extra deleted?
		$row = tx_newspaper::selectRows(
			'mm.*',
			$mm_table . ' mm LEFT JOIN tx_newspaper_extra e ON mm.uid_foreign=e.uid AND e.uid<=0',
			'1',
			'',
			'mm.uid_foreign'
		);
		if (sizeof($row) > 0) { 
			for($i = 0; $i < sizeof($row); $i++) {
				$msg .= 'Extra #' . $row[$i]['uid_foreign'] . ' is deleted; assigned to ' . $mm_table . ' #' . $row[$i]['uid_local'] . '<br />';
				$count++;
			}
		}		

		if ($count > 0) {
			$msg = '<strong>' . $count . ' problems found</strong></br>' . $msg;
		}

		return $msg;
	}

}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/newspaper/mod4/index.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/newspaper/mod4/index.php']);
}




// Make instance:
$SOBE = t3lib_div::makeInstance('tx_newspaper_module4');
$SOBE->init();

// Include files?
foreach($SOBE->include_once as $INC_FILE)	include_once($INC_FILE);

$SOBE->main();
$SOBE->printContent();

?>
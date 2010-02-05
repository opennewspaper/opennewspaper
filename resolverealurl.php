<?php
/**
 *  \file resolverealurl.php
 * 
 *  This file is part of the TYPO3 extension "newspaper".
 * 
 *  Copyright notice
 *
 *  (c) 2008 Helge Preuss, Oliver Schroeder, Samuel Talleux <helge.preuss@gmail.com, oliver@schroederbros.de, samuel@talleux.de>
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
 *  
 *  \author Helge Preuss <helge.preuss@gmail.com>
 *  \date Jan 18, 2010
 */


//
// the following is copied and adapted from index.php
//

error_reporting (E_ALL ^ E_NOTICE);                                     // stfu

define('PATH_thisScript',
    str_replace('//','/', 
        str_replace('\\','/', 
            (php_sapi_name()=='cgi' || php_sapi_name()=='isapi' || php_sapi_name()=='cgi-fcgi')&&
             ($_SERVER['ORIG_PATH_TRANSLATED']? 
              $_SERVER['ORIG_PATH_TRANSLATED']: 
                $_SERVER['PATH_TRANSLATED'])? 
                ($_SERVER['ORIG_PATH_TRANSLATED']? $_SERVER['ORIG_PATH_TRANSLATED']: $_SERVER['PATH_TRANSLATED']):
                  $_SERVER['SCRIPT_FILENAME']
        )
    )
);

define('PATH_site', tx_newspaper_ResolveRealURL::base_path . '/');

if (@is_dir(PATH_site.'typo3/sysext/cms/tslib/')) {
    define('PATH_tslib', PATH_site.'typo3/sysext/cms/tslib/');
} elseif (@is_dir(PATH_site.'tslib/')) {
    define('PATH_tslib', PATH_site.'tslib/');
}
if (PATH_tslib=='') {
    die('Cannot find tslib/. Please set path by defining $configured_tslib_path in '.basename(PATH_thisScript).'.');
}

//
// the following is copied and adapted from typo3/sysext/cms/tslib/index_ts.php
//

define('TYPO3_MODE','FE');

if (!defined('PATH_t3lib')) define('PATH_t3lib', PATH_site.'t3lib/');

define('PATH_typo3conf', PATH_site.'typo3conf/');
if (!@is_dir(PATH_typo3conf))   die('Cannot find configuration. This file is probably executed from the wrong location.');

require_once(PATH_t3lib.'class.t3lib_div.php');
require_once(PATH_t3lib.'class.t3lib_extmgm.php');

require_once(PATH_t3lib.'config_default.php');
// the name of the TYPO3 database is stored in this constant. Here the inclusion of the config-file is verified by checking if this var is set.
if (!defined ('TYPO3_db'))  die ('The configuration file was not included.');   

require_once(PATH_t3lib.'class.t3lib_db.php');
$TYPO3_DB = t3lib_div::makeInstance('t3lib_DB');
$TYPO3_DB->connectDB();

if (!t3lib_extMgm::isLoaded('newspaper')) die('newspaper not loaded.');

// needed for tslib_cObj::typolink()
require_once(PATH_tslib.'class.tslib_content.php');
require_once(PATH_tslib.'class.tslib_fe.php');

require_once(PATH_t3lib.'class.t3lib_timetrack.php');
$TT = new t3lib_timeTrack;  //  $TSFE needs this.
$TT->start();

$temp_TSFEclassName = t3lib_div::makeInstanceClassName('tslib_fe');
$TSFE = new $temp_TSFEclassName(
        $TYPO3_CONF_VARS,
        tx_newspaper_ResolveRealURL::article_typo3_page,
        t3lib_div::_GP('type'),
        t3lib_div::_GP('no_cache'),
        t3lib_div::_GP('cHash'),
        t3lib_div::_GP('jumpurl'),
        t3lib_div::_GP('MP'),
        t3lib_div::_GP('RDCT')
    );
    
$TSFE->connectToDB();
$TSFE->initTemplate();
$TSFE->getConfigArray();

/// Resolves a link to an old taz article and loads the article in the newspaper extension.
/** \todo long description
 */
class tx_newspaper_ResolveRealURL {

	/// SQL table containing the resolution parameters.
	const uniquealias_table = 'tx_newspaper_uniqalias';
	/// Typo3 page used to display resolved articles.
	const article_typo3_page = 33;
	
	const post_key = '1';

	const base_path = '/www/onlinetaz/branches/taz 2.0/helge';
	
	static $prefixes = array('1', '4');
	
	public function __construct() {
		$this->uri = $_SERVER['REQUEST_URI'];
	}
	
	public function resolve() {
		// uri will be of the form /[14]/.*/1/article-alias[...]
		$segments = explode('/', $this->uri);
		
        array_shift($segments);             // remove leading null string
		$first = array_shift($segments);    // get first path segment
		
		// should never happen if mod_rewrite and resolverealurl.php are configured in sync
		if (!in_array($first, self::$prefixes)) {
			// to do: show the original URI
		    self::error('Path ' . $this->uri . ' does not start with ' . implode(' or ', self::$prefixes));
		}
		
		$post_index = array_search(self::post_key, $segments);
		if ($post_index === false) {
			// URL does not lead to an article.
			// to do: handle this.
			self::error(self::post_key . ' not found!');
		}
		
		$article_alias = $segments[$post_index+1];
	
		$query = $GLOBALS['TYPO3_DB']->SELECTquery(
		    'field_id, value_id', self::uniquealias_table,
		    'value_alias = \'' . $article_alias .'\''
		);
		echo $query . '<br>';
        $res = $GLOBALS['TYPO3_DB']->sql_query($query);
        if (!$res) self::error('article alias ' . $article_alias . ' not found');

        $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
        if (!$row) self::error('article alias ' . $article_alias . ' not found');
        
        print('article alias: ' . $article_alias . ': ' . print_r($row, 1));

        /*
        $link = $this->fake_typolink(
            array(
                'id' => self::article_typo3_page,
                tx_newspaper::article_get_parameter => intval($row['value_id'])
        ));
        */
        $link = tx_newspaper::typolink_url(
            array(
                'id' => self::article_typo3_page,
                tx_newspaper::article_get_parameter => intval($row['value_id'])
        ));
        #        print_r($GLOBALS['TSFE']);
		die($link);		
	}
	
	private static function error($msg) {
		// todo handle errors.
		die($msg);
	}
	
	private function fake_typolink(array $params = array()) {

		// generate tslib_cObj::typolink() parameters from the saner parameters to this function		
        foreach ($params as $key => $param) {
            if (is_array($param)) {
                foreach ($param as $subkey => $value) {
                    $params[$key.'['.$subkey.']'] = $value;
                }
                unset($params[$key]);
            }
        }

        //  set TypoScript config array
        $temp_conf = array();

        if ($params['id']) $temp_conf['parameter'] = $params['id'];
        else $temp_conf['parameter.']['current'] = 1;
        unset($params['id']);
		
        $no_cache = false;
        $sep = '&';
        if (sizeof($params) > 0) {
            foreach ($params as $key => $value) {
                if ($key == 'no_cache' && $value != 0) $no_cache = true;
                if ($key != 'cHash') {
                    $temp_conf['additionalParams'] .= "$sep$key=$value";
                    $sep = '&';
                }
            }
        }
        if (!$no_cache) $temp_conf['useCacheHash'] = 1;

        
        $linktext = "";
        $conf = $temp_conf;
		
        $LD = array();
        $finalTagParts = array();
        $finalTagParts['aTagParams'] = $this->getATagParams($conf);

        $link_param = trim($this->stdWrap($conf['parameter'],$conf['parameter.']));

        $sectionMark = trim($this->stdWrap($conf['section'],$conf['section.']));
        $sectionMark = $sectionMark ? (t3lib_div::testInt($sectionMark)?'#c':'#').$sectionMark : '';
        $initP = '?id='.$GLOBALS['TSFE']->id.'&type='.$GLOBALS['TSFE']->type;
        $this->lastTypoLinkUrl = '';
        $this->lastTypoLinkTarget = '';
        if ($link_param) {
                $enableLinksAcrossDomains = $GLOBALS['TSFE']->config['config']['typolinkEnableLinksAcrossDomains'];
                $link_paramA = t3lib_div::unQuoteFilenames($link_param,true);

                // Check for link-handler keyword:
                list($linkHandlerKeyword,$linkHandlerValue) = explode(':',trim($link_paramA[0]),2);
                if ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_content.php']['typolinkLinkHandler'][$linkHandlerKeyword] && strcmp($linkHandlerValue, '')) {
                $linkHandlerObj = &t3lib_div::getUserObj($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_content.php']['typolinkLinkHandler'][$linkHandlerKeyword]);

                if(method_exists($linkHandlerObj, 'main')) {
                        return $linkHandlerObj->main($linktxt, $conf, $linkHandlerKeyword, $linkHandlerValue, $link_param, $this);
                }
                }

                $link_param = trim($link_paramA[0]);    // Link parameter value
                $linkClass = trim($link_paramA[2]);             // Link class
                if ($linkClass=='-')    $linkClass = '';        // The '-' character means 'no class'. Necessary in order to specify a title as fourth parameter without setting the target or class!
                $forceTarget = trim($link_paramA[1]);   // Target value
                $forceTitle = trim($link_paramA[3]);    // Title value
                if ($forceTarget=='-')  $forceTarget = '';      // The '-' character means 'no target'. Necessary in order to specify a class as third parameter without setting the target!
                // Check, if the target is coded as a JS open window link:
                $JSwindowParts = array();
                $JSwindowParams = '';
                $onClick = '';
                if ($forceTarget && ereg('^([0-9]+)x([0-9]+)(:(.*)|.*)$',$forceTarget,$JSwindowParts))  {
                        // Take all pre-configured and inserted parameters and compile parameter list, including width+height:
                $JSwindow_tempParamsArr = t3lib_div::trimExplode(',',strtolower($conf['JSwindow_params'].','.$JSwindowParts[4]),1);
                $JSwindow_paramsArr=array();
                foreach($JSwindow_tempParamsArr as $JSv)        {
                        list($JSp,$JSv) = explode('=',$JSv);
                        $JSwindow_paramsArr[$JSp]=$JSp.'='.$JSv;
                }
                        // Add width/height:
                $JSwindow_paramsArr['width']='width='.$JSwindowParts[1];
                $JSwindow_paramsArr['height']='height='.$JSwindowParts[2];
                        // Imploding into string:
                $JSwindowParams = implode(',',$JSwindow_paramsArr);
                $forceTarget = '';      // Resetting the target since we will use onClick.
                }

                // Internal target:
                $target = isset($conf['target']) ? $conf['target'] : $GLOBALS['TSFE']->intTarget;
                if ($conf['target.'])   {$target=$this->stdWrap($target, $conf['target.']);}

                // Title tag
                $title = $conf['title'];
                if ($conf['title.'])    {$title=$this->stdWrap($title, $conf['title.']);}

                // Parse URL:
                $pU = parse_url($link_param);

                       // Detecting kind of link:
                if(strstr($link_param,'@') && (!$pU['scheme'] || $pU['scheme']=='mailto'))      {               // If it's a mail address:
                $link_param = eregi_replace('^mailto:','',$link_param);
                list($this->lastTypoLinkUrl,$linktxt) = $this->getMailTo($link_param,$linktxt,$initP);
                $finalTagParts['url']=$this->lastTypoLinkUrl;
                $finalTagParts['TYPE']='mailto';
                } else {
                $isLocalFile=0;
                $fileChar=intval(strpos($link_param, '/'));
                $urlChar=intval(strpos($link_param, '.'));

                // Firsts, test if $link_param is numeric and page with such id exists. If yes, do not attempt to link to file
                if (!t3lib_div::testInt($link_param) || count($GLOBALS['TSFE']->sys_page->getPage_noCheck($link_param)) == 0) {
                        // Detects if a file is found in site-root (or is a 'virtual' simulateStaticDocument file!) and if so it will be treated like a normal file.
                        list($rootFileDat) = explode('?',rawurldecode($link_param));
                        $containsSlash = strstr($rootFileDat,'/');
                        $rFD_fI = pathinfo($rootFileDat);
                        if (trim($rootFileDat) && !$containsSlash && (@is_file(PATH_site.$rootFileDat) || t3lib_div::inList('php,html,htm',strtolower($rFD_fI['extension']))))  {
                        $isLocalFile = 1;
                        } elseif ($containsSlash)       {
                        $isLocalFile = 2;               // Adding this so realurl directories are linked right (non-existing).
                        }
                }

                if($pU['scheme'] || ($isLocalFile!=1 && $urlChar && (!$containsSlash || $urlChar<$fileChar)))   {       // url (external): If doubleSlash or if a '.' comes before a '/'.
                        $target = isset($conf['extTarget']) ? $conf['extTarget'] : $GLOBALS['TSFE']->extTarget;
                        if ($conf['extTarget.'])        {$target = $this->stdWrap($target, $conf['extTarget.']);}
                        if ($forceTarget)       {$target=$forceTarget;}
                        if ($linktxt=='') $linktxt = $link_param;
                        if (!$pU['scheme'])     {$scheme='http://';} else {$scheme='';}
                        if ($GLOBALS['TSFE']->config['config']['jumpurl_enable'])       {
                        $this->lastTypoLinkUrl = $GLOBALS['TSFE']->absRefPrefix.$GLOBALS['TSFE']->config['mainScript'].$initP.'&jumpurl='.rawurlencode($scheme.$link_param).$GLOBALS['TSFE']->getMethodUrlIdToken;
                        } else {
                        $this->lastTypoLinkUrl = $scheme.$link_param;
                        }
                        $this->lastTypoLinkTarget = $target;
                        $finalTagParts['url']=$this->lastTypoLinkUrl;
                        $finalTagParts['targetParams'] = $target ? ' target="'.$target.'"' : '';
                        $finalTagParts['TYPE']='url';
                } elseif ($containsSlash || $isLocalFile)       {       // file (internal)
                        $splitLinkParam = explode('?', $link_param);
                        if (@file_exists(rawurldecode($splitLinkParam[0])) || $isLocalFile)     {
                        if ($linktxt=='') $linktxt = rawurldecode($link_param);
                        if ($GLOBALS['TSFE']->config['config']['jumpurl_enable'])       {
                                $this->lastTypoLinkUrl = $GLOBALS['TSFE']->absRefPrefix.$GLOBALS['TSFE']->config['mainScript'].$initP.'&jumpurl='.rawurlencode($link_param).$GLOBALS['TSFE']->getMethodUrlIdToken;
                        } else {
                                $this->lastTypoLinkUrl = $GLOBALS['TSFE']->absRefPrefix.$link_param;
                        }
                        if ($forceTarget)       {$target=$forceTarget;}
                        $this->lastTypoLinkTarget = $target;

                        $finalTagParts['url'] = $this->lastTypoLinkUrl;
                        $finalTagParts['targetParams'] = $target ? ' target="'.$target.'"' : '';
                        $finalTagParts['TYPE'] = 'file';
                        } else {
                        $GLOBALS['TT']->setTSlogMessage("typolink(): File '".$splitLinkParam[0]."' did not exist, so '".$linktxt."' was not linked.",1);
                        return $linktxt;
                        }
                } else {        // integer or alias (alias is without slashes or periods or commas, that is 'nospace,alphanum_x,lower,unique' according to definition in $TCA!)
                        if ($conf['no_cache.']) $conf['no_cache']=$this->stdWrap($conf['no_cache'], $conf['no_cache.']);
                        $link_params_parts=explode('#',$link_param);
                        $link_param = trim($link_params_parts[0]);              // Link-data del
                        if (!strcmp($link_param,''))    {$link_param=$GLOBALS['TSFE']->id;}     // If no id or alias is given
                        if ($link_params_parts[1] && !$sectionMark)     {
                        $sectionMark = trim($link_params_parts[1]);
                        $sectionMark = (t3lib_div::testInt($sectionMark)?'#c':'#').$sectionMark;
                        }
                        // Splitting the parameter by ',' and if the array counts more than 1 element it's a id/type/? pair
                        unset($theTypeP);
                        $pairParts = t3lib_div::trimExplode(',',$link_param);
                        if (count($pairParts)>1)        {
                        $link_param = $pairParts[0];
                        $theTypeP = isset($pairParts[1]) ? $pairParts[1] : 0;           // Overruling 'type'
                        $conf['additionalParams'].= isset($pairParts[2]) ? $pairParts[2] : '';
                        }
                        // Checking if the id-parameter is an alias.
                       if (!t3lib_div::testInt($link_param))   {
                        $link_param = $GLOBALS['TSFE']->sys_page->getPageIdFromAlias($link_param);
                        }

                        // Link to page even if access is missing?
                        if (strlen($conf['linkAccessRestrictedPages'])) {
                        $disableGroupAccessCheck = ($conf['linkAccessRestrictedPages'] ? TRUE : FALSE);
                        } else {
                        $disableGroupAccessCheck = ($GLOBALS['TSFE']->config['config']['typolinkLinkAccessRestrictedPages'] ? TRUE : FALSE);
                        }

                        // Looking up the page record to verify its existence:
                        $page = $GLOBALS['TSFE']->sys_page->getPage($link_param,$disableGroupAccessCheck);

                        if (count($page))       {
                                // MointPoints, look for closest MPvar:
                        $MPvarAcc = array();
                        if (!$GLOBALS['TSFE']->config['config']['MP_disableTypolinkClosestMPvalue'])    {
                                $temp_MP = $this->getClosestMPvalueForPage($page['uid'],TRUE);
                                if ($temp_MP)   $MPvarAcc['closest'] = $temp_MP;
                        }
                                // Look for overlay Mount Point:
                        $mount_info = $GLOBALS['TSFE']->sys_page->getMountPointInfo($page['uid'], $page);
                        if (is_array($mount_info) && $mount_info['overlay'])    {
                                $page = $GLOBALS['TSFE']->sys_page->getPage($mount_info['mount_pid'],$disableGroupAccessCheck);
                                if (!count($page))      {
                                $GLOBALS['TT']->setTSlogMessage("typolink(): Mount point '".$mount_info['mount_pid']."' was not available, so '".$linktxt."' was not linked.",1);
                                return $linktxt;
                                }
                                $MPvarAcc['re-map'] = $mount_info['MPvar'];
                        }

                                // Setting title if blank value to link:
                        if ($linktxt=='') $linktxt = $page['title'];

                                // Query Params:
                        $addQueryParams = $conf['addQueryString'] ? $this->getQueryArguments($conf['addQueryString.']) : '';
                        $addQueryParams .= trim($this->stdWrap($conf['additionalParams'],$conf['additionalParams.']));
                        if (substr($addQueryParams,0,1)!='&')           {
                                $addQueryParams = '';
                        } elseif ($conf['useCacheHash']) {      // cache hashing:
                                $pA = t3lib_div::cHashParams($addQueryParams.$GLOBALS['TSFE']->linkVars);       // Added '.$this->linkVars' dec 2003: The need for adding the linkVars is that they will be included in the link,but not the cHash. Thus the linkVars will always be the problem that prevents the cHash from working. I cannot see what negative implications in terms of incompatibilities this could bring, but for now I hope there are none. So here we go... (- kasper)
                                $addQueryParams.= '&cHash='.t3lib_div::shortMD5(serialize($pA));
                        }

                        $tCR_domain = '';
                        // Mount pages are always local and never link to another domain
                        if (count($MPvarAcc))   {
                                // Add "&MP" var:
                                $addQueryParams.= '&MP='.rawurlencode(implode(',',$MPvarAcc));
                        }
                        elseif (strpos($addQueryParams, '&MP=') === false && $GLOBALS['TSFE']->config['config']['typolinkCheckRootline']) {

                                // We do not come here if additionalParams had '&MP='. This happens when typoLink is called from
                                // menu. Mount points always work in the content of the current domain and we must not change
                                // domain if MP variables exist.

                                // If we link across domains and page is free type shortcut, we must resolve the shortcut first!
                                // If we do not do it, TYPO3 will fail to (1) link proper page in RealURL/CoolURI because
                                // they return relative links and (2) show proper page if no RealURL/CoolURI exists when link is clicked
                                if ($enableLinksAcrossDomains && $page['doktype'] == 4 && $page['shortcut_mode'] == 0) {
                                $page2 = $page; // Save in case of broken destination or endless loop
                                $maxLoopCount = 20;     // Same as in RealURL, seems enough
                                while ($maxLoopCount && is_array($page) && $page['doktype'] == 4 && $page['shortcut_mode'] == 0) {
                                        $page = $GLOBALS['TSFE']->sys_page->getPage($page['shortcut'], $disableGroupAccessCheck);
                                        $maxLoopCount--;
                                }
                                if (count($page) == 0 || $maxLoopCount == 0) {
                                        // We revert if shortcut is broken or maximum number of loops is exceeded (indicates endless loop)
                                        $page = $page2;
                                }
                                }

                                // This checks if the linked id is in the rootline of this site and if not it will find the domain for that ID and prefix it:
                               $tCR_rootline = $GLOBALS['TSFE']->sys_page->getRootLine($page['uid']);  // Gets rootline of linked-to page
                                $tCR_flag = 0;
                                foreach ($tCR_rootline as $tCR_data)    {
                                if ($tCR_data['uid'] == $GLOBALS['TSFE']->tmpl->rootLine[0]['uid'])     {
                                        $tCR_flag = 1;  // OK, it was in rootline!
                                        break;
                                }
                                if ($tCR_data['is_siteroot']) {
                                        // Possibly subdomain inside main domain. In any case we must stop now because site root is reached.
                                        break;
                                }
                                }
                                if (!$tCR_flag) {
                                foreach ($tCR_rootline as $tCR_data)    {
                                        $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('domainName', 'sys_domain', 'pid='.intval($tCR_data['uid']).' AND redirectTo=\'\''.$this->enableFields('sys_domain'), '', 'sorting')
;
                                        $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
                                        $GLOBALS['TYPO3_DB']->sql_free_result($res);
                                        if ($row)       {
                                        $tCR_domain = preg_replace('/\/$/','',$row['domainName']);
                                        break;
                                        }
                                }
                                }
                        }
                                // If other domain, overwrite
                        if (strlen($tCR_domain) && !$enableLinksAcrossDomains) {
                                $target = isset($conf['extTarget']) ? $conf['extTarget'] : $GLOBALS['TSFE']->extTarget;
                                if ($conf['extTarget.']) {
                                $target = $this->stdWrap($target, $conf['extTarget.']);
                                }
                                if ($forceTarget) {
                                $target = $forceTarget;
                                }
                                $LD['target'] = $target;
                                $this->lastTypoLinkUrl = $this->URLqMark('http://'.$tCR_domain.'/index.php?id='.$page['uid'],$addQueryParams).$sectionMark;
                        } else {        // Internal link:
                                if ($forceTarget) {
                                $target = $forceTarget;
                                }
                                $LD = $GLOBALS['TSFE']->tmpl->linkData($page,$target,$conf['no_cache'],'','',$addQueryParams,$theTypeP);
                                if (strlen($tCR_domain)) {
                                // We will add domain only if URL does not have it already.

                                if ($enableLinksAcrossDomains) {
                                        // Get rid of the absRefPrefix if necessary. absRefPrefix is applicable only
                                        // to the current web site. If we have domain here it means we link across
                                        // domains. absRefPrefix can contain domain name, which will screw up
                                        // the link to the external domain.
                                        $prefixLength = strlen($GLOBALS['TSFE']->config['config']['absRefPrefix']);
                                        if (substr($LD['totalURL'], 0, $prefixLength) == $GLOBALS['TSFE']->config['config']['absRefPrefix']) {
                                        $LD['totalURL'] = substr($LD['totalURL'], $prefixLength);
                                        }
                                }
                                $urlParts = parse_url($LD['totalURL']);
                                if ($urlParts['host'] == '') {
                                        $LD['totalURL'] = 'http://' . $tCR_domain . ($LD['totalURL']{0} == '/' ? '' : '/') . $LD['totalURL'];
                                }
                                }
                                $this->lastTypoLinkUrl = $this->URLqMark($LD['totalURL'],'').$sectionMark;
                        }

                        $this->lastTypoLinkTarget = $LD['target'];
                        $targetPart = $LD['target'] ? ' target="'.$LD['target'].'"' : '';

                                // If sectionMark is set, there is no baseURL AND the current page is the page the link is to, check if there are any additional parameters and is not, drop the url.
                               if ($sectionMark && !trim($addQueryParams) && $page['uid']==$GLOBALS['TSFE']->id && !$GLOBALS['TSFE']->config['config']['baseURL'])     {
                                list(,$URLparams) = explode('?',$this->lastTypoLinkUrl);
                                list($URLparams) = explode('#',$URLparams);
                                parse_str ($URLparams.$LD['orig_type'], $URLparamsArray);
                                if (intval($URLparamsArray['type'])==$GLOBALS['TSFE']->type)    {       // type nums must match as well as page ids
                                unset($URLparamsArray['id']);
                                unset($URLparamsArray['type']);
                                if (!count($URLparamsArray))    {       // If there are no parameters left.... set the new url.
                                        $this->lastTypoLinkUrl = $sectionMark;
                                }
                                }
                        }

                                // If link is to a access restricted page which should be redirected, then find new URL:
                        if ($GLOBALS['TSFE']->config['config']['typolinkLinkAccessRestrictedPages'] &&
                                $GLOBALS['TSFE']->config['config']['typolinkLinkAccessRestrictedPages']!=='NONE' &&
                                !$GLOBALS['TSFE']->checkPageGroupAccess($page)) {
                                        $thePage = $GLOBALS['TSFE']->sys_page->getPage($GLOBALS['TSFE']->config['config']['typolinkLinkAccessRestrictedPages']);

                                        $addParams = $GLOBALS['TSFE']->config['config']['typolinkLinkAccessRestrictedPages_addParams'];
                                        $addParams = str_replace('###RETURN_URL###',rawurlencode($this->lastTypoLinkUrl),$addParams);
                                        $addParams = str_replace('###PAGE_ID###',$page['uid'],$addParams);
                                        $LD = $GLOBALS['TSFE']->tmpl->linkData($thePage,$target,'','','',$addParams,$theTypeP);
                                        $this->lastTypoLinkUrl = $this->URLqMark($LD['totalURL'],'');
                        }

                                // Rendering the tag.
                        $finalTagParts['url']=$this->lastTypoLinkUrl;
                        $finalTagParts['targetParams']=$targetPart;
                        $finalTagParts['TYPE']='page';
                        } else {
                        $GLOBALS['TT']->setTSlogMessage("typolink(): Page id '".$link_param."' was not found, so '".$linktxt."' was not linked.",1);
                        return $linktxt;
                        }
                }
                }

                $this->lastTypoLinkLD = $LD;

                if ($forceTitle) {
                $title=$forceTitle;
                }

                if ($JSwindowParams) {

                        // Create TARGET-attribute only if the right doctype is used
                if (!t3lib_div::inList('xhtml_strict,xhtml_11,xhtml_2', $GLOBALS['TSFE']->xhtmlDoctype))        {
                        $target = ' target="FEopenLink"';
                } else {
                        $target = '';
                }

                $onClick="vHWin=window.open('".$GLOBALS['TSFE']->baseUrlWrap($finalTagParts['url'])."','FEopenLink','".$JSwindowParams."');vHWin.focus();return false;";
                $res = '<a href="'.htmlspecialchars($finalTagParts['url']).'"'. $target .' onclick="'.htmlspecialchars($onClick).'"'.($title?' title="'.$title.'"':'').($linkClass?' class="'.$linkClass.'"':'').$finalTagParts['aTagParams'].'>';
                } else {
                if ($GLOBALS['TSFE']->spamProtectEmailAddresses === 'ascii' && $finalTagParts['TYPE'] === 'mailto') {
                        $res = '<a href="'.$finalTagParts['url'].'"'.($title?' title="'.$title.'"':'').$finalTagParts['targetParams'].($linkClass?' class="'.$linkClass.'"':'').$finalTagParts['aTagParams'].'>';
                } else {
                        $res = '<a href="'.htmlspecialchars($finalTagParts['url']).'"'.($title?' title="'.$title.'"':'').$finalTagParts['targetParams'].($linkClass?' class="'.$linkClass.'"':'').$finalTagParts['aTagParams'].'>';
                }
                }

                // Call user function:
                if ($conf['userFunc'])  {
                $finalTagParts['TAG']=$res;
                $res = $this->callUserFunction($conf['userFunc'],$conf['userFunc.'],$finalTagParts);
                }

                // Hook: Call post processing function for link rendering:
                if (isset($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_content.php']['typoLink_PostProc']) && is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_content.php']['typoLink_PostProc'])) {
                $_params = array(
                        'conf' => &$conf,
                        'linktxt' => &$linktxt,
                        'finalTag' => &$res,
                        'finalTagParts' => &$finalTagParts,
                );
                foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_content.php']['typoLink_PostProc'] as $_funcRef) {
                        t3lib_div::callUserFunction($_funcRef, $_params, $this);
                }
                }

                // If flag "returnLastTypoLinkUrl" set, then just return the latest URL made:
                if ($conf['returnLast'])        {
                switch($conf['returnLast'])     {
                        case 'url':
                        return $this->lastTypoLinkUrl;
                        break;
                        case 'target':
                        return $this->lastTypoLinkTarget;
                        break;
                }
                }

                if ($conf['ATagBeforeWrap'])    {
                return $res.$this->wrap($linktxt, $conf['wrap']).'</a>';
                } else {
                return $this->wrap($res.$linktxt.'</a>', $conf['wrap']);
                }
        } else {
                return $linktxt;
        }
                
	}
	
	private $uri;
}

$resolver = new tx_newspaper_ResolveRealURL();

$resolver->resolve();

?>

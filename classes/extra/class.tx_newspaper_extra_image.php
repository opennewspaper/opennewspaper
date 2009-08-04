<?php

require_once(PATH_typo3conf . 'ext/newspaper/classes/class.tx_newspaper_extra.php');

class tx_newspaper_Extra_Image extends tx_newspaper_Extra {

	/// Create a tx_newspaper_Extra_Image
	/** The following parameters must be read from TSConfig.
	 *  /code
	 *  newspaper.image.basepath
	 *  # The following is not yet well thought out.
	 *  newspaper.image.size....
	 *                       article....
	 *                               default
	 *                               aufmacher
	 *                       teaser....
	 *                       adr....
	 * /endcode
	 */
	public function __construct($uid = 0) {
		if ($uid) {
			parent::__construct($uid); 
		}
//		$this->smarty = new tx_newspaper_Smarty();
/*	wtf?! look at that:
Fatal error: Call to a member function exec_SELECTquery() on a non-object in 
/var/lib/httpd/onlinetaz/typo3_src-4.2.6/t3lib/class.t3lib_befunc.php on line 1238
 */
		$TSConfig = t3lib_BEfunc::getPagesTSconfig($GLOBALS['TSFE']->page['uid']);
		$this->basepath = $TSConfig['newspaper.']['image']['basepath'];
		$this->sizes =  $TSConfig['newspaper.']['image']['size'];
	}
	
	public function __toString() {
		$ret = '';
		try{
			$ret .= 'Extra: UID ' . $this->getExtraUid() . ', Image: UID ' . $this->getUid();
			$ret .= ' (Title: ' . $this->getAttribute('title') . ')';
		} catch (Exception $e) {  }
		return $ret;	
	}
	
	/** Just a quick hack to see anything
	 */
	public function render($template_set = '') {
		$this->prepare_render($template_set);
		
		$this->smarty->assign('title', $this->getAttribute('title'));
		$this->smarty->assign('image', $this->getAttribute('image'));
		$this->smarty->assign('caption', $this->getAttribute('caption'));
		return $this->smarty->fetch($this);
	}

	/// A short description that makes an Extra uniquely identifiable in the BE
	public function getDescription() {
		return $this->getAttribute('title') . ' (#' . $this->getUid() . ')';
	}

//TODO: getLLL
	public function getTitle() {
		return 'Image';
	}

// title for module
	public static function getModuleName() {
		return 'np_image';
	}
	
	public static function dependsOnArticle() { return false; }
	
	/// Damn the Typo3 documentation, I was unable to find authoritative docs.
	/** Here's what i could deduce.
	 *  \param $table The table of the record that is to be stored
	 *  \param $id The UID of the record that is to be stored
	 *  \param $fieldArray The values to be stored, as a reference so they can be changed
	 */
	public static function processDatamap_postProcessFieldArray(
		$status, $table, $id, &$fieldArray, $that
	) {
		/*  in a static function, there is no object to call. prior to PHP 5.3,
		 *  there is no way to find out which class we are in.
		 */
		if ((PHP_MAJOR_VERSION == 5 && PHP_MINOR_VERSION >= 3) || (PHP_MAJOR_VERSION > 5)) {
			$extra_table = strtolower(get_called_class());
		} else {
			$extra_table = 'tx_newspaper_extra_image';
		}
		if ($table != $extra_table) return;
		
		t3lib_div::devlog('image save hook', 'newspaper', 0, 
			array('status' => $status, 'table' => $table, 'id' => $id, 
				  'fieldArray' => $fieldArray, 'that' => $that));
	}
	
	////////////////////////////////////////////////////////////////////////////
	
	protected function resizeImages($fieldArray) {
		foreach (
			array(
				'article_mainteaser_image',
				'plugin_photo_1_img',
				'plugin_photo_2_img')
			as $key) {
			if ($fieldArray[$key]) $this->resizeImg($fieldArray[$key]);
		}
	}


	/** if image needs resizing, do it (using imagemagick)					  */
    protected function resizeImg($img) {
		foreach (self::$imgSizes as $key => $dimension) {
	    	if ($this->imgIsResized($img, $key)) continue;
			taz_base::resizeImage(self::$imgSizes[$key]['width'], self::$imgSizes[$key]['height'],
							  self::$basepath.self::$baseurl.$img,
							  self::$basepath.self::$baseurl.$this->imgResizedName($img, $key));
		}
    }

	/** copy $basedir to $targetPath on $targetHost	*/
	protected function rsync($basedir, $targetHost, $targetPath) {
		$command = "rsync ".self::$rsyncOpts." \"$basedir\" \"$targetHost:$targetPath\" 2>&1"; // "..." -> space in path
		$output = array();
		$return = 0;
		exec($command, $output, $return);
		exec("date >> ".self::$rsyncLog);
        $f = fopen(self::$rsyncLog, "a+");
        fwrite ($f, "$basedir\n");
        fwrite ($f, print_r($output,1));
        fclose($f);

        if ($return) {
			taz_base::degub("Achtung: das Uebertragen der hochgeladenen Bilder auf den Live-Server ist fehlgeschlagen!");
			taz_base::degub("Es kann sein, dass Bilder fuer diesen Artikel nicht angekommen sind. Vergewissere Dich bitte, bevor Du den Artikel sichtbar stellst.");
			taz_base::degub("Im folgenden etwas Debug-Information, die Dir vielleicht weiterhilft, und die du an ".self::$devAddress." schicken kannst, um das Problem zu beheben.");
			taz_base::degub($command,0);
			taz_base::degub($output,0);
			taz_base::degub($return,0);
		}

	}


    /** if image needs resizing, do it (using imagemagick)					  */
    static function resizeImage($width, $height, $source, $target) {
    	if (!file_exists(dirname($target))) {
    		if(!mkdir(dirname($target))) {
    			die('Couldn\'t mkdir('.dirname($target).')');
    		}
    	}
    	if (!file_exists($target)) {
    		system(self::$convert.' '.self::$convertoptions.
				' -geometry '.$width./*'x'.$height.*/' '.$source.' '.$target);
    	}
    }

    /** @return name of resized image										  */
    protected static function imageResizedName($img, $name) {
    	$nameparts = self::splitExtension($img);
    	return '../'.RESIZED_MARKER.$name.'/'.$img;
    }

    /** @return whether resized image already exists					 	  */
    protected function imgIsResized($img, $key) {
    	return file_exists($this->imgResizedName($img, $key));
    }

    /** @return name of resized image										  */
    protected function imgResizedName($img, $key) {
    	return taz_base::imageResizedName($img, self::$imgSizes[$key]['name']);
    }

    /** where uploaded images are stored */
    static protected $baseurl = '/uploads/tx_hptazarticle/';
    static protected $basepath = BASEPATH;

    /** path to convert(1) */
    static protected $convert = '/usr/bin/convert';
    /** path to montage(1) */
    static protected $montage = '/usr/bin/montage';

	/** options to convert */
	static protected $convertoptions = '-quality 90';
//    static protected $convertoptions = '-quality '.
//    	(intval($TYPO3_CONF_VARS['GFX']['jpg_quality'])? intval($TYPO3_CONF_VARS['GFX']['jpg_quality']): 90);

    /** definition of fixed image sizes as array(width, height, name)
        name designates the extension to the image filename resized images will get */
    static protected $imgSizes = array(
    	//	shorty image
    	IMAGE_SIZE_SHUFFLE => array('width' =>  52, 'height' =>  26, 'name' => 'shuf'),
    	//	shorty image
    	IMAGE_SIZE_SMALL   => array('width' => 132, 'height' => 132, 'name' => 'smal'),
    	//	column inside article
    	IMAGE_SIZE_NORMAL  => array('width' => 212, 'height' => 106, 'name' => 'norm'),
    	//	full width inside article
    	IMAGE_SIZE_FULL    => array('width' => 424, 'height' => 212, 'name' => 'full'),
    	//	maximum size (in a popup)
    	IMAGE_SIZE_XL      => array('width' => 684, 'height' => 342, 'name' => 'xl'),
    	//	teaser image ("ressort teaser"... the name is old, but no reason to change it)
    	IMAGE_SIZE_RESTEA  => array('width' => 136, 'height' =>  68, 'name' => 'rtea'),
    	//	main teaser image
        IMAGE_SIZE_MAINTEA => array('width' => 424, 'height' => 212, 'name' => 'mtea'));

	
}

tx_newspaper_Extra::registerExtra(new tx_newspaper_extra_image());

//  Popups displaying enlarged images are handled here
if ($_GET['bild_fuer_artikel']) { 
	//...
}
?>
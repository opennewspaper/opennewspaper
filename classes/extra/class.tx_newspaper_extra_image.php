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

		if ($fieldArray['image']) self::resizeImages($fieldArray['image']);
	}
	
	////////////////////////////////////////////////////////////////////////////
	
	/** If image needs resizing, resize it to all sizes defined in TSConfig as
	 *  \code
	 *  newspaper.image.size.{KEY} = {WIDTH}x{HEIGHT}
	 *  \endcode
	 *  and move the resized image to the folder {WIDTH}x{HEIGHT} located under 
	 *  \p self::$basepath as defined via TSCONFIG as
	 *  \code
	 *  newspaper.image.basepath = {BASEPATH}
	 *  \endcode
	 * 
	 *  \param $image Name of the uploaded image file
	 */
	protected static function resizeImages($image) {
		/// Check TSConfig in Extra_Image sysfolder
 		$sysfolder = tx_newspaper_Sysfolder::getInstance()->getPid(new tx_newspaper_Extra_Image()); 
		$TSConfig = t3lib_BEfunc::getPagesTSconfig($sysfolder);
		self::$basepath = $TSConfig['newspaper.']['image.']['basepath'];
		self::$sizes =  $TSConfig['newspaper.']['image.']['size.'];

		t3lib_div::devlog('basepath', 'newspaper', 0, self::$basepath);
		t3lib_div::devlog('sizes', 'newspaper', 0, self::$sizes);

		foreach (self::$sizes as $key => $dimension) {
	    	if (self::imgIsResized($image, $dimension)) continue;
	    	$wxh = explode('x', $dimension);
	    	$width = intval($wxh[0]);
	    	$height = intval($wxh[1]);
	    	if (!$width || !$height) {
	    		throw new tx_newspaper_IllegalUsageException(
	    			'TSConfig usage: "newspaper.image.size.{KEY} = {WIDTH}x{HEIGHT}". ' . "\n" .
	    			'Actual TSConfig for this line: ' . 'newspaper.image.size.' . $key . ' = ' . $dimension
	    		);
	    	}
			self::resizeImage($width, $height,
							  self::uploads_folder . '/'. $image,
							  self::$basepath . self::imageResizedName($image, $dimension));
		}
	}

    /** if image needs resizing, do it (using imagemagick)					  */
    protected static function resizeImage($width, $height, $source, $target) {

		t3lib_div::devlog('resizeImage()', 'newspaper', 0, 
			array('width' => $width, 'height' => $height, 'source' => $source, 'target' => $target)
		);
    	if (!file_exists(dirname(PATH_site . $target))) {
    		if(!mkdir(dirname(PATH_site . $target))) {
				throw new tx_newspaper_Exception('Couldn\'t mkdir(' . dirname(PATH_site . $target) . ')');
    		}
    	}
    	if (!file_exists(PATH_site . $target)) {
    		system(self::$convert . ' ' . self::$convertoptions .
				' -geometry ' . $width . /*'x'.$height.*/ ' ' .
				PATH_site . $source . ' ' . PATH_site . $target);
    	}
    }

    /** @return name of resized image										  */
    protected static function imageResizedName($img, $dimension) {
    	return $dimension . '/' . $img;
    }

    /** @return whether resized image already exists					 	  */
    protected static function imgIsResized($img, $dimension) {
    	return file_exists(self::imageResizedName($img, $dimension));
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

	const uploads_folder = 'uploads/tx_newspaper';
	
    /** path to convert(1) */
    static protected $convert = '/usr/bin/convert';

	/** options to convert */
	static protected $convertoptions = '-quality 90';
//    static protected $convertoptions = '-quality '.
//    	(intval($TYPO3_CONF_VARS['GFX']['jpg_quality'])? intval($TYPO3_CONF_VARS['GFX']['jpg_quality']): 90);


	private static $basepath = null;
	private static $sizes = array();	
}

tx_newspaper_Extra::registerExtra(new tx_newspaper_extra_image());

//  Popups displaying enlarged images are handled here
if ($_GET['bild_fuer_artikel']) { 
	//...
}
?>
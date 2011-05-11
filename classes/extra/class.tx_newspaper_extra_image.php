<?php

require_once(PATH_typo3conf . 'ext/newspaper/classes/class.tx_newspaper_extra.php');
require_once(PATH_typo3conf . 'ext/newspaper/classes/class.tx_newspaper_sysfolder.php');

/// tx_newspaper_Extra that displays an image
/** This Extra displays an image, along with several parameters such as a title,
 *  caption or alt text.
 *
 *  The size of the image can be adjusted from the Smarty template, but it must
 *  be one of a list of sizes predefined in the TSConfig of the storage page of
 *  the Image Extras.
 *
 *  Image sizes can be defined like this (example):
 * 	\code
 *  newspaper.image.size.article = 500x300
 *  newspaper.image.size.teaser = 250x150
 *  \endcode
 *  Images are automatically resized to all defined sizes immediately on upload.
 *
 *  The sizes are supplied to the Smarty template as variable \p $sizes. An
 *  example for displaying the image in the Smarty template:
 *  \code
 *  	<img src="{$basepath}/{$sizes.article}/{$attributes.filename}" \>
 *  \endcode
 *
 *  The following member functions are especially useful in a Smarty template:
 *  - getBasepath()
 *  - getSizes()
 *  - getWidths()
 *  - getHeights()
 *  - getAttribute('filename')
 *  - getAttribute('alttext')
 *  - getAttribute('caption')
 *
 *  Attributes:
 *  - \p pool (bool)
 *  - \p title (string)
 *  - \p image_file (string)
 *  - \p caption (string)
 *  - \p normalized_filename (string)
 *  - \p kicker (string)
 *  - \p credit (string)
 *  - \p source (string)
 *  - \p type (int index to select tag)
 *  - \p alttext (string)
 *  - \p tags (UIDs of tx_newspaper_Tag)
 */
class tx_newspaper_Extra_Image extends tx_newspaper_Extra {

    /// Create a tx_newspaper_Extra_Image
    public function __construct($uid = 0) {
        if ($uid) {
            parent::__construct($uid);
        }
    }

    public function __toString() {
        $ret = '';
        try{
            $ret .= 'Extra: UID ' . $this->getExtraUid() . ', Image: UID ' . $this->getUid();
            $ret .= ' (Title: ' . $this->getAttribute('title') . ')';
        } catch (Exception $e) {  }
        return $ret;
    }

    /** Assigns image attributes and TSConfig parameters to smarty template,
     *  then renders it.
     *
     *  Smarty template:
     *  \include res/templates/tx_newspaper_extra_image.tmpl
     */
    public function render($template_set = '') {

        tx_newspaper::startExecutionTimer();

        $this->prepare_render($template_set);

        $this->smarty->assign('basepath', self::getBasepath());
        $this->smarty->assign('sizes', self::getSizes());
        $this->smarty->assign('widths', self::getWidths());
        $this->smarty->assign('heights', self::getHeights());
        $this->smarty->assign('type', $this->getImageType());

        $rendered = $this->smarty->fetch($this);

        tx_newspaper::logExecutionTime();

        return $rendered;
    }

    /// A short description that makes an Extra uniquely identifiable in the BE
	/** Displays title and UID of the image, as well as a thumbnail of it.
	 */
	public function getDescription() {
		self::getTSConfig();

		if ($this->getAttribute(self::image_file_field)) {
			$thumbnail_file = PATH_site . self::$basepath . '/' . self::$sizes[self::thumbnail_name] .
			 		  '/' . $this->getAttribute(self::image_file_field);
			if (file_exists($thumbnail_file)) {
				$image_tag = '<img src="/' . self::$basepath . '/' . self::$sizes[self::thumbnail_name] .
			 		  '/' . $this->getAttribute(self::image_file_field) . '" />';
			} else {
				$image_tag = tx_newspaper_BE::renderIcon(
					'gfx/icon_warning.gif', '',
					tx_newspaper::getTranslation('message_image_missing')
				);
			}
		} else {
			$image_tag = tx_newspaper_BE::renderIcon(
					'gfx/icon_warning2.gif', '',
					tx_newspaper::getTranslation('message_image_unset')
			);
		}

		return $this->getAttribute('title') . ' (#' . $this->getUid() . ')' .
			$image_tag;
	}

// title for module
	public static function getModuleName() {
		return 'np_image';
	}

	public static function dependsOnArticle() { return false; }

	/// Get the array of possible image sizes registered in TSConfig
	public static function getSizes() {
		self::getTSConfig();
		return self::$sizes;
	}

    public static function getWidths() {
        self::fillWidthOrHeightArray(self::$widths, 0);
        return self::$widths;
    }

    public static function getHeights() {
        self::fillWidthOrHeightArray(self::$heights, 1);
        return self::$heights;
    }

    private static function fillWidthOrHeightArray(array &$what, $index) {
        if (empty($what)) {
            foreach (self::getSizes() as $size) {
                $width_and_height = explode('x', $size);
                if (isset($width_and_height[$index])) {
                    $what[] = $width_and_height[$index];
                }
            }
        }
    }

	/// Get the path from root to the images directory, as registered in TSConfig
	public static function getBasepath() {
		self::getTSConfig();
		return self::$basepath;
	}

	public function getSearchFields() {
		return array('title', 'caption', 'kicker');
	}

	/// Save hook function, called from the global save hook
	/** Resizes the uploaded image into all sizes specified in TSConfig.
	 *
	 *  \todo Appends the UID of the Extra_Image to the image file name.
	 *  \todo Normalizes field \p normalized_filename.
	 *
	 *  Damn the Typo3 documentation, I was unable to find authoritative docs
	 *  for processDatamap_postProcessFieldArray(). Here's what i could deduce.
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

		self::getTSConfig(); // called after class check, otherwise TSConfig for images is read for every record stored in typo3

//		t3lib_div::devlog('image save hook', 'newspaper', 0,
//			array('status' => $status, 'table' => $table, 'id' => $id,
//				  'fieldArray' => $fieldArray, 'that' => $that));

		if ($fieldArray[self::image_file_field]) {
			self::resizeImages($fieldArray[self::image_file_field]);
		}
	}

    ////////////////////////////////////////////////////////////////////////////

    /// If image needs resizing, resize it to all sizes defined in TSConfig
	/** The image sizes are defined as
	 *  \code
	 *  newspaper.image.size.{KEY} = {WIDTH}x{HEIGHT}
	 *  \endcode
	 *  and move the resized image to the folder {WIDTH}x{HEIGHT} located under
	 *  \p self::$basepath as defined via TSConfig as
	 *  \code
	 *  newspaper.image.basepath = {BASEPATH}
	 *  \endcode
	 *
	 *  \param $image Name of the uploaded image file
	 */
	protected static function resizeImages($image) {

		self::getTSConfig(); // make sure tsconfig is read (when called from outside tx_newspaper_extra_image

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
							  self::$basepath . '/' . self::imageResizedName($image, $dimension));
		}
	}

    /// If image needs resizing, do it (using ImageMagick)
    /** This function is called with the path of the resized image. If the
     *  directory which contains the resized image does not exist, it is
     *  created.
     *
     *  If the resized file already exists, it is not re-created.
     *
     *  \todo Launch the \c convert - process in the background, returning
     * 		immediately.
     *  \todo clean up this function
     *
     *  \param $width Desired width of the image
     *  \param $height Desired height of the image (currently ignored; the image
     *  	is resized by width, keeping its aspect ratio)
     *  \param $source File name of the source image, relative to the Typo3
     * 		installation directory
     *  \param $target  File name of the resized image, relative to the Typo3
     * 		installation directory
     */
    protected static function resizeImage($width, $height, $source, $target) {

		global $TYPO3_CONF_VARS;

    	if (!file_exists(dirname(PATH_site . $target))) {
    		if(!mkdir(dirname(PATH_site . $target), 0770, true)) {
				throw new tx_newspaper_Exception('Couldn\'t mkdir(' . dirname(PATH_site . $target) . ')');
    		}
    	}
    	if (!file_exists(PATH_site . $target)) {

    		$convert = self::convert .
    			' -quality ' .
    				(intval($TYPO3_CONF_VARS['GFX']['jpg_quality'])?
    					intval($TYPO3_CONF_VARS['GFX']['jpg_quality']):
    					self::default_jpeg_quality) .
				' -geometry ' . $width .
					(self::contain_aspect_ratio? '': ('x' . $height)) .
				' ' . self::convert_options .
			 	' \'' . PATH_site . $source . '\'' .
				' \'' . PATH_site . $target . '\'';
        $return = array();

    		exec($convert, $return);

    		t3lib_div::devlog('command line for convert', 'newspaper', 0, $convert);
    		if ($return) t3lib_div::devlog('convert output', 'newspaper', 0, $return);
    	}
    }

	/// Get the name of an image of a given size
    /** \param $img Image file name
     *  \param $dimension Width and height of the desired image
     * 	\return name of resized image
     */
    protected static function imageResizedName($img, $dimension) {
    	return $dimension . '/' . $img;
    }

    /// Checks if an image already exists in a resized version
    /** \param $img Image file name
     *  \param $dimension Width and height of the desired image
     *  \return whether resized image already exists
     */
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
			tx_newspaper::devlog(
                "Transfer of uploaded images to live server failed!",
                array('command' => $command, 'output' => $output, 'return value' => $return)
            );
		}

	}

	///	Read base path and predefined sizes for images
	/** The following parameters must be read from the TSConfig for the storage
	 *  SysFolder for Image Extras:
	 *  \code
	 *  newspaper.image.basepath
	 *  newspaper.image.size....
	 *  \endcode
	 *
	 *  \return The whole TSConfig for the storage SysFolder for Image Extras
	 */
	protected static function getTSConfig() {

		if (self::$basepath && self::$sizes) return;

		/// Check TSConfig in Extra_Image sysfolder
 		$sysfolder = tx_newspaper_Sysfolder::getInstance()->getPid(new tx_newspaper_Extra_Image());
		$TSConfig = t3lib_BEfunc::getPagesTSconfig($sysfolder);

		if (!self::$basepath) {
			if (!$TSConfig['newspaper.']['image.']['basepath']) {
				t3lib_div::devlog('basepath newspaper.image.basepath not set in TSConfig',
								  'newspaper', 1, array('TSConfig' =>$TSConfig));
			}
			self::$basepath = $TSConfig['newspaper.']['image.']['basepath'];
		}

		if (!self::$sizes) {
			self::$sizes =  $TSConfig['newspaper.']['image.']['size.'];
			if (!isset(self::$sizes[self::thumbnail_name])) {
				self::$sizes[self::thumbnail_name] = self::thumbnail_size;
			}
		}

		return $TSConfig;
	}

    private function getImageType() {
        if (!in_array('type', tx_newspaper::getAttributes($this))) return false;

        $type_index = $this->getAttribute('type');
        $type_language_key = "tx_newspaper_extra_image.type.I.$type_index";
        $type_string = tx_newspaper::getTranslation($type_language_key, 'locallang_db.xml');
        return $type_string;
    }

    /// The field which carries the image file
    const image_file_field = 'image_file';

    ///	Where Typo3 stores uploaded images
    const uploads_folder = 'uploads/tx_newspaper';

    /// path to \c convert(1)
    const convert = '/usr/bin/convert';

    /// Name of the size for thumbnail images displayed in the BE
    const thumbnail_name = 'thumbnail';
    /// Default size for thumbnail images displayed in the BE (overridable with TSConfig)
    const thumbnail_size = '64x64';

    /// Default quality for JPEG compression.
    /** Overridden by \p $TYPO3_CONF_VARS['GFX']['jpg_quality'].
     */
    const default_jpeg_quality = 90;

    /// Other options to \c convert(1)
    const convert_options = '';

    /// Whether to keep aspect ratio when resizing images
    const contain_aspect_ratio = false;

    /// The path to the image storage directory, relative to the Typo3 installation directory
    private static $basepath = null;

    /// The list of image sizes, predefined in TSConfig
    private static $sizes = array();
    /// The list of image widths, predefined as sizes in TSConfig
    private static $widths = array();
    /// The list of image heights, predefined as sizes in TSConfig
    private static $heights = array();

}

tx_newspaper_Extra::registerExtra(new tx_newspaper_extra_Image());

tx_newspaper::registerSaveHook(new tx_newspaper_extra_Image());

//  Popups displaying enlarged images are handled here
if ($_GET['bild_fuer_artikel']) {
	//...
}
?>
<?php
/**
 * Created by JetBrains PhpStorm.
 * User: lene
 * Date: 5/19/11
 * Time: 3:15 PM
 * To change this template use File | Settings | File Templates.
 */

/// Class for handing the upload, resizing and deployment of an image.
class tx_newspaper_Image {

    public function __construct($image_file) {
        $this->image_file = $image_file;
    }

    public function prepare_render(tx_newspaper_Smarty $smarty) {
        $smarty->assign('basepath', self::getBasepath());
        $smarty->assign('sizes', self::getSizes());
        $smarty->assign('widths', self::getWidths());
        $smarty->assign('heights', self::getHeights());
    }

    public function getThumbnail() {
        self::getTSConfig();

        if ($this->image_file) {
            $thumbnail_file = PATH_site . self::$basepath . '/' . self::$sizes[self::thumbnail_name] .
                       '/' . $this->image_file;
            if (file_exists($thumbnail_file)) {
                return '<img src="/' . self::$basepath . '/' . self::$sizes[self::thumbnail_name] .
                       '/' . $this->image_file . '" />';
            } else {
                return tx_newspaper_BE::renderIcon(
                    'gfx/icon_warning.gif', '',
                    tx_newspaper::getTranslation('message_image_missing')
                );
            }
        } else {
            return tx_newspaper_BE::renderIcon(
                    'gfx/icon_warning2.gif', '',
                    tx_newspaper::getTranslation('message_image_unset')
            );
        }
    }


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
	public function resizeImages() {

		self::getTSConfig(); // make sure tsconfig is read (when called from outside tx_newspaper_extra_image

		foreach (self::$sizes as $key => $dimension) {

	    	if (self::imgIsResized($this->image_file, $dimension)) continue;

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
							  self::uploads_folder . '/'. $this->image_file,
							  self::$basepath . '/' . self::imageResizedName($this->image_file, $dimension));
		}
	}


	/// Get the path from root to the images directory, as registered in TSConfig
	public static function getBasepath() {
		self::getTSConfig();
		return self::$basepath;
	}

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
	private static function getTSConfig() {

		if (self::$basepath && self::$sizes) return;

 		$sysfolder = tx_newspaper_Sysfolder::getInstance()->getPidRootfolder();
		$TSConfig = t3lib_BEfunc::getPagesTSconfig($sysfolder);

		if (!self::$basepath) self::setBasepath($TSConfig);
		if (!self::$sizes) self::setSizes($TSConfig);

		return $TSConfig;
	}

    private static function setBasepath($TSConfig) {
        if (!$TSConfig['newspaper.']['image.']['basepath']) {
            self::$basepath = 'uploads/images';
        }
        self::$basepath = $TSConfig['newspaper.']['image.']['basepath'];
    }

    private static function setSizes($TSConfig) {
        self::$sizes =  $TSConfig['newspaper.']['image.']['size.'];
        if (!isset(self::$sizes[self::thumbnail_name])) {
            self::$sizes[self::thumbnail_name] = self::thumbnail_size;
        }
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
    private static function resizeImage($width, $height, $source, $target) {

        self::makeTargetDir($target);

    	if (!file_exists(PATH_site . $target)) {
            self::executeConvert($width, $height, $source, $target);
    	}
    }

    private static function makeTargetDir($target) {
        if (!file_exists(dirname(PATH_site . $target))) {
            if(!mkdir(dirname(PATH_site . $target), 0770, true)) {
                throw new tx_newspaper_Exception('Couldn\'t mkdir(' . dirname(PATH_site . $target) . ')');
            }
        }
    }

    private static function executeConvert($width, $height, $source, $target) {
        $convert = self::convert .
            ' -quality ' . self::getJPEGQuality() .
            ' -geometry ' . $width .
                (self::contain_aspect_ratio? '': ('x' . $height)) .
            ' ' . self::convert_options .
             ' \'' . PATH_site . $source . '\'' .
            ' \'' . PATH_site . $target . '\'';

        $return = array();
        exec($convert, $return);

        if ($return) tx_newspaper::devlog('convert(1)', array('command'=>$convert, 'return'=>$return));
    }

	/// Get the name of an image of a given size
    /** \param $img Image file name
     *  \param $dimension Width and height of the desired image
     * 	\return name of resized image
     */
    private static function imageResizedName($img, $dimension) {
    	return $dimension . '/' . $img;
    }

    /// Checks if an image already exists in a resized version
    /** \param $img Image file name
     *  \param $dimension Width and height of the desired image
     *  \return whether resized image already exists
     */
    private static function imgIsResized($img, $dimension) {
    	return file_exists(self::imageResizedName($img, $dimension));
    }

    private static function getJPEGQuality() {
        global $TYPO3_CONF_VARS;
        return intval($TYPO3_CONF_VARS['GFX']['jpg_quality'])?
    		   intval($TYPO3_CONF_VARS['GFX']['jpg_quality']):
    		   self::default_jpeg_quality;

    }

	/** copy $basedir to $targetPath on $targetHost	*/
	private function rsync($basedir, $targetHost, $targetPath) {
		$command = "rsync " . self::getRsyncOptions() . " \"$basedir\" \"$targetHost:$targetPath\" 2>&1"; // "..." -> space in path
		$output = array();
		$return = 0;
		exec($command, $output, $return);
        if (self::getRsyncLog()) {
            exec("date >> " . self::getRsyncLog());
            $f = fopen(self::getRsyncLog(), "a+");
            fwrite ($f, "$basedir\n");
            fwrite ($f, print_r($output,1));
            fclose($f);
        }

        if ($return) {
			tx_newspaper::devlog(
                "Transfer of uploaded images to live server failed!",
                array('command' => $command, 'output' => $output, 'return value' => $return)
            );
		}

	}

    private static function getRsyncLog() {
        if (tx_newspaper::getTSConfigVar('rsync_log')) {
            return tx_newspaper::getTSConfigVar('rsync_log');
        }
        return null;
    }

    private static function getRsyncOptions() {
        if (tx_newspaper::getTSConfigVar('rsync_options')) {
            return tx_newspaper::getTSConfigVar('rsync_options');
        }
        return '';
    }

    private $image_file = null;

    /// The path to the image storage directory, relative to the Typo3 installation directory
    private static $basepath = null;
    /// The list of image sizes, predefined in TSConfig
    private static $sizes = array();
    /// The list of image widths, predefined as sizes in TSConfig
    private static $widths = array();
    /// The list of image heights, predefined as sizes in TSConfig
    private static $heights = array();

    /// Name of the size for thumbnail images displayed in the BE
    const thumbnail_name = 'thumbnail';
    /// Default size for thumbnail images displayed in the BE (overridable with TSConfig)
    const thumbnail_size = '64x64';


    ///	Where Typo3 stores uploaded images
    const uploads_folder = 'uploads/tx_newspaper';

    /// path to \c convert(1)
    const convert = '/usr/bin/convert';

    /// Default quality for JPEG compression.
    /** Overridden by \p $TYPO3_CONF_VARS['GFX']['jpg_quality'].
     */
    const default_jpeg_quality = 90;

    /// Other options to \c convert(1)
    const convert_options = '';

    /// Whether to keep aspect ratio when resizing images
    const contain_aspect_ratio = false;

}

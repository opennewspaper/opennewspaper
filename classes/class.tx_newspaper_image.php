<?php
/**
 * Created by JetBrains PhpStorm.
 * User: lene
 * Date: 5/19/11
 * Time: 3:15 PM
 * To change this template use File | Settings | File Templates.
 */

require_once('private/class.tx_newspaper_file.php');
require_once('private/class.tx_newspaper_imagesizeset.php');


/// Class for handing the upload, resizing and deployment of an image.
class tx_newspaper_Image extends tx_newspaper_TSconfigControlled {

    public function __construct($image_file, $width_set = 0) {
        $this->image_file = $image_file;
        $this->width_set = intval($width_set);
    }

    public function prepare_render(tx_newspaper_Smarty $smarty) {
        $smarty->assign('basepath', self::getBasepath());
        $smarty->assign('sizes', $this->getSizes());
        $smarty->assign('widths', $this->getWidths());
        $smarty->assign('heights', $this->getHeights());
        $size_set = new tx_newspaper_ImageSizeSet($this->width_set);
        $smarty->assign('width_set', $size_set->getLabel());
    }

    public function getThumbnail() {
        if (!$this->image_file) {
            return self::iconImageUnset();
        }

        $this->resizeImage(self::getThumbnailWidth(), self::getThumbnailHeight());

        if (file_exists(PATH_site . $this->getThumbnailPath())) {
            return $this->getIcon();
        }

        return self::iconImageMissing();
    }

    public function getSizes() {
        return self::getAllSizes($this->width_set);
    }

    public function getWidths() {
        return $this->getAllWidths($this->width_set);
    }

    public function getHeights() {
        return $this->getAllHeights($this->width_set);
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

		self::readTSConfig(); // make sure tsconfig is read (when called from outside tx_newspaper_extra_image
		foreach ($this->getSizes() as $key => $dimension) {
	    	if (self::imgIsResized($this->image_file, $dimension)) continue;
            $this->resizeImage(self::extractWidth($dimension, $key), self::extractHeight($dimension, $key));
		}
	}

    public function resizeImage($width, $height) {
        self::doResizeImage(
            $width, $height,
            self::uploads_folder . '/'. $this->image_file,
            self::$basepath . '/' . self::imageResizedName($this->image_file, "${width}x${height}")
        );
    }

    public function copyTo($folder) {
        self::makeTargetDir($folder);
        if (!file_exists($folder .'/' . $this->image_file)) {
            copy(self::uploads_folder . '/' . $this->image_file, $folder . '/' . $this->image_file);
        }
    }

    public function rsyncAllImageFiles() {
        foreach ($this->getSizes() as $size) {
            $this->rsyncSingleImageFile($size);
        }
    }

    public function rsyncSingleImageFile($subfolder) {

        if (!self::isRsyncEnabled()) {
            return;
        }

        $filename = implode('/', array(PATH_site, self::getBasepath(), $subfolder, $this->image_file));
        $target_file = implode('/', array(self::$rsync_path, $subfolder, $this->image_file));
        self::rsync($filename, self::$rsync_host, $target_file);
    }

    ////////////////////////////////////////////////////////////////////////////
    
	/// Get the path from root to the images directory, as registered in TSConfig
	public static function getBasepath() {
		self::readTSConfig();
		return self::$basepath;
	}

	/// Get the array of possible image sizes registered in TSConfig
	public static function getAllSizes($width_set = 0) {
		self::readTSConfig();
		return self::$sizes[$width_set];
	}

    public static function getAllWidths($width_set = 0) {
        self::fillWidthOrHeightArray(self::$widths, 0);
        return self::$widths[$width_set];
    }

    public static function getAllHeights($width_set = 0) {
        self::fillWidthOrHeightArray(self::$heights, 1);
        return self::$heights[$width_set];
    }

    public static function getMaxImageFileSize() {
        return 10240; // 10 mb \todo: make configurable
    }

    ////////////////////////////////////////////////////////////////////////////

    /** copy $basedir to $targetPath on $targetHost	*/
    private static function rsync($basedir, $targetHost, $targetPath) {

        $command = "rsync " . self::getRsyncOptions() . " \"$basedir\" \"$targetHost:$targetPath\" 2>&1"; // "..." -> space in path
        $output = array();
        $return = 0;
        exec($command, $output, $return);
        
        self::writeRsyncLog($basedir, $output);

        if ($return) {
            self::logRsyncError($command, $output, $return);
        }

    }

    private static function logRsyncError($command, $output, $return) {
        tx_newspaper::devlog(
            "Transfer of uploaded images to live server failed!",
            array('command' => $command, 'output' => $output, 'return value' => $return)
        );
    }

    private static function writeRsyncLog($basedir, $output) {
        if (self::getRsyncLog()) {
            exec("date >> " . self::getRsyncLog());
            $f = new tx_newspaper_File(self::getRsyncLog());
            $f->write("$basedir\n");
            $f->write(print_r($output, 1));
        }
    }

    private static function readRsyncOptions() {
        if (!is_null(self::$rsync_host)) return;

        self::$rsync_host = self::getTSConfigVar('rsync_host');
        self::$rsync_path = self::getTSConfigVar('rsync_path');

        $target_user = self::getTSConfigVar('rsync_user');
        if ($target_user) {
            self::$rsync_host = $target_user . '@' .self::$rsync_host;
        }

    }

    private static $ts_config = array();
    private static function getTSConfigVar($key) {
        if (empty(self::$ts_config)) {
            $global_tsconfig = tx_newspaper::getTSConfig();
            self::$ts_config = $global_tsconfig['newspaper.'];
        }
        return self::$ts_config[$key];
    }
    private static function isRsyncEnabled() {
        self::readRsyncOptions();
        return !(empty(self::$rsync_host) || empty(self::$rsync_path));
    }

    private function getThumbnailPath() {
        self::readTSConfig();

        return self::$basepath . '/' . self::$sizes[0][self::thumbnail_name] . '/' . $this->image_file;
    }

    private static function getThumbnailWidth() {
        $widths = self::getAllWidths();
        return $widths[self::thumbnail_name];
    }

    private static function getThumbnailHeight() {
        $heights = self::getAllHeights();
        return $heights[self::thumbnail_name];
    }

    private function getIcon() {
        return '<img src="/' . $this->getThumbnailPath() . '" />';
    }

    private static function iconImageMissing() {
        return tx_newspaper_BE::renderIcon(
            'gfx/icon_warning.gif', '',
            tx_newspaper::getTranslation('message_image_missing')
        );
    }

    private static function iconImageUnset() {
        return tx_newspaper_BE::renderIcon(
                'gfx/icon_warning2.gif', '',
                tx_newspaper::getTranslation('message_image_unset')
        );
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
	private static function readTSConfig() {

		if (self::$basepath && self::$sizes) return;

        $TSConfig = self::getTSconfig();

		if (!self::$basepath) self::setBasepath($TSConfig);
		if (!self::$sizes) self::setSizes();

		return $TSConfig;
	}

    private static function setBasepath($TSConfig) {
        if (!$TSConfig['newspaper.']['image.']['basepath']) {
            self::$basepath = 'uploads/images';
        }
        self::$basepath = $TSConfig['newspaper.']['image.']['basepath'];
    }

    private static function setSizes() {
        self::$sizes[0] = self::getDefaultSizesArray();
        self::fillArrayForFormat(self::$sizes, 'getSizesFromTSconfigForFormat');
        for ($i = 1; $i < sizeof(self::$sizes); $i++) {
            self::$sizes[$i][self::thumbnail_name] = self::thumbnail_size;
        }
    }

    private static function getDefaultSizesArray() {
        $TSconfig = self::getTSconfig();
        $sizes = $TSconfig['newspaper.']['image.']['size.'];
        if (!isset($sizes[self::thumbnail_name])) {
            $sizes[self::thumbnail_name] = self::thumbnail_size;
        }
        return $sizes;
    }

    private static function extractWidth($dimension, $key) {
        return self::extractDimension($dimension, $key, 0);
    }

    private static function extractHeight($dimension, $key) {
        return self::extractDimension($dimension, $key, 1);
    }

    private static function extractDimension($dimension, $key, $index) {
        $wxh = explode('x', $dimension);
        $dim = intval($wxh[$index]);
        if (!$dim) {
            throw new tx_newspaper_IllegalUsageException(
                'TSConfig usage: "newspaper.image.size.{KEY} = {WIDTH}x{HEIGHT}". ' . "\n" .
                'Actual TSConfig for this line: ' . 'newspaper.image.size.' . $key . ' = ' . $dimension
            );
        }
        return $dim;
    }

    private static function fillWidthOrHeightArray(array &$what, $index) {
        if (empty($what)) {
            foreach (self::getAllSizes() as $key => $size) {
                $width_and_height = explode('x', $size);
                if (isset($width_and_height[$index])) {
                    $what[$key] = $width_and_height[$index];
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
    private static function doResizeImage($width, $height, $source, $target) {

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

    private static function getRsyncLog() {
        if (self::getTSConfigVar('rsync_log')) {
            return self::getTSConfigVar('rsync_log');
        }
        return null;
    }

    private static function getRsyncOptions() {
        if (tx_newspaper::getTSConfigVar('rsync_options')) {
            return tx_newspaper::getTSConfigVar('rsync_options');
        }
        return '';
    }

    ////////////////////////////////////////////////////////////////////////////

    private $image_file = null;

    private $width_set = 0;

    /// The path to the image storage directory, relative to the Typo3 installation directory
    private static $basepath = null;
    /// The list of image sizes, predefined in TSConfig
    private static $sizes = array();
    /// The list of image widths, predefined as sizes in TSConfig
    private static $widths = array();
    /// The list of image heights, predefined as sizes in TSConfig
    private static $heights = array();

    private static $rsync_host = null;
    private static $rsync_path = null;

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

/// An image with no image file, instantiated where an image is needed
class tx_newspaper_NullImage extends tx_newspaper_Image {
    public function __construct() { }
}
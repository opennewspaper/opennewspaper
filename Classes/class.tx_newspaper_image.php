<?php
/**
 *  Created by JetBrains PhpStorm.
 *  User: lene
 *  Date: 5/19/11
 *  Time: 3:15 PM
 *  To change this template use File | Settings | File Templates.
 */

require_once('private/class.tx_newspaper_file.php');
require_once('private/class.tx_newspaper_imagesizeset.php');
require_once('private/class.tx_newspaper_imagethumbnail.php');

/// Class for handing the upload, resizing and deployment of an image.
/**
 *  \todo Factor out handling of resize and rsync into separate classes.
 *  \todo Documentation
 */
class tx_newspaper_Image extends tx_newspaper_TSconfigControlled {

    public function __construct($image_file, $width_set = 0) {
        $this->image_file = $image_file;
        $this->size_set = new tx_newspaper_ImageSizeSet($width_set);
        $this->thumbnail = new tx_newspaper_ImageThumbnail($this);
    }

    public function __toString() {
        return "file: $this->image_file Sizes: " . print_r($this->getSizes(), 1) . " Widths: " . print_r($this->getWidths(), 1);
    }

    public function prepare_render(tx_newspaper_Smarty $smarty) {
        $smarty->assign('basepath', self::getBasepath());
        $smarty->assign('sizes', $this->getSizes());
        $smarty->assign('widths', $this->getWidths());
        $smarty->assign('heights', $this->getHeights());
        $smarty->assign('size_set_label', $this->size_set->getLabel());
        $smarty->assign('size_set_name', $this->size_set->getName());
    }

    public function getThumbnail() {
        return $this->thumbnail->getThumbnail();
    }

    public function getSizes() {
        return $this->joinDimensionWithDefaultDimension('getSizes');
    }

    public function getWidths() {
        return $this->joinDimensionWithDefaultDimension('getWidths');
    }

    public function getHeights() {
        return $this->joinDimensionWithDefaultDimension('getHeights');
    }

    public function getFormatLabel() {
        return $this->size_set->getLabel();
    }

    public function getFormatName() {
        return $this->size_set->getName();
    }

    public function getFilename() {
        return $this->image_file;
    }

    public function deployImages() {

        $timer = tx_newspaper_ExecutionTimer::create();

        $this->resizeImages();
        $this->rsyncAllImageFiles();

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

        $timer = tx_newspaper_ExecutionTimer::create();

		self::readTSConfig(); // make sure tsconfig is read (when called from outside tx_newspaper_extra_image
		foreach ($this->getSizes() as $dimension) {
	    	if (self::imgIsResized($this->image_file, $dimension)) continue;
            $this->resizeImage(self::extractWidth($dimension), self::extractHeight($dimension));
		}
	}

    public function resizeImage($width, $height) {
        self::doResizeImage(
            $width, $height,
            self::uploads_folder . '/'. $this->image_file,
            self::$basepath . '/' . self::imageResizedName($this->image_file, self::dimension($width, $height))
        );
    }

    private static function dimension($width, $height) {
        if (intval($height)) return "${width}x${height}";
        return $width;
    }

    public function copyTo($folder) {
        self::makeTargetDir($folder);
        if (!file_exists($folder .'/' . $this->image_file)) {
            copy(self::uploads_folder . '/' . $this->image_file, $folder . '/' . $this->image_file);
        }
    }

    public function rsyncAllImageFiles() {

        $timer = tx_newspaper_ExecutionTimer::create();

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

    public static function getMaxImageFileSize() {
        return 10240; // 10 mb \todo: make configurable
    }

    ////////////////////////////////////////////////////////////////////////////

    private function joinDimensionWithDefaultDimension($function) {
        $default_size_set = new tx_newspaper_ImageSizeSet(0);
        return array_unique(array_merge($this->size_set->$function(), $default_size_set->$function()));
    }

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

    private static function setBasepath($TSConfig) {
        self::$basepath = $TSConfig['newspaper.']['image.']['basepath'];
        if (!self::$basepath) self::$basepath = 'uploads/images';
    }

    private static function extractWidth($dimension) {
        return self::extractDimension($dimension, 0);
    }

    private static function extractHeight($dimension) {
        return self::extractDimension($dimension, 1);
    }

    private static function extractDimension($dimension, $index) {
        $wxh = explode('x', $dimension);
        return intval($wxh[$index]);
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

    /**
     * Get (path and) ImageMagick's convert program
     * @return string (Path and) program name, defaults to /usr/bin/convert
     */
    private static function getConvertCommand() {
        $tsc = tx_newspaper::getTSConfig();
        return $tsc['newspaper.']['ImageMagick.']['convert']? $tsc['newspaper.']['ImageMagick.']['convert'] : '/usr/bin/convert';
    }

    /**
     * Scale the images according to $width and $height settings
     * When successful: writes a devlog entry
     * @param $width int Target image width
     * @param $height int Target image height
     * @param $source string Source image filename
     * @param $target string Target image file name
     */
    private static function executeConvert($width, $height, $source, $target) {
        $convert = self::getConvertCommand() .
            ' -quality ' . self::getJPEGQuality() .
            ' -geometry ' . $width .
                (!self::scaleToWidth() && ($height > 0)? ('x' . $height): '') .
            ' ' . self::convert_options .
            ' "' . PATH_site . $source . '"' .
            ' "' . PATH_site . $target . '"';

        $return = array();
        exec($convert, $return);

        if ($return) {
            tx_newspaper::devlog('convert(1)', array('command' => $convert, 'return' => $return));
        }
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

    private static function scaleToWidth() {
        return self::scale_to_width;
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

        $TSConfig = self::getTSconfig();

    	if (!self::$basepath) self::setBasepath($TSConfig);

    	return $TSConfig;
    }

    ////////////////////////////////////////////////////////////////////////////

    /** @var string */
    private $image_file = null;
    /** @var tx_newspaper_ImageSizeSet */
    private $size_set = null;
    /** @var tx_newspaper_ImageThumbnail */
    private $thumbnail = null;

    /// The path to the image storage directory, relative to the Typo3 installation directory
    private static $basepath = 'uploads/images';

    private static $rsync_host = null;
    private static $rsync_path = null;

    ///	Where Typo3 stores uploaded images
    const uploads_folder = 'uploads/tx_newspaper';

    /// Default quality for JPEG compression.
    /** Overridden by \p $TYPO3_CONF_VARS['GFX']['jpg_quality'].
     */
    const default_jpeg_quality = 90;

    /// Other options to \c convert(1)
    const convert_options = '';

    /// Whether to keep aspect ratio when resizing images
    const scale_to_width = true;

}

/// An image with no image file, instantiated where an image is needed
class tx_newspaper_NullImage extends tx_newspaper_Image {
    public function __construct() { }
}
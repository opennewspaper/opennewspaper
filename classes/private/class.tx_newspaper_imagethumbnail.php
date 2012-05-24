<?php
/**
 * Author: Lene Preuss <lene.preuss@gmail.com>
 * Date:   1/26/12
 * Time:   5:43 PM
 */

class tx_newspaper_ImageThumbnail {

    public function __construct(tx_newspaper_Image $image) {
        $this->image = $image;
    }

    public function getThumbnail() {
        if (!$this->image->getFilename()) {
            return self::iconImageUnset();
        }

        $this->image->resizeImage($this->getThumbnailWidth(), $this->getThumbnailHeight());

        if (file_exists(PATH_site . $this->getThumbnailPath())) {
            return $this->getIcon();
        }

        return self::iconImageMissing();
    }

    public function getThumbnailWidth() {

        $widths = $this->image->getWidths();
        if (isset($widths[self::thumbnail_name])) return $widths[self::thumbnail_name];
        throw new tx_newspaper_InconsistencyException("image->getWidths() didnt return thumbnail width: " . print_r($widths, 1));
    }

    public function getThumbnailHeight() {
        $heights = $this->image->getHeights();
        return $heights[self::thumbnail_name];
        if (isset($heights[self::thumbnail_name])) return $heights[self::thumbnail_name];
        throw new tx_newspaper_InconsistencyException("image->getHeights() didnt return thumbnail height: " . print_r($heights, 1));
    }

    public static function addThumbnailSizes(array &$sizes) {
        for ($i = 0; $i < sizeof($sizes); $i++) {
            if (isset($sizes[0][self::thumbnail_name])) {
                self::addThumbnailSize($sizes[$i], $sizes[0][self::thumbnail_name]);
            } else {
                self::addThumbnailSize($sizes[$i], self::thumbnail_size);
            }
        }
    }

    ////////////////////////////////////////////////////////////////////////////

    private static function iconImageUnset() {
        return tx_newspaper_BE::renderIcon(
                'gfx/icon_warning2.gif', '',
                tx_newspaper::getTranslation('message_image_unset')
        );
    }

    private function getThumbnailPath() {
        $sizes = $this->image->getSizes();
        return tx_newspaper_Image::getBasepath() . '/' . $sizes[self::thumbnail_name] . '/' . $this->image->getFilename();
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

    private static function addThumbnailSize(array &$sizes, $default) {
        if (!isset($sizes[tx_newspaper_ImageThumbnail::thumbnail_name])) {
            $sizes[tx_newspaper_ImageThumbnail::thumbnail_name] = $default;
        }
    }

    /** @var tx_newspaper_Image */
    private $image;

    /// TSconfig Name of the size for thumbnail images displayed in the BE
    const thumbnail_name = 'thumbnail';
    /// Default size for thumbnail images displayed in the BE (overridable with TSConfig)
    const thumbnail_size = '64x64';

}

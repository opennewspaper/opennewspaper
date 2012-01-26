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

    public static function addThumbnailSize(array &$sizes, $default) {
        if (!isset($sizes[tx_newspaper_ImageThumbnail::thumbnail_name])) {
            $sizes[tx_newspaper_ImageThumbnail::thumbnail_name] = $default;
        }

    }

    ////////////////////////////////////////////////////////////////////////////

    private static function iconImageUnset() {
        return tx_newspaper_BE::renderIcon(
                'gfx/icon_warning2.gif', '',
                tx_newspaper::getTranslation('message_image_unset')
        );
    }

    private function getThumbnailWidth() {
        $widths = $this->image->getWidths();
        return $widths[self::thumbnail_name];
    }

    private function getThumbnailHeight() {
        $heights = $this->image->getHeights();
        return $heights[self::thumbnail_name];
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

    /** @var tx_newspaper_Image */
    private $image;

    /// TSconfig Name of the size for thumbnail images displayed in the BE
    const thumbnail_name = 'thumbnail';

}

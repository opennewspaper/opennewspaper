<?php

/**
 * This view helper displays the page type of a tx_newspaper_Page
 *
 * Usage (ex.):
 * \code
 * {namespace np=Tx_Newspaper_ViewHelpers}
 * <np:pagetype object="{page}" />
 * \endcode
 *
 * @author Lene Preuss <lene.preuss@gmail.com>
 */
class Tx_Newspaper_ViewHelpers_PagetypeViewHelper extends Tx_Fluid_Core_ViewHelper_AbstractViewHelper {

    /**
     * Displays the page type of a tx_newspaper_Page
     *
     * @param tx_newspaper_Page $object
     * @return $object->getAttribute($attribute)
     * @author Lene Preuss <lene.preuss@gmail.com>
     */
    public function render($object) {
        if (!$object instanceof tx_newspaper_Page) return print_r($object, 1);
        try {
            $type = $object->getPageType();
            return $type->getAttribute('type_name');
        } catch (tx_newspaper_Exception $e) {
            // ...
        }
    }
}


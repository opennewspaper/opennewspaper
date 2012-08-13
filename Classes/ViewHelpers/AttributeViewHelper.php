<?php

/**
 * This view helper displays an attribute of a StoredObject
 *
 * Usage (ex.):
 * \code
 * {namespace np=Tx_Newspaper_ViewHelpers}
 * <np:attribute object="{section}" attribute="section_name" />
 * \endcode
 *
 * @author Lene Preuss <lene.preuss@gmail.com>
 */
class Tx_Newspaper_ViewHelpers_AttributeViewHelper extends Tx_Fluid_Core_ViewHelper_AbstractViewHelper {

    /**
     * Renders an attribute of a tx_newspaper_StoredObject
     *
     * @param tx_newspaper_StoredObject $object
     * @validate $object StoredObjectValidator
     * @param string $attribute
     * @validate $attribute StringValidator
     * @return $object->getAttribute($attribute)
     * @author Lene Preuss <lene.preuss@gmail.com>
     */
    public function render($object, $attribute) {
        if (!is_object($object)) return print_r($object, 1);
        try {
            return $object->getAttribute($attribute);
        } catch (tx_newspaper_Exception $e) {
            // ...
        }
    }
}


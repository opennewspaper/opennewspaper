<?php
/**
 * @author Lene Preuss <lene.preuss@gmail.com>
 */

class Tx_Newspaper_Validation_Validator_StoredObjectValidator extends Tx_Extbase_Validation_Validator_AbstractValidator {

    public function isValid($value) {
        $this->errors = array();
        if (!($value instanceof tx_newspaper_StoredObject)) {
            $this->addError(get_class($value) . ' is not a StoredObject', self::UNIQUE_ERROR_ID);
            return false;
        }
        return true;
    }

    const UNIQUE_ERROR_ID = 1344852983;
}
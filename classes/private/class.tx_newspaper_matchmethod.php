<?php
/**
 * Created by JetBrains PhpStorm.
 * User: lene
 * Date: 10/6/11
 * Time: 3:59 PM
 * To change this template use File | Settings | File Templates.
 */

class tx_newspaper_MatchMethod {

    public function __construct($method = '') {
        self::normalizeMethodString($method);

        self::checkMethodAllowed($method);

        $this->method_string = $method;
    }

    public function isOr() { return ($this->method_string == 'or'); }
    public function isAnd() { return ($this->method_string == 'and'); }
    public function isPhrase() { return ($this->method_string == 'phrase'); }

	////////////////////////////////////////////////////////////////////////////

    private static function normalizeMethodString(&$method) {
        $method = strtolower($method);
        if ($method == '') $method = 'phrase';
    }

    private static function checkMethodAllowed($method) {
        if (!self::isAllowedMethod($method)) {
            throw new tx_newspaper_IllegalUsageException(
                'Matching method "' . $method . '" not supported'
            );
        }
    }

    private static function isAllowedMethod($method) {
        return ($method == 'phrase' || $method == 'or' || $method == 'and');
    }

    private $method_string;

}

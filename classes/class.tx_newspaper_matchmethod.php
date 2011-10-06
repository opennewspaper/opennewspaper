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
        $method = strtolower($method);
        if ($method == '') $method = 'phrase';
        if (!$method == 'phrase' || $method == 'or') {
            throw new tx_newspaper_IllegalUsageException(
                'Matching method "' . $method . '" not supported'
            );
        }
        $this->method_string = $method;
    }

    public function isOr() { return ($this->method_string == 'or'); }
    public function isPhrase() { return ($this->method_string == 'phrase'); }

	////////////////////////////////////////////////////////////////////////////

    private $method_string;

}

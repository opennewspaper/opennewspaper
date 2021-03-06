<?php
/**
 * @author Lene Preuss <lene.preuss@gmail.com>
 */

class tx_newspaper_Diff {

    public function __construct($old_text, $new_text, $strip_tags = true) {

        $this->strip_tags = $strip_tags;

        try {
            $this->diff_representation = self::arrayDiff($this->textAsArray($old_text), $this->textAsArray($new_text));
        } catch (tx_newspaper_OutOfMemoryException $e) {
            $this->diff_representation = array(array('d' => $this->textAsArray($old_text), 'i' => $this->textAsArray($new_text)));
        }
# tx_newspaper::devlog('diff representation', $this->diff_representation);
    }

    public function textDiff() {
        return array_reduce($this->diff_representation, array($this, 'elementsToText'));
    }

    public function isDifferent() {
        foreach ($this->diff_representation as $element) {
            if (is_array($element)) return true;
        }
        return false;
    }

    ////////////////////////////////////////////////////////////////////////////

    private function textAsArray($text) {
        if ($this->strip_tags) $text = strip_tags($text, '<p>');
        $text = str_replace("\n", "<br /> ", $text);
        return array_filter(preg_split("/\s+/", $text), 'trim');
    }

    private static function arrayDiff(array $old, array $new){

        list($maxlen, $omax, $nmax) = self::findDifferenceData($old, $new);

        if($maxlen == 0) {
            if (self::isTextElement($old) || self::isTextElement($new)) {
                return array(array('d'=>$old, 'i'=>$new));
            }
            else return array('');
        }

    	return array_merge(
    		self::arrayDiff(array_slice($old, 0, $omax), array_slice($new, 0, $nmax)),
    		array_slice($new, $nmax, $maxlen),
    		self::arrayDiff(array_slice($old, $omax + $maxlen), array_slice($new, $nmax + $maxlen)));
    }

    private static function findDifferenceData($old, $new) {
        $matrix = new tx_newspaper_DiffMatrix;
        foreach ($old as $oindex => $ovalue) {
            foreach (array_keys($new, $ovalue) as $nindex) {
                $matrix->set($oindex, $nindex);
            }
        }
        return array($matrix->getMaxlen(), $matrix->getOMax(), $matrix->getNMax());
    }

    private static function elementsToText($already, $next) {
        if (!is_array($next)) return $already . ' ' . $next;
        return $already . ' ' . self::markedToText($next['d'], 'red') . self::markedToText($next['i'], 'green');
    }

    private static function markedToText(array $changed, $color) {
        return !empty($changed)? '<span style="color:' . $color . '">' . implode(' ', $changed) . "</span> ": '';
    }

    private static function isTextElement($old) { return is_array($old) && isset($old[0]) && $old[0]; }

    private $diff_representation = array();
    private $strip_tags = false;

}

class tx_newspaper_OutOfMemoryException extends tx_newspaper_Exception {
    public function __construct() {
        parent::__construct("Out of memory", false);
    }
}

class tx_newspaper_DiffMatrix {

    /** if less memory than that is left, throw a tx_newspaper_OutOfMemoryException */
    const memory_safety_margin = 67108864; // 64M

    public function set($oindex, $nindex) {

        if (self::getMaxMem() - memory_get_usage(true) < self::memory_safety_margin) throw new tx_newspaper_OutOfMemoryException();

        $this->matrix[$oindex][$nindex] = isset($this->matrix[$oindex - 1][$nindex - 1]) ? $this->matrix[$oindex - 1][$nindex - 1] + 1 : 1;
        if ($this->matrix[$oindex][$nindex] > $this->maxlen) {
            $this->maxlen = $this->matrix[$oindex][$nindex];
            $this->omax = $oindex + 1 - $this->maxlen;
            $this->nmax = $nindex + 1 - $this->maxlen;
        }
    }

    public function getMaxlen() { return $this->maxlen; }
    public function getOMax() { return $this->omax; }
    public function getNMax() { return $this->nmax; }

    static private function getMaxMem() {
        if (!self::$max_mem) {
            $max_mem = ini_get('memory_limit');
            switch (substr($max_mem, -1)) {
                case 'G': return intval($max_mem)*1024*1024*1024;
                case 'M': return intval($max_mem)*1024*1024;
                case 'K': return intval($max_mem)*1024;
            }
            self::$max_mem = intval($max_mem);
        }
        return self::$max_mem;
    }

    private $matrix = array(array());
    private $maxlen = 0;
    private $omax = 0;
    private $nmax = 0;

    private static $max_mem = 0;

}
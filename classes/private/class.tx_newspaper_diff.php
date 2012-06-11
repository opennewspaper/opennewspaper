<?php
/**
 * @author Lene Preuss <lene.preuss@gmail.com>
 */

class tx_newspaper_Diff {

    public function __construct($old_text, $new_text) {
        $this->diff_representation = self::arrayDiff(explode(' ', $old_text), explode(' ', $new_text));
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

    private static function arrayDiff(array $old, array $new){
    	foreach($old as $oindex => $ovalue){
    		$nkeys = array_keys($new, $ovalue);
    		foreach($nkeys as $nindex){
    			$matrix[$oindex][$nindex] = isset($matrix[$oindex - 1][$nindex - 1]) ?
    				$matrix[$oindex - 1][$nindex - 1] + 1 : 1;
    			if($matrix[$oindex][$nindex] > $maxlen){
    				$maxlen = $matrix[$oindex][$nindex];
    				$omax = $oindex + 1 - $maxlen;
    				$nmax = $nindex + 1 - $maxlen;
    			}
    		}
    	}
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

    private static function elementsToText($already, $next) {
        if (!is_array($next)) return $already . ' ' . $next;
        return $already . ' ' . self::markedToText($next['d'], 'red') . self::markedToText($next['i'], 'green');
    }

    private static function markedToText(array $changed, $color) {
        return !empty($changed)? '<span style="color:' . $color . '">' . implode(' ', $changed) . "</span> ": '';
    }

    private static function isTextElement($old) { return is_array($old) && isset($old[0]) && $old[0]; }

    private $diff_representation = array();

}

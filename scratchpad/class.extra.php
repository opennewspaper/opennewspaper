<?php
/*
 * Created on Aug 12, 2008
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
interface Extra {

	/// returns an actual member of the Extra
	function getField($fieldname);

	/// returns the definition of fields and their mapping to implementation fields
	/// for all supported Sources
	static private function getFields();
	
}

class ConcreteExtra implements Extra {
	function __construct($uid, $src = null) {
		/// Version A: $src is an intantiation of a Source class
		/*
		if ($src === null) { 
			$this->setSource(new DBSource('concrete_extra_table'));
		} else {
			$this->setSource($src);
		} 
		*/
		/// Version B: $src is a string which says which class to be instantiated as source
		$this->setSource(new $src($_GLOBALS['config_table']['ConcreteExtra']['Table']));	
				
		$this->uid = $uid;

		foreach (self::getFields() as $key => $representation) {
			if (is_array($representation)) {
				$this->instantiateExtras($key, $representation);
			} else if (starts_with('array:', $representation)) {
				$this->instantiateArray($key, substr(strlen('array:'),$representation));
			} else {
				$this->instantiateFields($key, $representation);
			}
		}
		
		/// you could move this block before the foreach loop. Then every displayable 
		/// Extra would need TWO DB accesses: first to determine if it's hidden, second
		///	to get the data. Whether it's better to first read ALL data and throw them 
		/// away if the extra is hidden, remains to be seen. 
		if (!$this->isDisplayable()) {
			$this->invalidate();
			return;	
		}
	}
	
	/** @return bool if this Extra may be seen currently */
	private function isDisplayable() {
		$this->field['hidden'] = Registry::getInstance()->getFields('hidden', $this->uid, $this->source);
		if ($this->field['hidden'] && 
			Registry::getInstance()->getDisplayMode() != 'preview') {
				return false;
			}
		return true;
	}
	/// public function to return object's properties
	function getField($key) {
		return $this->fields[$key];
	}
	
	/// return list of properties this object has and their representations
	static private function getFields() {
		return $this->fields[$this->source];
	}

	/// factored out of constructor for clarity	
	private function instantiateFields($key, $representation) {
		$this->field[$key] = Registry::getInstance()->getFields($representation, $this->uid, $this->source);
	}

	/// factored out of constructor for clarity	
	private function instantiateArray($key, $representation) {
		$this->field[$key] = Registry::getInstance()->getArray($representation, $this->uid, $this->source);
	}
	
	/// factored out of constructor for clarity	
	private function instantiateExtras($key, $representation) {
		$this->contained_extras = array_merge(
			$this->contained_extras,
			Registry::getInstance()->getExtras($representation, $this->uid, $this->source));
	}
	
	static private $fieldsTemplate = array(
		'db-source' => array(
			'Titel' => 'article_title',
			'Links' => 'array:link_2_concrete_extra',
			'ExtraInside' => array (
				'ExtraType' => 'LinkBox',
				'WantedFields' => 'Title, NumLinks'
			)
		)
	);
	
	private $uid;
	private $source;
	private $fields;
	
	private $contained_extras;
	
}

Registry::getInstance()->setDisplayMode('preview');

$ex = new ConcreteExtra(1);
$ex->getField('Titel');

$reg->getField(...)	// NEIN! BOESE!
$ex->getField(...)	//	JA!
?>

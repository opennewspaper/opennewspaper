<?php
/**
 * Created by JetBrains PhpStorm.
 * User: lene
 * Date: 12/12/11
 * Time: 5:21 PM
 * To change this template use File | Settings | File Templates.
 */

abstract class tx_newspaper_HTMLContainer {

    public function close() {
        $this->checkClosed('Tried to close a ' . get_class($this) .' that is already closed');
        $this->text .= $this->closeString();
        $this->is_closed = true;
        return $this->text;
    }

    public function getHTML() {
        $text = $this->text;
        if (!$this->is_closed) $text .= $this->closeString();
        return $text;
    }

    abstract protected function closeString();

    protected  function checkClosed($message) {
        if (!$this->is_closed) return;
        throw new tx_newspaper_IllegalUsageException($message);
    }

    protected $is_closed = false;

    protected $text = '';

}

class tx_newspaper_TableRenderer extends tx_newspaper_HTMLContainer {

    public function __construct($attributes = '') {
        $this->text = "<table $attributes>\n";
    }

    public function addRow(array $content, $attributes = '') {
        $row = new tx_newspaper_TableRow($content, $attributes);
        $this->text .= $row->close();
        $this->row_number++;
    }

    protected function closeString() { return "</table>\n"; }

    private $row_number = 0;


}

class tx_newspaper_TableRow extends tx_newspaper_HTMLContainer {

    public function __construct(array $content = array(), $attributes = '') {
        $this->text .= "<tr $attributes>";
        $this->addCells($content);
    }

    public function addCells($content) {
        foreach ($content as $field) {
            $this->addCell($field);
        }
    }

    public function addCell($text, $attributes = '') {
        $this->checkClosed('Tried to add to a row after it was closed');
        $cell = new tx_newspaper_TableCell($text, $attributes);
        $this->text .= $cell;
    }

    protected function closeString() { return "</tr>\n"; }

}

class tx_newspaper_TableCell {

    public function __construct($text = '', $attributes = '') {
        $this->text .= "<td  $attributes>$text</td>";
    }

    public function __toString() {
        return $this->text;
    }

    private $text;

}
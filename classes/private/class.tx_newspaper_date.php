<?php
/**
 * Created by JetBrains PhpStorm.
 * User: lene
 * Date: 4/6/11
 * Time: 15:13 PM
 * To change this template use File | Settings | File Templates.
 */

class tx_newspaper_Date {

    public function __construct($year = 0, $month = 1, $day = 1) {
        $this->year = $year;
        $this->month = $month;
        $this->day = $day;
    }

    public function getTimestamp() {
        if (!$this->year) return 0;
        return mktime(0, 0, 0, $this->month, $this->day, $this->year);
    }

    private $year;
    private $month;
    private $day;
}

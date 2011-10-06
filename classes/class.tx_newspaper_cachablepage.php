<?php

/// A structure for a newspaper object representable as a combination of GET-parameters
class tx_newspaper_CachablePage {

    public function __construct(tx_newspaper_Page $page,
                                tx_newspaper_article $article = null,
                                $additional_parameters = array()) {
        $this->newspaper_page = $page;
        $this->newspaper_article = $article;
        $this->get_parameters = $additional_parameters;

    }

    public function __toString() {
        $string = $this->newspaper_page->__toString();
        if ($this->newspaper_article) $string .= $this->newspaper_article->__toString();
        return $string;
    }

    public function equals(tx_newspaper_CachablePage $other) {
        if ($this->getNewspaperPage()->getUid() != $other->getNewspaperPage()->getUid()) return false;
        if ($this->newspaper_article) {
            return ($other->newspaper_article &&
                    ($this->newspaper_article->getUid() == $other->newspaper_article->getUid()));
        }
        if ($other->newspaper_article) return false;
        return true;
    }

    public function getNewspaperPage() {
        return $this->newspaper_page;
    }

    public function getGETParameters() {

        $parameters = array(
            'id' => $this->getTypo3PageID(),
        );
        if ($this->newspaper_article) {
            $parameters[tx_newspaper::article_get_parameter] = $this->newspaper_article->getUid();
        }
        if ($this->get_parameters) {
            $parameters = array_merge($parameters, $this->get_parameters);
        }

        /// \todo page type
        $type = $this->newspaper_page->getPageType();
#        t3lib_div::devlog('getGETParameters',$type->getCondition());

        return $parameters;
    }

    public function getURL() {
        throw new tx_newspaper_NotYetImplementedException();
    }

    public function getTypo3PageID() {
        if (!$this->newspaper_page) {
            throw new tx_newspaper_IllegalUsageException(
                'tx_newspaper_CachablePage::getTypo3Page() called without a Newspaper page'
            );
        }
        return $this->newspaper_page->getTypo3PageID();
    }

    public function getStarttime() {
        if ($this->starttime) return $this->starttime;
        if (is_null($this->newspaper_article)) return 0;
        return $this->newspaper_article->getAttribute('starttime');
    }

    public function setStarttime($starttime) {
        $this->starttime = $starttime;
    }

    public function getEndtime() {
        if ($this->endtime) return $this->endtime;
        if (is_null($this->newspaper_article)) return 0;
        return $this->newspaper_article->getAttribute('endtime');
    }

    public function setEndtime($endtime) {
        $this->endtime = $endtime;
    }

    ////////////////////////////////////////////////////////////////////////////

    private $newspaper_page = null;
    private $newspaper_article = null;
    private $get_parameters = array();
    private $starttime = 0;
    private $endtime = 0;

}

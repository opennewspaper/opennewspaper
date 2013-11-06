<?php
/**
 * @author: Lene Preuss <lene.preuss@gmail.com>
 */

/**
 *  Representation for the text paragraphs, extras and spacing between the paragraphs of an article
 */
class tx_newspaper_ArticleTextParagraphs {

    /**
     * @param tx_newspaper_Article $article The article whose text is represented
     * @param string $wrap_open opening string for wrapping every paragraph
     * @param string $wrap_close closing string for wrapping every paragraph
     */
    public function __construct(tx_newspaper_Article $article, $wrap_open = '<p class="bodytext">', $wrap_close = "</p>\n") {

        self::$wrap_open = $wrap_open;
        self::$wrap_close = $wrap_close;

        $this->extras = $article->getExtras();

        $this->populateTextParagraphs($article);

        $this->assembleParagraphs();

        $this->addExtrasWithBadParagraphNumbers();
    }

    public function toArray() { return $this->paragraphs; }

    ////////////////////////////////////////////////////////////////////////////

    private function populateTextParagraphs(tx_newspaper_Article $article) {
        $text = self::filterUnprintableCharacters($article->getAttribute('bodytext'));
        $this->text_paragraphs = array_map(
            'tx_newspaper_ArticleTextParagraphs::convertRTELinks',
            self::splitIntoParagraphs($text)
        );
    }

    private function assembleParagraphs() {
        foreach ($this->text_paragraphs as $text_paragraph) {

            if (trim($text_paragraph)) {
                $this->paragraphs[] = $this->assembleParagraph($text_paragraph);
            } else {
                //  empty paragraph, increase spacing value to next paragraph
                $this->spacing++;
            }
        }
    }

    private function assembleParagraph($text_paragraph) {
        $paragraph = array(
            'text' => $text_paragraph,
            'spacing' => intval($this->spacing)
        );
        $this->spacing = 0;

        foreach ($this->extras as $extra) {
            if ($extra->getAttribute('paragraph') == sizeof($this->paragraphs) ||
                sizeof($this->text_paragraphs) + $extra->getAttribute('paragraph') == sizeof($this->paragraphs)
            ) {
                $paragraph['extras'][$extra->getAttribute('position')] = self::makeParagraphRepresentationFromExtra($extra);
            }
        }

        /*  Braindead PHP does not sort arrays automatically, even if the keys are integers.
         *  So if you, e.g., insert first $a[4] and then $a[2], $a == array ( 4 => ..., 2 => ...).
         *  Thus, you must call ksort.
         */
        if ($paragraph['extras']) ksort($paragraph['extras']);
        return $paragraph;
    }

    /** Make sure all extras are rendered, even those whose \c paragraph
     *  attribute is greater than the number of text paragraphs or less
     *  than its negative.
     */
    private function addExtrasWithBadParagraphNumbers() {
        $number_of_text_paragraphs = sizeof($this->text_paragraphs);
        foreach ($this->extras as $extra) {
            if ($extra->getAttribute('paragraph') + $number_of_text_paragraphs < 0) {
                $this->paragraphs[0]['extras'][intval($extra->getAttribute('position'))] =
                    self::makeParagraphRepresentationFromExtra($extra);
            } else if ($extra->getAttribute('paragraph') >= $number_of_text_paragraphs) {
                $this->paragraphs[sizeof($this->paragraphs)]['extras'][intval($extra->getAttribute('position'))] =
                    self::makeParagraphRepresentationFromExtra($extra);
            }
        }
    }

    private static function filterUnprintableCharacters($text) {
        return preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $text);
    }


    /// Split the article's text into an array, one entry for each paragraph
    /**
     *  tx_newspaper_Extra are inserted before or after paragraphs. This function splits
     *  the article text so the position of a tx_newspaper_Extra can be found.
     *
     *  The functionality of this function depends on the way the RTE stores line breaks.
     *  Currently it breaks the text at \c "<p>/</p>" -pairs and also at line breaks \c ("\n").
     *
     *  @attention If the format of line breaks changes, this function must be altered.
     */
    private static function splitIntoParagraphs($text) {
        /** A text usually starts with a \c "<p>", in that case the first paragraph must be
         *  removed. It may not be the case though, if so, the first paragraph is meaningful
         *  and must be kept.
         */
        $temp_paragraphs = preg_split('/<p[\s>]/', $text);
        $paragraphs = array();
        foreach ($temp_paragraphs as $paragraph) {

            $paragraph = self::trimPTags($paragraph);

            /// Split the paragraph at line breaks.
            $sub_paragraphs = explode("\n", $paragraph);

            /// Store the pieces in one flat array.
            foreach ($sub_paragraphs as $sub_paragraph)
                $paragraphs[] = $sub_paragraph;
        }

        return $paragraphs;
    }

    /**
     *  convertRteField wraps paragraphs in <p></p> again. This function removes
     *  the p's while keeping the link conversion.
     */
    private static function convertRTELinks($paragraph) {
        $paragraph = tx_newspaper::convertRteField($paragraph);
        if (!(substr($paragraph, 0, 3) != '<p>' && substr($paragraph, 0, 3) != '<p ')) {
            $paragraph = self::trimPTags(substr($paragraph, 2));
        }
        return self::wrapParagraph($paragraph);
    }

    private static function wrapParagraph($paragraph) {
        if (self::isHeader($paragraph)) return $paragraph;
        return self::$wrap_open . $paragraph . self::$wrap_close;
    }

    /**
     * @param $paragraph
     * @return bool
     */
    private static function isHeader($paragraph) {
        return (bool)preg_match('#^<(.*)>(.*)</(.*)>$#', trim($paragraph));
    }

    /// Remove the rest of the \c "<p>" - tag from every line.
    private static function trimPTags($paragraph) {
        $paragraph = self::trimLeadingKet($paragraph);

        /** Each paragraph now should end with a \c "</p>". If it doesn't, the
         *  text is not well-formed. In any case, we must remove the \c "</p>".
         */
        $paragraph = str_replace('</p>', '', $paragraph);

        return $paragraph;
    }

    private static function trimLeadingKet($paragraph) {
        $paragraph_start = strpos($paragraph, '>');
        if ($paragraph_start !== false) {
            if ($paragraph_start <= 1 || self::startsWithHTMLAttribute($paragraph)) {
                $paragraph = substr($paragraph, $paragraph_start + 1);
            }
        }
        $paragraph = trim($paragraph);

        return $paragraph;
    }

    private function startsWithHTMLAttribute($paragraph) {
        return preg_match('/^\w+="\w+">/', trim($paragraph));
    }

    private static function makeParagraphRepresentationFromExtra(tx_newspaper_Extra $extra) {
        return array(
            'extra_name' => $extra->getTable(),
            'content' => $extra->render()
        );
    }

    private $paragraphs = array();

    private $spacing = 0;
    /** @var tx_newspaper_Extra[] */
    private $extras = array();
    private $text_paragraphs = array();

    private static $wrap_open;
    private static $wrap_close;

}

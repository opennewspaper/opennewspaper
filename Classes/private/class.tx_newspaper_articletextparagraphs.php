<?php
/**
 * @author: Lene Preuss <lene.preuss@gmail.com>
 */

/**
 *  Representation for the text paragraphs, extras and spacing between the paragraphs of an article
 */
class tx_newspaper_ArticleTextParagraphs {

    public function __construct(array $text_paragraphs, tx_newspaper_Article $article) {
        $this->article = $article;
        foreach ($text_paragraphs as $paragraph) {
            $this->text_paragraphs[] = self::convertRTELinks($paragraph);
        }

        $this->assembleTextParagraphs();

        $this->addExtrasWithBadParagraphNumbers();
    }

    public function toArray() { return $this->paragraphs; }

    ////////////////////////////////////////////////////////////////////////////

    private function assembleTextParagraphs() {
        foreach ($this->text_paragraphs as $text_paragraph) {

            if (trim($text_paragraph)) {
                $this->paragraphs[] = $this->assembleTextParagraph($text_paragraph);
            } else {
                //  empty paragraph, increase spacing value to next paragraph
                $this->spacing++;
            }
        }
    }

    private function assembleTextParagraph($text_paragraph) {
        $paragraph = array(
            'text' => $text_paragraph,
            'spacing' => intval($this->spacing)
        );
        $this->spacing = 0;

        foreach ($this->article->getExtras() as $extra) {
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
        foreach ($this->article->getExtras() as $extra) {
            if ($extra->getAttribute('paragraph') + $number_of_text_paragraphs < 0) {
                $this->paragraphs[0]['extras'][intval($extra->getAttribute('position'))] =
                    self::makeParagraphRepresentationFromExtra($extra);
            } else if ($extra->getAttribute('paragraph') >= $number_of_text_paragraphs) {
                $this->paragraphs[sizeof($this->paragraphs)]['extras'][intval($extra->getAttribute('position'))] =
                    self::makeParagraphRepresentationFromExtra($extra);
            }
        }
    }

    /**
     *  convertRteField wraps paragraphs in <p></p> again. This function removes
     *  the p's while keeping the link conversion.
     */
    private static function convertRTELinks($paragraph) {
        $paragraph = tx_newspaper::convertRteField($paragraph);
        if (substr($paragraph, 0, 3) != '<p>' && substr($paragraph, 0, 3) != '<p ') return $paragraph;
        return tx_newspaper_Article::trimPTags(substr($paragraph, 2));
    }

    private static function makeParagraphRepresentationFromExtra(tx_newspaper_Extra $extra) {
        return array(
            'extra_name' => $extra->getTable(),
            'content' => $extra->render()
        );
    }

    private $paragraphs = array();
    private $spacing = 0;
    /** @var tx_newspaper_Article */
    private $article = null;
    private $text_paragraphs = array();

}

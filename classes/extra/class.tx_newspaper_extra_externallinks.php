<?php

require_once(PATH_typo3conf . 'ext/newspaper/classes/class.tx_newspaper_extra.php');

/// An HTML link with a text, a target URL and a target frame
/** These links are intended for pages outside of Typo3/newspaper, whose text
 *  can not be deduced by other means. Of course, they can be used for internal
 *  links just as well.
 *
 *  \todo Instead of explicit member variables, just use an attributes array.
 *  \todo Use tx_newspaper::getTable() instead of tx_newspaper_ExternalLink::$table
 */
class tx_newspaper_ExternalLink {

	///	Create a tx_newspaper_ExternalLink from a DB record
	public function __construct($uid) {

		$row = tx_newspaper::selectOneRow(
			'*', self::table, 'uid = ' . intval($uid)
		);

		$this->text = $row['text'];
		$this->url = $row['url'];
	}

	public function __toString() {
		return $this->url . ' ' . $this->text;
	}

	/// \return The text displayed under the link
	public function getText() {
		return $this->text? $this->text: $this->url;
	}

	/// \return The URL pointed to
	public function getURL() {
		$temp_params = explode(' ', $this->url);

        if (strpos($temp_params[0], 'http://') !== false) {
			$href = $temp_params[0];
        } else {
			$href = 'http://' . $temp_params[0];
        }

		return $href;
	}

	/// \return The target frame
	public function getTarget() {
		$temp_params = explode(' ', $this->url);
		if (sizeof($temp_params) > 0) {
			unset($temp_params[0]);
			foreach ($temp_params as $param) {
				if ($param) {
					$target = trim($param);
					break;
				}
			}
		}
		return $target;
	}

	////////////////////////////////////////////////////////////////////////////

	private $text = null; 		///< The text displayed under the link
	private $url = null;		///< The URL pointed to

	/// SQL table for persistence
	const table = 'tx_newspaper_externallinks';
}

///	An Extra displaying a list of HTML links pointing to external sources
/** These links are intended for pages outside of Typo3/newspaper, whose text
 *  can not be deduced by other means. Of course, they can be used for internal
 *  links just as well.
 *
 *  Insert this Extra in a Page Zone or an Article wherever a list of Links is
 *  wanted.
 *
 *  Attributes:
 *  - \p pool (bool)
 *  - \p title (string)
 *  - \p links (UIDs of tx_newspaper_ExternalLink)
 *  - \p template (string)
 */
class tx_newspaper_Extra_ExternalLinks extends tx_newspaper_Extra {

	/// Create a tx_newspaper_Extra_ExternalLinks
	public function __construct($uid = 0) {
		if ($uid) {
			parent::__construct($uid);
		}
	}

	/// Convert object to string to make it visible in stack backtraces, devlog etc.
	public function __toString() {
		try {
		return 'Extra: UID ' . $this->getExtraUid() . ', External Links Extra: UID ' . $this->getUid() .
				' (Links: ' . $this->getAttribute('links') . ')';
		} catch(Exception $e) {
			return "External Links: Exception thrown!" . $e;
		}
	}

	/// Display the list of links
	/** Smarty template:
	 *  \include res/templates/tx_newspaper_extra_externallinks.tmpl
	 */
	public function render($template_set = '') {

		$this->prepare_render($template_set);

		if ($this->getAttribute('template')) $template = $this->getAttribute('template');
		else $template = $this;

		$this->smarty->assign('title', $this->getAttribute('title'));
		$this->smarty->assign('links', $this->getLinks());

		return $this->smarty->fetch($this->getSmartyTemplate());
	}

	/// A description to identify the link list in the BE, shows the link texts
	public function getDescription() {
		$sep = $ret = '';
		foreach ($this->getLinks() as $link) {
			$ret .= $sep . $link->getText();
			$sep = ', ';
		}
		return $this->getAttribute('short_description') . '<br />' . $ret;
	}

	/// Title for module/SysFolder
	public static function getModuleName() {
		return 'np_extlinks';
	}

	///	This Extra may be different for every article
	public static function dependsOnArticle() { return true; }

	////////////////////////////////////////////////////////////////////////////

	/// Return and (if needed) read the links displayed in this list
	private function getLinks() {
		if (!$this->links) {
			try {
				foreach (explode(',', trim($this->getAttribute('links'))) as $link_uid) {
					if ($link_uid) {
						$this->links[] = new tx_newspaper_ExternalLink($link_uid);
					}
				}
			} catch (tx_newspaper_DBException $e) { }
		}

		return $this->links;
	}

	////////////////////////////////////////////////////////////////////////////

	private $links = array();	///< The links displayed in this list

}

tx_newspaper_Extra::registerExtra(new tx_newspaper_Extra_ExternalLinks());

?>
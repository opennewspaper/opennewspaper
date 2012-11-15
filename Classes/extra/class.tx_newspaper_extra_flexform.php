<?php

require_once(PATH_typo3conf . 'ext/newspaper/Classes/class.tx_newspaper_extra.php');

/// tx_newspaper_Extra that renders
/** This Extra is used to render Flexform based content
 */

class tx_newspaper_Extra_Flexform extends tx_newspaper_Extra {

	const description_length = 50;

    // Flexform folder types
    const FF_TYPE_ALL = 1;
    const FF_TYPE_ACTIVE = 2;
    const FF_TYPE_ARCHIVED = 3;

    private static $flexformFolderActive = null;
    private static $flexformFolderArchive = null;

    private $flexformArray = null;



	/// Constructor
	public function __construct($uid = 0) {
		if ($uid) {
			parent::__construct($uid);
		}
	}

	public function __toString() {
		try {
			return 'Extra: UID ' . $this->getExtraUid() . ', Flexform: UID ' . $this->getUid() . t3lib_div::view_array($this->getFlexformValues());
		} catch(Exception $e) {
			return "Flexform: Exception thrown!" . $e;
		}
	}

	/** Render flexform based content.
	/*  Smarty template:
	 *  @include res/templates/tx_newspaper_extra_flexform.tmpl
	 */
	public function render($template_set = '') {

        $this->prepare_render($template_set);

        $this->smarty->assign('ds_file', array(
                'path' => self::getFlexformFolder(self::getFlexformDataStructureType($this->getAttribute('ds_file'))),
                'file' => $this->getAttribute('ds_file') . '.xml')
        );

        $this->smarty->assign('flexform', $this->getFlexformValues());
        $this->smarty->assign('flexform_debug', array(
            'xml' => htmlentities($this->getAttribute('flexform')),
            'array' => t3lib_div::view_array($this->getFlexformValues())
        ));

        $rendered = $this->smarty->fetch($this->getSmartyTemplate());
// tx_newspaper::devlog("Render Extra Flexform", $rendered);
        return $rendered;
	}

    /**
     * Get template name (or return template set in record)
     * Name: tx_newspaper_extra_flexform_[name of data structure xml file (omitting .xml)].tmpl
     * @return string Name of Smarty template to be used
     */
    protected function getSmartyTemplate() {
        try {
            $template = trim($this->getAttribute('template'));
        } catch (tx_newspaper_WrongAttributeException $e) { }

        if (!empty($template)) {
            return $template; // Template file was set in Extra record, so use that template
        }

        // No template set in Extra, so use template for Extra flexform based on the data structure xml file name
        return strtolower(get_class($this)) . '_' . $this->getAttribute('ds_file') . '.tmpl';

    }



    public function getDescription() {
		if ($desc = $this->getAttribute('short_description')) {
		} elseif ($desc = $this->getAttribute('notes')) {
		} else {
            $desc = $this->getAttribute('ds_file');
		}
		return substr(
			$desc,
			0, self::description_length + 2*strlen('<strong>') + 1
        );
	}

		/// title for module
	public static function getModuleName() {
		return 'np_extra_flexform';
	}

	public static function dependsOnArticle() { return false; }



    private function getFlexformValues() {

        if ($this->flexformArray !== null) {
            return $this->flexformArray;
        }

        require_once(PATH_tslib . 'class.tslib_pibase.php');
        $pi = new tslib_pibase(); // Get pibase object, needed for flexform functions

        // Read current data in $pi
        $pi->cObj->data = $pi->pi_getRecord('tx_newspaper_extra_flexform', $this->getUid());

        $pi->pi_initPIflexForm('flexform'); // Convert xml data stored in record into an array

        $this->flexformArray = $pi->cObj->data['flexform']; // Cache array

        return $this->flexformArray;

    }



    /// Flexform template functions

    /**
     * Get flexform data structure for $file
     * Checks active folder first, then archive folder
     * @param $file String Filename (.xml will be appended!)
     * @return string XML file contents
     */
    public static function getFlexformDataStructure($file) {

        $type = self::getFlexformDataStructureType($file);

        if ($type == self::FF_TYPE_ACTIVE) {
            return file_get_contents(self::getFlexformActiveFolder() . '/' . $file . '.xml');
        } else if ($type == self::FF_TYPE_ARCHIVED) {
            return file_get_contents(self::getFlexformArchiveFolder() . '/' . $file . '.xml');
        }

        return ''; // File not found/readable ...

    }


    /**
     * Get flexform template files for given $type
     * @param $type int Either FF_TYPE_ACTIVE or FF_TYPE_ARCHIVED
     * @return array Flexform template files
     */
    private static function getFlexformFolderTemplates($type) {

        $files = array();

        switch(intval($type)) {
            case self::FF_TYPE_ACTIVE:
                $folder = self::getFlexformActiveFolder();
                break;
            case self::FF_TYPE_ARCHIVED:
                $folder = self::getFlexformArchiveFolder();
                break;
            default:
                t3lib_div::devlog('Unknown Flexform type in getFlexformFolderFiles()' ,'newspaper', 3, array('type' => $type));
        }

        if (!is_dir($folder)) {
            $message = t3lib_div::makeInstance('t3lib_FlashMessage', sprintf(tx_newspaper::getTranslation('flashMessage_extra_flexform_folder_not_found'), $folder), 'Flexform', t3lib_FlashMessage::ERROR);
            t3lib_FlashMessageQueue::addMessage($message);
            return array();
        }

        // List all xml files in folder
        $dir = dir($folder);
        while (($file = $dir->read()) !== false) {
            $parts = pathinfo($folder . '/' . $file);
            if (is_readable($folder . '/' . $file) && $parts['extension'] == 'xml') {
                $files[] = $parts['filename'];
            }
        }
      	$dir->close();

        return $files;
    }


    /**
     * Get active templates
     * @return array Active flexform template files
     */
    private static function getFlexformActiveFolderTemplates() {
        return self::getFlexformFolderTemplates(self::FF_TYPE_ACTIVE);
    }


    /**
     * Get archived templates
     * @return array Archived flexform template files
     */
    private static function getFlexformArchivedFolderTemplates() {
        return self::getFlexformFolderTemplates(self::FF_TYPE_ARCHIVED);
    }


    /**
     * Get type of flexform template
     * Either FF_TYPE_ACTIVE (checked first) or FF_TYPE_ARCHIVED (checked then)
     * So if a file exists in both the active AND the archive folder, the active template will be used
     * @param $file String Flexform datastructure file
     * @return int FF_TYPE_ACTIVE or FF_TYPE_ARCHIVED or false, if file can't be found/read
     */
    public static function getFlexformDataStructureType($file) {
        // Check active folder first ...
        if (file_exists(self::getFlexformActiveFolder() . '/' . $file . '.xml') && is_readable(self::getFlexformActiveFolder() . '/' . $file . '.xml')) {
            return self::FF_TYPE_ACTIVE;
        }
        // ... and then archive folder
        if (file_exists(self::getFlexformArchiveFolder() . '/' . $file . '.xml') && is_readable(self::getFlexformArchiveFolder() . '/' . $file . '.xml')) {
            return self::FF_TYPE_ARCHIVED;
        }
        return false; // File not found, so no type ...
    }


    /**
     * Get folder as configured in Page TSConfig on newspaper root folder for given $type
     * newspaper.extra.flexform.folder.active = [path]
     * newspaper.extra.flexform.folder.archive = [path]
     * @param $type int Either FF_TYPE_ACTIVE or FF_TYPE_ARCHIVED
     * @return String Path (or empty string if not configured)
     */
    private static function getFlexformFolder($type) {

        // Read TSConfig from newspaper root folder
        $TSConfig = t3lib_BEfunc::getPagesTSconfig(tx_newspaper_Sysfolder::getInstance()->getPidRootfolder());

        switch(intval($type)) {
            case self::FF_TYPE_ACTIVE:
                if (self::$flexformFolderActive === null) {
                    self::$flexformFolderActive = $TSConfig['newspaper.']['extra.']['flexform.']['folder.']['active'];
                }
                return self::$flexformFolderActive;
                break;
            case self::FF_TYPE_ARCHIVED:
                if (self::$flexformFolderArchive === null) {
                    self::$flexformFolderArchive = $TSConfig['newspaper.']['extra.']['flexform.']['folder.']['archive'];
                }
                return self::$flexformFolderArchive;
                break;
            default:
                t3lib_div::devlog('Unknown Flexform type in getFlexformFolder()' ,'newspaper', 3, array('type' => $type));
        }

        return '';
    }


    /**
     * Get path to path with ACTIVE Extra flexform data structure files
     * These templates will be listed in the Flexform backend
     * Page TSConfig, must be set on newspaper root folder
     * This setting is mandatory
     * newspaper.extra.flexform.folder.active = [path]
     * @return String path
     */
    private static function getFlexformActiveFolder() {
        return self::getFlexformFolder(self::FF_TYPE_ACTIVE);
    }


    /**
     * Get path to path with ARCHIVED Extra flexform data structure files
     * Page TSConfig, must be set on newspaper root folder
     * This setting is optional
     * newspaper.extra.flexform.folder.active = [path]
     * @return String path
     */
    private static function getFlexformArchiveFolder() {
        return self::getFlexformFolder(self::FF_TYPE_ARCHIVED);
    }



    /// Backend functions

    /**
     * Fill dropdown for flexform data structure xml files in Extra flexform
     * Current value might be an archived flexform template. In that case the entry is merged into the array.
     * $TCA['tx_newspaper_extra_flexform']['columns']['ds_file']['config']['itemsProcFunc'] = 'tx_newspaper_Extra_Flexform->addFlexformTemplateDropdownEntries';
     * @param $params Params array (call by reference!)
     * @param $pObj t3lib_TCEforms Parent object
     * @return void
     */
    public function addFlexformTemplateDropdownEntries(&$params, &$pObj) {
//t3lib_div::debug($params);

        $files = array();

        if ($folder = self::getFlexformActiveFolder()) {
            $files = self::getFlexformActiveFolderTemplates();
        } else {
            $message = t3lib_div::makeInstance('t3lib_FlashMessage', tx_newspaper::getTranslation('flashMessage_extra_flexform_no_folder_configured'), 'Flexform', t3lib_FlashMessage::ERROR);
            t3lib_FlashMessageQueue::addMessage($message);
        }

        // Check if chosen template is an archived template
        if ($params['row']['ds_file']) {
            $type = self::getFlexformDataStructureType($params['row']['ds_file']);
            if ($type === false) {
                /// Stored file not found in file system
                $message = t3lib_div::makeInstance('t3lib_FlashMessage', sprintf(tx_newspaper::getTranslation('flashMessage_extra_flexform_file_not_found'), $params['row']['ds_file']), 'Flexform', t3lib_FlashMessage::ERROR);
                t3lib_FlashMessageQueue::addMessage($message);
            } else if ($type == self::FF_TYPE_ARCHIVED) {
                $message = t3lib_div::makeInstance('t3lib_FlashMessage', tx_newspaper::getTranslation('flashMessage_extra_flexform_archived'), 'Flexform', t3lib_FlashMessage::INFO);
                t3lib_FlashMessageQueue::addMessage($message);
                $files = array($params['row']['ds_file']); // Add selected flexform file to dropdown
            } // So file is an active template, has been read already
        }

        // Fill $params array for TCEForms
        $params['items'][] = array('0' => '', '1' => ''); // Empty entry
        foreach($files as $file) {
            $params['items'][] = array('0' => $file, '1' => $file);
        }

    }


    /**
     * Typo3 hook, see class.t3lib_tceforms.php
     * @param $table
     * @param $field
     * @param $row
     * @param $altName
     * @param $palette
     * @param $extra
     * @param $pal
     * @param $pObj
     */
    public static function getSingleField_preProcess($table, $field, $row, $altName, $palette, $extra, $pal, $pObj) {
        self::modifyFlexformTca($table, $field, $row);
    }

    /**
     * Reads Flexform data structure from file set in dropdown "ds_file"
     * Hides Flexform field if no data structure is selected
     * Manipulates $TCA
     * @param $table
     * @param $field
     * @param $row
     * @return void
     */
    private static function modifyFlexformTca($table, $field, $row) {
        if ($table == 'tx_newspaper_extra_flexform' && $field == 'ds_file' && !$row['ds_file']) {
            // Hide Flexform when no data structure was selected
            unset($GLOBALS['TCA']['tx_newspaper_extra_flexform']['columns']['flexform']);
        }
        if ($table == 'tx_newspaper_extra_flexform' && $field == 'flexform') {
            $GLOBALS['TCA']['tx_newspaper_extra_flexform']['columns']['flexform']['config']['ds']['default'] = self::getFlexformDataStructure($row['ds_file']);
        }
    }


}

tx_newspaper_Extra::registerExtra(new tx_newspaper_Extra_Flexform());

?>
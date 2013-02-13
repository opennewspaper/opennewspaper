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
			return 'Extra: UID ' . $this->getExtraUid() . ', Flexform: UID ' . $this->getUid() . t3lib_utility_Debug::viewArray($this->getFlexformValues());
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
                'path' => dirname($this->getAttribute('ds_file')),
                'file' => self::extractDsFileTitle($this->getAttribute('ds_file'))
            )
        );

        $this->smarty->assign('flexform', $this->getFlexformValues());

        $this->smarty->assign('flexform_debug', array(
            'xml' => htmlentities($this->getAttribute('flexform')),
            'array' => t3lib_utility_Debug::viewArray($this->getFlexformValues())
        ));

        $rendered = $this->smarty->fetch($this->getSmartyTemplate());
//tx_newspaper::devlog("Render Extra Flexform", array('smarty template' => $this->getSmartyTemplate(), 'ds_file' => array('path' => dirname($this->getAttribute('ds_file'))), 'file' => self::extractDsFileTitle($this->getAttribute('ds_file')), 'flexform data' => $this->getFlexformValues(), 'rendered' => $rendered));
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
        return strtolower(get_class($this)) . '_' . self::extractDsFileTitle($this->getAttribute('ds_file')) . '.tmpl';

    }



    public function getDescription() {
		if ($desc = $this->getAttribute('short_description')) {
		} elseif ($desc = $this->getAttribute('notes')) {
		} else {
            $desc =  self::extractDsFileTitle($this->getAttribute('ds_file'));// Show file name only (cut off xml file extension)
		}
		return substr($desc, 0, self::description_length + 2*strlen('<strong>') + 1);
	}

		/// title for module
	public static function getModuleName() {
		return 'np_extra_flexform';
	}

	public static function dependsOnArticle() { return false; }


    /**
     * Get data stored in flexform xml as array
     * @return array Flexform data
     */
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

        switch(self::getFlexformDataStructureType($file)) {
            case self::FF_TYPE_ACTIVE:
                return file_get_contents($file);
            case self::FF_TYPE_ARCHIVED:
                return file_get_contents($file);
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
                $files[] = $folder . '/'. $parts['filename'];
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
     * @param $file String Flexform data structure file (full path)
     * @return int FF_TYPE_ACTIVE or FF_TYPE_ARCHIVED or false, if file can't be found/read
     */
    public static function getFlexformDataStructureType($file) {

        // Check active folder first ...
        if (file_exists($file) && is_readable($file)) {
            return self::FF_TYPE_ACTIVE;
        }

        // ... and then archive folder
        // Switch flexform template path to archived folder
        $file = self::getFlexformArchiveFolder() . '/' . self::extractDsFileTitle($file) . '.xml';
        if (file_exists($file) && is_readable($file)) {
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

    /**
     * Extract title for flexform data structure file (strips path and xml file extensions)
     * Example: /this/is/an/example/dummy.xml -> dummy
     * @param $file string File name (may include the path)
     * @return string Data structure file title
     */
    public static function extractDsFileTitle($file) {
        $tmp = pathinfo($file);
        return $tmp['filename']; // Show file name only (cut off xml file extension)
    }


    /// Backend functions

    /**
     * Fill dropdown for flexform data structure xml files in Extra flexform.
     * Current value might be an archived flexform template. In that case the entry is merged into the array.
     * $TCA['tx_newspaper_extra_flexform']['columns']['ds_file']['config']['itemsProcFunc'] = 'tx_newspaper_Extra_Flexform->addFlexformTemplateDropdownEntries';
     * @param $params array Params (call by reference!)
     * @param $pObj t3lib_TCEforms Parent object
     * @return void
     */
    public function addFlexformTemplateDropdownEntries(&$params, &$pObj) {
//t3lib_div::devlog('addFlexformTemplateDropdownEntries', 'newspaper', 0, array('paramas' => $params));
        $files = array();

        // Get active templates
        if ($folder = self::getFlexformActiveFolder()) {
            $files = self::getFlexformActiveFolderTemplates();
        } else {
            $message = t3lib_div::makeInstance('t3lib_FlashMessage', tx_newspaper::getTranslation('flashMessage_extra_flexform_no_folder_configured'), 'Flexform', t3lib_FlashMessage::ERROR);
            t3lib_FlashMessageQueue::addMessage($message);
        }

        // Check if chosen template is an archived template
        // @todo: Process archived flexform templates correctly ...
        if ($params['row']['ds_file']) {
            $type = self::getFlexformDataStructureType($params['row']['ds_file']);
            if ($type === false) {
                /// Stored file not found in file system
                $message = t3lib_div::makeInstance('t3lib_FlashMessage', sprintf(tx_newspaper::getTranslation('flashMessage_extra_flexform_file_not_found'), $params['row']['ds_file']), 'Flexform', t3lib_FlashMessage::ERROR);
                t3lib_FlashMessageQueue::addMessage($message);
            } else if ($type == self::FF_TYPE_ARCHIVED) {
//                $message = t3lib_div::makeInstance('t3lib_FlashMessage', tx_newspaper::getTranslation('flashMessage_extra_flexform_archived'), 'Flexform', t3lib_FlashMessage::INFO);
                $message = t3lib_div::makeInstance('t3lib_FlashMessage', 'File not found. Please contact Typo3 support.', 'Flexform', t3lib_FlashMessage::ERROR);
                t3lib_FlashMessageQueue::addMessage($message);
//                $archivedFile = self::getFlexformArchiveFolder() . '/' . self::extractDsFileTitle($params['row']['ds_file']);
//                $files[] = $archivedFile; // Add selected flexform file to dropdown
//                 NOT WORKING: $params['row']['ds_file'] = $archivedFile . '.xml'; // Modify selected entry //@todo: move '.xml' into self::getFlexformActiveFolderTemplates()
            } // So file is an active template, has been read already
        }

        // Fill $params array for TCEForms
        $params['items'][] = array('0' => '', '1' => ''); // Empty entry
        foreach($files as $file) {
            $params['items'][] = array('0' => basename($file), '1' => $file  . '.xml'); // Store complete path, show Filename (excluding .xml) only
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
        if ($table != 'tx_newspaper_extra_flexform') {
            return; // Nothing to do ...
        }
        if ($field != 'ds_file' && $field != 'flexform') {
            return; // Still nothing to do ...
        }

        $ds_file = self::getFlexformDataStructure($row['ds_file']);

        self::modifyFlexformTca($field, $row, $ds_file);
        self::checkImageUploadFolder($field, $ds_file);
    }

    /**
     * Reads Flexform data structure from file set in dropdown "ds_file"
     * Hides Flexform field if no data structure is selected
     * Manipulates $TCA
     * @param $field
     * @param $row
     * @param $ds_file string ds_file contents
     * @return void
     */
    private static function modifyFlexformTca($field, $row, $ds_file) {
        if ($field == 'ds_file' && !$row['ds_file']) {
            // Hide Flexform when no data structure was selected
            unset($GLOBALS['TCA']['tx_newspaper_extra_flexform']['columns']['flexform']);
        }
        if ($field == 'flexform') {
            // @todo: Modify when template moved from active to archived path (see #2028)
            $GLOBALS['TCA']['tx_newspaper_extra_flexform']['columns']['flexform']['config']['ds']['default'] = $ds_file;
        }
    }

//* Shows a warning if image upload path is NOT set to uploads/tx_newspaper

    private static function checkImageUploadFolder($field, $ds_file) {
        if ($field != 'ds_file') {
            return; // Nothing to do ...
        }

        foreach(t3lib_div::trimExplode("\n", $ds_file) as $line) {
            // 14 -> length of string '<uploadfolder>'
            // 15 -> length of string '</uploadfolder>'
            if (strpos($line, '<uploadfolder>') ===  0) {
                // Upload path setting found
                $path = substr($line, 14, strlen($line) - 15 - 14);
                if ($path != tx_newspaper_Image::uploads_folder) {
                    $message = t3lib_div::makeInstance('t3lib_FlashMessage', sprintf(tx_newspaper::getTranslation('flashMessage_extra_flexform_wrong_upload_folder'), htmlspecialchars($path)), 'Flexform', t3lib_FlashMessage::WARNING);
                    t3lib_FlashMessageQueue::addMessage($message);
                }
            }
        }
    }


    /**
     * Typo3 image upload post process hook
     * Scale and deploy (=sync) images that are uploaded in to newspaper image upload folder /uploads/tx_newspaper
     * @param $filename string File
     * @param $pObj t3lib_TCEmain Parent object
     * @return void
     */
    public function processUpload_postProcessAction($filename, t3lib_TCEmain $pObj) {
        if (tx_newspaper::isNewspaperUploadPathUsed($filename)) {
            // Image is uploaded to newspaper image path, so resize and sync images
            $timer = tx_newspaper_ExecutionTimer::create();
            if ($image = new tx_newspaper_Image(basename($filename))) {
                if (class_exists('tx_AsynchronousTask')) {
                    $task = new tx_AsynchronousTask($image, 'deployImages');
                    $task->execute();
                } else {
                    $image->deployImages();
                }
            }
        } else {
            t3lib_div::devlog('Extra Flexform: Wrong image upload path', 'newspaper', 2, array('filename' => $filename));
        }
   	}


}

tx_newspaper_Extra::registerExtra(new tx_newspaper_Extra_Flexform());

tx_newspaper::registerSaveHook(new tx_newspaper_Extra_Flexform());

?>
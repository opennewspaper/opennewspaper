<?php

########################################################################
# Extension Manager/Repository config file for ext: "newspaper"
#
# Auto generated 21-03-2009 14:52
#
# Manual updates:
# Only the data in the array - anything else is removed by next write.
# "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'CMS for newspapers',
	'description' => 'CMS tailored for the needs of print newspapers who want to maintain an online presence',
	'category' => 'plugin',
	'author' => 'Helge Preuss, Oliver Schröder, Samuel Talleux',
	'author_email' => 'helge.preuss@gmail.com, typo3@schroederbros.de, samuel@talleux.de',
	'shy' => '',
	'dependencies' => 'cms,smarty',
	'conflicts' => '',
	'priority' => '',
	'module' => 'mod1,mod2',
	'state' => 'alpha',
	'internal' => '',
	'uploadfolder' => 1,
	'createDirs' => 'uploads/tx_newspaper/rte/',
	'modify_tables' => '',
	'clearCacheOnLoad' => 0,
	'lockType' => '',
	'author_company' => '',
	'version' => '0.0.0',
	'constraints' => array(
		'depends' => array(
			'cms' => '',
			'smarty' => '',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
);

?>
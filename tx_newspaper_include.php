<?php
/**
 *  \file tx_newspaper_include.php
 * 
 *  This file is part of the TYPO3 extension "newspaper".
 * 
 *  Copyright notice
 *
 *  (c) 2008 Helge Preuss, Oliver Schroeder, Samuel Talleux <helge.preuss@gmail.com, oliver@schroederbros.de, samuel@talleux.de>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 *  
 *  \author Helge Preuss <helge.preuss@gmail.com>
 *  \date Jan 9, 2009
 */
 
/** \mainpage tx_newspaper - an editing system for an online newspaper
 * 
 *  \section concepts Concepts
 *
 *    \subsection articles Articles
 * 
 *    Any newspaper consists of a number of articles. The article is the basic 
 *    building block of a newspaper.
 * 
 *    A typical article consists of a header, a kicker, a teaser and/or an
 *    introduction, and the article text. However, any article, especially
 *    online articles, can consist of many more elements. In tx_newspaper these
 *    elements are called \ref extras. These Extras are placed in an article
 *    before or after paragraphs of text. Placing Extras in an article is called
 *    Placement.
 * 
 *    \sa tx_newspaper_Article
 * 
 *      \subsubsection article_import Article import
 * 
 *      An online newspaper typically is just a department of a traditional
 *      print newspaper. The articles displayed in the online edition are taken
 *      from the editing software the editors for the print edition use. Getting
 *      an article from the print editing software into tx_newspaper is called
 *      "import".
 * 
 *      \subsubsection default_placement Default placement for articles
 *
 *      \subsubsection article_type Article types
 *      See tx_newspaper_ArticleType
 * 
 *    \subsection sections Sections
 * 
 *    \ref articles are grouped into Sections. When the reader visits a landing
 *    page for a section on the website, he or she expects to see a list of
 *    \ref articles belonging to that section.
 * 
 *    The articles belonging to a Section are managed in \ref article_lists.
 * 
 *    See tx_newspaper_Section.
 * 
 *      \subsubsection article_lists Article lists
 *      See tx_newspaper_ArticleList
 * 
 *    \subsection extras Extras
 *    See tx_newspaper_Extra
 * 
 *    \subsection pages Pages
 *    See tx_newspaper_Page
 * 
 *      \subsubsection page_types Page types
 *      See tx_newspaper_PageType
 * 
 *    \subsection page_zones Page Zones
 *    See tx_newspaper_PageZone
 * 
 *   \subsubsection placement Placement and inheritance
 *   see ...
 * 
 *   \subsubsection page_zone_types Page zone types
 *   see tx_newspaper_PageZoneType
 * 
 *   \subsection templates Display templates 
 *   see tx_newspaper_Smarty
 *  
 *  \section usage Using tx_newspaper
 * 
 *  \section administration Administering tx_newspaper
 *  
 *  \subsection install Installation
 * 
 *  \subsubsection requirements Requirements
 * 
 *  - Typo3 >= 4.2
 *  - Required extensions should be installed automatically
 * 
 *  \attention Installing \p newspaper with Typo3 4.1 on a Windows(TM) system
 *  	can lead to serious frustration and is not recommended!
 *
 *  \subsubsection install_templates Smarty templates
 * 
 *  \subsubsection install_articletypes Article types
 *   
 *  \subsection Troubleshooting Troubleshooting
 *  
 *  \todo Write me!
 */
 
require_once(PATH_typo3conf . 'ext/newspaper/classes/class.tx_newspaper.php');
require_once(PATH_typo3conf . 'ext/newspaper/classes/class.tx_newspaper_exception.php');
require_once(PATH_typo3conf . 'ext/newspaper/interfaces/interface.tx_newspaper_storedobject.php');
require_once(PATH_typo3conf . 'ext/newspaper/interfaces/interface.tx_newspaper_extraiface.php');
require_once(PATH_typo3conf . 'ext/newspaper/interfaces/interface.tx_newspaper_withsource.php');
require_once(PATH_typo3conf . 'ext/newspaper/interfaces/interface.tx_newspaper_articleiface.php');
require_once(PATH_typo3conf . 'ext/newspaper/interfaces/interface.tx_newspaper_source.php');
require_once(PATH_typo3conf . 'ext/newspaper/interfaces/interface.tx_newspaper_renderable.php');
require_once(PATH_typo3conf . 'ext/newspaper/interfaces/interface.tx_newspaper_writeslog.php');
require_once(PATH_typo3conf . 'ext/newspaper/classes/class.tx_newspaper_section.php');
require_once(PATH_typo3conf . 'ext/newspaper/classes/class.tx_newspaper_page.php');
require_once(PATH_typo3conf . 'ext/newspaper/classes/class.tx_newspaper_pagezone.php');
require_once(PATH_typo3conf . 'ext/newspaper/classes/class.tx_newspaper_extra_factory.php');
require_once(PATH_typo3conf . 'ext/newspaper/classes/class.tx_newspaper_articlelist_factory.php');
require_once(PATH_typo3conf . 'ext/newspaper/classes/class.tx_newspaper_article.php');
require_once(PATH_typo3conf . 'ext/newspaper/classes/class.tx_newspaper_articletype.php');
require_once(PATH_typo3conf . 'ext/newspaper/classes/class.tx_newspaper_sysfolder.php');

if (TYPO3_MODE == 'BE')	{
	require_once(PATH_typo3conf . 'ext/newspaper/classes/class.tx_newspaper_be.php');
	require_once(PATH_typo3conf . 'ext/newspaper/classes/class.tx_newspaper_util_mod.php');
}

?>

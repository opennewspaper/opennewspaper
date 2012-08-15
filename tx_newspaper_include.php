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
 *    \ref placement.
 * 
 *    \sa tx_newspaper_Article.
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
 *      Usually, an article should have a certain number of predefined  
 *      \ref extras. For example every article could be required to have an
 *      image. The Extras required for articles might change depending on the
 *      \ref sections the article is in - in the commentary section articles may
 *      have no image.
 * 
 *      For that reason it is possible to define the Extras that are associated
 *      with an article by default, per section. The position of the Extras in
 *      the article is also defined per section.
 *
 *      \subsubsection article_type Article types
 * 
 *      Not only does the default placement depend on the section an article is
 *      in, but also on the type of article. For instance, an interview might be
 *      required to always display a short biography of the interview partner,
 *      while a standard article would not. To distinguish the type of article
 *      and subsequently the correct default placement, the user can define an
 *      article type and the corresponding default placement.
 * 
 *      \sa tx_newspaper_ArticleType.
 * 
 *    \subsection sections Sections
 * 
 *    \ref articles are grouped into Sections. When the reader visits a landing
 *    page for a section on the website, he or she expects to see a list of
 *    \ref articles belonging to that section.
 * 
 *    The articles belonging to a Section are managed in \ref article_lists.
 * 
 *    Sections can be arranged in a tree-like structure. The "Politics" Section
 *    may have national and international politics as subsections. Only Articles
 *    from the "National" section can appear on the landing page for the
 *    "National" section, but articles from both "National" and "International"
 *    can appear on the "Politics" landing page.
 * 
 *    A section is comprised not only of a landing page, but also of other
 *    \ref pages.
 *   
 *    All sections are usually grouped under a root section. The landing page of
 *    the "root" section is the "Start" or "Home" page of the online newspaper.
 *    Articles from all sections can appear on the "Home" page.
 * 
 *    An article can belong to more than one section, if required. That way an
 *    article can appear both in the "Politics" and "Culture" sections, for
 *    example. 
 * 
 *    \sa tx_newspaper_Section.
 * 
 *      \subsubsection article_lists Article lists
 *      
 *      Which articles actually appear on a section landing page, is a matter of
 *      editorial decision. The articles and their order are managed in article
 *      lists.
 *  
 *      Often the articles should appear simply in the order in which they were
 *      published, with the newest at the top. But even in this case it may be
 *      necessary to tweak the order in which they appear. For that reason these
 *      lists are called semiautomatic. In other cases the order of articles is
 *      determined entirely by the editor. These article lists are called
 *      manual.
 * 
 *      \sa tx_newspaper_ArticleList, tx_newspaper_ArticleList_Manual, 
 *          tx_newspaper_ArticleList_Semiautomatic.
 * 
 *    \subsection extras Extras
 * 
 *    A newspaper page consists not only of \ref articles and \ref article_lists,
 *    but has many other elements: Text boxes, images, videos, ads, etc. These
 *    elements are called Extras. An Extra can be placed in an article or on any
 *    page.
 * 
 *    \sa tx_newspaper_Extra.
 * 
 *      \subsubsection placement Placement
 * 
 *    \subsection pages Pages
 *    \sa tx_newspaper_Page.
 * 
 *      \subsubsection page_types Page types
 *      \sa tx_newspaper_PageType.
 * 
 *    \subsection page_zones Page Zones
 *    \sa tx_newspaper_PageZone.
 * 
 *      \subsubsection placement Placement and inheritance
 *      see ...
 * 
 *      \subsubsection page_zone_types Page zone types
 *      \sa tx_newspaper_PageZoneType.
 * 
 *    \subsection templates Display templates 
 *    \sa tx_newspaper_Smarty.
 *  
 *  \section usage Using tx_newspaper
 * 
 *  \section administration Administering tx_newspaper
 *  
 *    \subsection install Installation
 * 
 *      \subsubsection requirements Requirements
 * 
 *      - Typo3 >= 4.2
 *      - Required extensions should be installed automatically
 * 
 *      \attention Installing \p newspaper with Typo3 4.1 on a Windows(TM) 
 *  	    system can lead to serious frustration and is not recommended!
 *
 *      \subsubsection install_templates Smarty templates
 * 
 *      \subsubsection install_articletypes Article types
 *   
 *    \subsection Troubleshooting Troubleshooting
 *  
 *  \todo Write me!
 */
 
require_once(PATH_typo3conf . 'ext/newspaper/Classes/class.tx_newspaper.php');
require_once(PATH_typo3conf . 'ext/newspaper/Classes/class.tx_newspaper_exception.php');
require_once(PATH_typo3conf . 'ext/newspaper/interfaces/interface.tx_newspaper_storedobject.php');
require_once(PATH_typo3conf . 'ext/newspaper/interfaces/interface.tx_newspaper_extraiface.php');
require_once(PATH_typo3conf . 'ext/newspaper/interfaces/interface.tx_newspaper_withsource.php');
require_once(PATH_typo3conf . 'ext/newspaper/interfaces/interface.tx_newspaper_articleiface.php');
require_once(PATH_typo3conf . 'ext/newspaper/interfaces/interface.tx_newspaper_source.php');
require_once(PATH_typo3conf . 'ext/newspaper/interfaces/interface.tx_newspaper_renderable.php');
require_once(PATH_typo3conf . 'ext/newspaper/interfaces/interface.tx_newspaper_writeslog.php');
require_once(PATH_typo3conf . 'ext/newspaper/Classes/class.tx_newspaper_section.php');
require_once(PATH_typo3conf . 'ext/newspaper/Classes/class.tx_newspaper_page.php');
require_once(PATH_typo3conf . 'ext/newspaper/Classes/class.tx_newspaper_pagezone.php');
require_once(PATH_typo3conf . 'ext/newspaper/Classes/private/class.tx_newspaper_extra_factory.php');
require_once(PATH_typo3conf . 'ext/newspaper/Classes/private/class.tx_newspaper_articlelist_factory.php');
require_once(PATH_typo3conf . 'ext/newspaper/Classes/class.tx_newspaper_article.php');
require_once(PATH_typo3conf . 'ext/newspaper/Classes/class.tx_newspaper_articletype.php');
require_once(PATH_typo3conf . 'ext/newspaper/Classes/class.tx_newspaper_sysfolder.php');
require_once(PATH_typo3conf . 'ext/newspaper/Classes/private/class.tx_newspaper_util_mod.php'); // \todo: why is this class used in frontend? (see #1019)

if (TYPO3_MODE == 'BE')	{
	require_once(PATH_typo3conf . 'ext/newspaper/Classes/private/class.tx_newspaper_be.php');
	require_once(PATH_typo3conf . 'ext/newspaper/Classes/class.tx_newspaper_workflow.php');
	require_once(PATH_typo3conf . 'ext/newspaper/Classes/class.tx_newspaper_typo3hook.php');
	require_once(PATH_typo3conf . 'ext/newspaper/Classes/class.tx_newspaper_dependencytree.php');
    require_once(PATH_typo3conf . 'ext/newspaper/Classes/private/class.tx_newspaper_dependencytreeproxy.php');

    tx_newspaper_ExecutionTimer::setLogger(new tx_newspaper_OrderedFileLogger());

}

?>

<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
//
// +----------------------------------------------------------------------+
// | PHP Version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2002 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject toversion 3.0 of the PHP license,      |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/3_0.txt.                                  |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Authors: Alexander Zhukov <alex@veresk.ru> Original port from Python |
// | Authors: Harry Fuecks <hfuecks@phppatterns.com> Port to PEAR + more  |
// | Authors: Many @ Sitepointforums Advanced PHP Forums                  |
// +----------------------------------------------------------------------+
//
// $Id: Grammar.php 245174 2007-10-29 21:41:35Z hfuecks $
//
/**
* For dealing with HTML's special cases
* @package XML_HTMLSax3
* @version $Id: Grammar.php 245174 2007-10-29 21:41:35Z hfuecks $
*/
/**
* Passed as fourth argument to opening and closing handler to signify
* a tag which was immediately closed like <this />
* @package XML_HTMLSax3
*/
define ('XML_HTMLSAX3_EMTPY',1);

/**
* Passed as fourth argument to opening and closing handler to signify
* special HTML tags which are not supposed to have a closing tag such
* as the hr tag. Only used when XML_OPTION_HTML_SPECIALS is on
* @package XML_HTMLSax3
*/
define ('XML_HTMLSAX3_ENDTAG_FORBIDDEN',2);

/**
* Passed as fourth argument to closing handler only, to signify
* special HTML tags which are not supposed to have a closing tag such
* as the hr tag but where a closing tag has been used. Only used when
* XML_OPTION_HTML_SPECIALS is on
* @package XML_HTMLSax3
*/
define ('XML_HTMLSAX3_ENDTAG_FORBIDDEN_WARNING',3);

/**
* Global array for lookups on tags which should not have closing tags
* @package XML_HTMLSax3
*/
$GLOBALS['_XML_HTMLSAX3_ENDTAG_FORBIDDEN'] = array (
    'area',
    'base',
    'basefont',
    'br',
    'col',
    'frame',
    'hr',
    'img',
    'input',
    'isindex',
    'link',
    'meta',
    'param',
);
?>
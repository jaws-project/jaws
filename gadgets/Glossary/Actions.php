<?php
/**
 * Glossary Actions file
 *
 * @category    GadgetActions
 * @package     Glossary
 * @author      Pablo Fischer <pablo@pablo.com.mx>
 * @copyright   2004-2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */

/**
 * Index actions
 */
$actions['ViewTerm'] = array(
    'normal' => true,
    'file'   => 'Term',
);
$actions['RandomTerms'] = array(
    'layout' => true,
    'file'   => 'Term',
);
$actions['ListOfTerms'] = array(
    'layout' => true,
    'file'   => 'Term',
);

/**
 * Admin actions
 */
$admin_actions['Term'] = array(
    'normal' => true,
    'file' => 'Term',
);

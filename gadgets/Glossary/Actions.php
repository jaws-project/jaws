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
$actions['ViewTerms'] = array(
    'normal' => true,
    'file'   => 'Terms',
);
$actions['RandomTerms'] = array(
    'layout' => true,
    'file'   => 'Terms',
);
$actions['ListOfTerms'] = array(
    'layout' => true,
    'file'   => 'Terms',
);

/**
 * Admin actions
 */
$admin_actions['Terms'] = array(
    'normal' => true,
    'file' => 'Terms',
);
$admin_actions['GetTerm'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['NewTerm'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['UpdateTerm'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['DeleteTerm'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['ParseText'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);

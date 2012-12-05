<?php
/**
 * Glossary Actions file
 *
 * @category   GadgetActions
 * @package    Glossary
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
$index_actions = array();

$index_actions['ViewTerm']    = array('NormalAction');

$index_actions['RandomTerms'] = array('LayoutAction',  
                                _t('GLOSSARY_LAYOUT_RANDOM'),
                                _t('GLOSSARY_LAYOUT_RANDOM_DESC'));
$index_actions['ListOfTerms'] = array('LayoutAction', 
                                _t('GLOSSARY_LAYOUT_LISTOF'),
                                _t('GLOSSARY_LAYOUT_LISTOF_DESC'));

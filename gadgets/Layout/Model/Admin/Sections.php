<?php
/**
 * Layout Core Gadget
 *
 * @category   GadgetModel
 * @package    Layout
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2014 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Layout_Model_Admin_Sections extends Layout_Model_Layout
{
    /**
     * Get the layout sections
     *
     * @access  public
     * @return  array   Returns an array of layout mode sections and Jaws_Error on error
     */
    function GetLayoutSections()
    {
        $user = (int)$GLOBALS['app']->Session->GetAttribute('layout');
        $lyTable = Jaws_ORM::getInstance()->table('layout');
        return $lyTable->select('section')->where('user', $user)->orderBy('section')->fetchRow();
    }

    /**
     * Move a section to other place
     *
     * @access  public
     * @param   string  $from   Which section to move
     * @param   string  $to     The destination
     * @return  bool    True if the section was moved without problems, if not it returns false
     */
    function MoveSection($from, $to)
    {
        $user = (int)$GLOBALS['app']->Session->GetAttribute('layout');
        $lyTable = Jaws_ORM::getInstance()->table('layout');
        $maxpos = $lyTable->select('max(layout_position)')
            ->where('section', $to)
            ->and()
            ->where('user', $user)
            ->fetchOne();
        if (Jaws_Error::IsError($maxpos) || empty($maxpos)) {
            $maxpos = '0';
        }

        $lyTable->update(array(
            'section' => $to,
            'layout_position' => $lyTable->expr('layout_position + ?', $maxpos)
        ));
        return $result = $lyTable->where('section', $from)->and()->where('user', $user)->exec();
    }

}
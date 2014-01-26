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
     * @param   bool    $index  Index layout
     * @return  array   Returns an array of layout mode sections and Jaws_Error on error
     */
    function GetLayoutSections($user = 0, $index = false)
    {
        $lyTable = Jaws_ORM::getInstance()->table('layout');
        return $lyTable->select('section')
            ->distinct()
            ->where('user', (int)$user)
            ->and()
            ->where('index', (bool)$index)
            ->orderBy('section')
            ->fetchColumn();
    }

    /**
     * Move a section to other place
     *
     * @access  public
     * @param   bool    $index  Index layout
     * @param   string  $from   Which section to move
     * @param   string  $to     The destination
     * @param   int     $user   (Optional) User's ID
     * @return  bool    True if the section was moved without problems, if not it returns false
     */
    function MoveSection($index, $from, $to, $user = 0)
    {
        $lyTable = Jaws_ORM::getInstance()->table('layout');
        $maxpos = $lyTable->select('max(layout_position)')
            ->where('user', (int)$user)
            ->and()
            ->where('index', (bool)$index)
            ->and()
            ->where('section', $to)
            ->fetchOne();
        if (Jaws_Error::IsError($maxpos) || empty($maxpos)) {
            $maxpos = 0;
        }

        $lyTable->update(array(
                'section' => $to,
                'layout_position' => $lyTable->expr('layout_position + ?', (int)$maxpos)
        ));
        return $lyTable->where('user', (int)$user)
            ->and()
            ->where('index', (bool)$index)
            ->and()
            ->where('section', $from)
            ->exec();
    }

    /**
     * Delete user's layouts
     *
     * @access  public
     * @param   int     $user   User's ID
     * @param   bool    $index  Index layout
     * @return  bool    Returns true if layouts was removed otherwise it returns Jaws_Error
     */
    function DeleteUserLayouts($user, $index = null)
    {
        $lyTable = Jaws_ORM::getInstance()->table('layout');
        $lyTable->delete()->where('user', (int)$user);
        if (!is_null($index)) {
            $lyTable->and()->where('index', (bool)$index);
        }
        return $lyTable->exec();
    }

}
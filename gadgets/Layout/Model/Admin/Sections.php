<?php
/**
 * Layout Core Gadget
 *
 * @category   GadgetModel
 * @package    Layout
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Layout_Model_Admin_Sections extends Layout_Model_Layout
{
    /**
     * Get the layout layouts
     *
     * @access  public
     * @param   int     $user   (Optional) User's ID
     * @return  array   Returns an array of layout mode sections or Jaws_Error on error
     */
    function GetLayoutLayouts($user = 0)
    {
        $lyTable = Jaws_ORM::getInstance()->table('layout');
        return $lyTable->select('layout')
            ->distinct()
            ->where('user', (int)$user)
            ->orderBy('layout')
            ->fetchColumn();
    }

    /**
     * Get the layout sections
     *
     * @access  public
     * @param   string  $layout Layout name
     * @param   int     $user   (Optional) User's ID
     * @return  array   Returns an array of layout mode sections or Jaws_Error on error
     */
    function GetLayoutSections($layout = 'Layout', $user = 0)
    {
        $lyTable = Jaws_ORM::getInstance()->table('layout');
        $lyTable->select('section')
            ->distinct()
            ->where('layout', $layout);
        if (!empty($user)) {
            $lyTable->and()->where('user', (int)$user);
        }

        return $lyTable->orderBy('section')->fetchColumn();
    }

    /**
     * Move a section to other place
     *
     * @access  public
     * @param   string  $layout Layout name
     * @param   string  $from   Which section to move
     * @param   string  $to     The destination
     * @param   int     $user   (Optional) User's ID
     * @return  bool    True if the section was moved without problems, if not it returns false
     */
    function MoveSection($layout, $from, $to, $user = 0)
    {
        $lyTable = Jaws_ORM::getInstance()->table('layout');
         return $lyTable->update(array('section' => $to))
            ->where('layout', $layout)
            ->and()
            ->where('section', $from)
            ->exec();
    }

    /**
     * Delete user's layouts
     *
     * @access  public
     * @param   int     $user   User's ID
     * @param   string  $layout Layout name
     * @return  bool    Returns true if layouts was removed otherwise it returns Jaws_Error
     */
    function DeleteUserLayouts($user, $layout = null)
    {
        $lyTable = Jaws_ORM::getInstance()->table('layout');
        $lyTable->delete()->where('user', (int)$user);
        if (!is_null($layout)) {
            $lyTable->and()->where('layout', $layout);
        }
        return $lyTable->exec();
    }

    /**
     * Delete layouts
     *
     * @access  public
     * @param   string  $layout Layout name
     * @param   int     $user   (Optional) User's ID
     * @return  bool    Returns true if layouts was removed otherwise it returns Jaws_Error
     */
    function DeleteLayouts($layout, $user = 0)
    {
        $lyTable = Jaws_ORM::getInstance()->table('layout');
        $lyTable->delete()-where('layout', $layout);
        if (!empty($user)) {
            $lyTable->and()->where('user', (int)$user);
        }
        return $lyTable->exec();
    }

}
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
     * Get layouts
     *
     * @access  public
     * @return  array   Returns an array of layouts or Jaws_Error on error
     */
    function GetLayouts()
    {
        $lyTable = Jaws_ORM::getInstance()->table('layout');
        return $lyTable->select('layout')
            ->distinct()
            ->orderBy('layout')
            ->fetchColumn();
    }

    /**
     * Get the layout sections
     *
     * @access  public
     * @param   string  $layout Layout name
     * @return  array   Returns an array of layout mode sections or Jaws_Error on error
     */
    function GetLayoutSections($layout = 'Layout')
    {
        $lyTable = Jaws_ORM::getInstance()->table('layout');
        return $lyTable->select('section')
            ->distinct()
            ->where('layout', $layout)
            ->orderBy('section')
            ->fetchColumn();
    }

    /**
     * Move a section to other place
     *
     * @access  public
     * @param   string  $layout Layout name
     * @param   string  $from   Which section to move
     * @param   string  $to     The destination
     * @return  bool    True if the section was moved without problems, if not it returns false
     */
    function MoveSection($layout, $from, $to)
    {
        $lyTable = Jaws_ORM::getInstance()->table('layout');
         return $lyTable->update(array('section' => $to))
            ->where('layout', $layout)
            ->and()
            ->where('section', $from)
            ->exec();
    }

    /**
     * Delete a layout
     *
     * @access  public
     * @param   string  $layout Layout name
     * @param   int     $user   (Optional)User's ID
     * @return  bool    Returns true if layout was removed otherwise it returns Jaws_Error
     */
    function DeleteLayout($layout, $user = 0)
    {
        $lyTable = Jaws_ORM::getInstance()->table('layout');
        $lyTable->delete()-where('layout', $layout);
        if (!empty($user)) {
            $lyTable->and()->where('user', (int)$user);
        }
        return $lyTable->exec();
    }

}
<?php
/**
 * Model class (has the heavy queries) to manage layout
 *
 * @category   Layout
 * @package    Core
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2006-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Layout_Model_Layout extends Jaws_Gadget_Model
{
    /**
     * Get the layout items
     *
     * @access  public
     * @param   string  $layout     Layout name
     * @param   bool    $published  Publish status
     * @return  array   Returns an array with the layout items or Jaws_Error on failure
     */
    function GetLayoutItems($layout = 'Layout', $published = null)
    {
        $user = 0;
        if ($layout == 'Index.Dashboard') {
            $user = (int)$GLOBALS['app']->Session->GetAttribute('user');
        }

        $lyTable = Jaws_ORM::getInstance()->table('layout');
        $lyTable->select(
            'id', 'title', 'gadget', 'action', 'params',
            'filename', 'when', 'section', 'position'
        );
        $lyTable->where('user', (int)$user)->and()->where('layout', $layout);
        if (!is_null($published)) {
            $lyTable->and()->where('published', (bool)$published);
        }

        return $lyTable->orderBy('position asc')->fetchAll();
    }

    /**
     * Initialize a layout
     *
     * @access  public
     * @param   string  $layout Layout name
     * @return  mixed   Return true or Jaws_Error on failure
     */
    function InitialLayout($layout = 'Layout')
    {
        $user = 0;
        if ($layout == 'Index.Dashboard') {
            $user = (int)$GLOBALS['app']->Session->GetAttribute('user');
        }

        // REQUESTEDGADGET/REQUESTEDACTION
        $lyTable = Jaws_ORM::getInstance()->table('layout');
        $exists = $lyTable->select('count(id)')
            ->where('layout', $layout)
            ->and()
            ->where('user', $user)
            ->and()
            ->where('gadget', '[REQUESTEDGADGET]')
            ->fetchOne();
        if (Jaws_Error::IsError($exists)) {
            return $exists;
        }

        if (empty($exists)) {
            $elModel = $this->gadget->model->loadAdmin('Elements');
            $elModel->NewElement(
                $layout,
                null,
                'main',
                '[REQUESTEDGADGET]',
                '[REQUESTEDACTION]',
                null,
                '',
                1
            );
        }

        if ($layout == 'Index.Dashboard') {
            $GLOBALS['app']->Session->SetAttribute(
                'layout',
                $GLOBALS['app']->Session->GetAttribute('layout')? 0 : $user
            );
        }

        return true;
    }

}
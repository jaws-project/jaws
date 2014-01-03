<?php
/**
 * Model class (has the heavy queries) to manage layout
 *
 * @category   Layout
 * @package    Core
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2006-2014 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Layout_Model_Layout extends Jaws_Gadget_Model
{
    /**
     * Get the layout items
     *
     * @access  public
     * @param   int     $user       User's ID
     * @param   bool    $published  Publish status
     * @return  array   Returns an array with the layout items or Jaws_Error on failure
     */
    function GetLayoutItems($user = 0, $published = null)
    {
        $lyTable = Jaws_ORM::getInstance()->table('layout');
        $items = $lyTable->select(
            'id', 'gadget', 'gadget_action', 'action_params',
            'action_filename', 'display_when', 'section'
        );

        $items->where('user', (int)$user);
        if (!is_null($published)) {
            $items->and()->where('published', (bool)$published);
        }

        $lyTable->orderBy('layout_position asc');
        return $items->fetchAll();
    }

    /**
     * Switch between layouts
     *
     * @access  public
     * @param   int     $user   User's ID
     * @return  void
     */
    function layoutSwitch($user = 0)
    {
        $lyTable = Jaws_ORM::getInstance()->table('layout');
        if (!empty($user)) {
            // REQUESTEDGADGET/REQUESTEDACTION
            $exists = $lyTable->select('count(id)')
                ->where('user', (int)$user)
                ->and()
                ->where('gadget', '[REQUESTEDGADGET]')
                ->fetchOne();
            if (!Jaws_Error::IsError($exists) && empty($exists)) {
                $elModel = $this->gadget->model->loadAdmin('Elements');
                $elModel->NewElement('main', '[REQUESTEDGADGET]', '[REQUESTEDACTION]', null, '', 1, $user);
            }

            // Users/LoginBox
            $exists = $lyTable->select('count(id)')
                ->where('user', (int)$user)
                ->and()
                ->where('gadget', 'Users')
                ->and()
                ->where('gadget_action', 'LoginBox')
                ->fetchOne();
            if (!Jaws_Error::IsError($exists) && empty($exists)) {
                $elModel = $this->gadget->model->loadAdmin('Elements');
                $elModel->NewElement('main', 'Users', 'LoginBox', null, 'Login', 2, $user);
            }
        }

        $layout_user = (int)$GLOBALS['app']->Session->GetAttribute('layout');
        $GLOBALS['app']->Session->SetAttribute('layout', empty($layout_user)? $user : 0);
        return true;
    }

}
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
     * @param   int     $user       User's ID
     * @param   bool    $index      Elements in index layout
     * @param   bool    $published  Publish status
     * @return  array   Returns an array with the layout items or Jaws_Error on failure
     */
    function GetLayoutItems($user = 0, $index = false, $published = null)
    {
        $lyTable = Jaws_ORM::getInstance()->table('layout');
        $lyTable->select(
            'id', 'gadget', 'gadget_action', 'action_params',
            'action_filename', 'display_when', 'section', 'layout_position'
        );
        $lyTable->where('user', (int)$user)->and()->where('index', (bool)$index);
        if (!is_null($published)) {
            $lyTable->and()->where('published', (bool)$published);
        }
        $items = $lyTable->orderBy('layout_position asc')->fetchAll();
        if (!Jaws_Error::isError($items) && $index) {
            array_unshift(
                $items,
                array(
                    'id'              => null,
                    'gadget'          => '[REQUESTEDGADGET]',
                    'gadget_action'   => '[REQUESTEDACTION]',
                    'action_params'   => '',
                    'action_filename' => '',
                    'display_when'    => '*',
                    'section'         => 'main',
                    'layout_position' => 0,
                )
            );
        }

        return $items;
    }

    /**
     * Switch between layouts
     *
     * @access  public
     * @param   int     $user   User's ID
     * @return  void
     */
    function DashboardSwitch($user = 0)
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
                $elModel->NewElement(false, 'main', '[REQUESTEDGADGET]', '[REQUESTEDACTION]', null, '', 1, $user);
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
                $elModel->NewElement(false, 'main', 'Users', 'LoginBox', null, 'Login', 2, $user);
            }
        }

        $layout_user = (int)$GLOBALS['app']->Session->GetAttribute('layout');
        $GLOBALS['app']->Session->SetAttribute('layout', empty($layout_user)? $user : 0);
        return true;
    }

}
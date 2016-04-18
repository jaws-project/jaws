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
     * @param   bool    $layout     Layout name
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
        $items = $lyTable->orderBy('position asc')->fetchAll();
        if (!Jaws_Error::isError($items) && ($layout != 'Layout')) {
            array_unshift(
                $items,
                array(
                    'id'       => null,
                    'gadget'   => '[REQUESTEDGADGET]',
                    'action'   => '[REQUESTEDACTION]',
                    'params'   => '',
                    'filename' => '',
                    'when'     => '*',
                    'section'  => 'main',
                    'position' => 0,
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
                $elModel->NewElement(
                    'Index.Dashboard',
                    null,
                    'main',
                    '[REQUESTEDGADGET]',
                    '[REQUESTEDACTION]',
                    null,
                    '',
                    1
                );
            }

            // Users/LoginBox
            $exists = $lyTable->select('count(id)')
                ->where('user', (int)$user)
                ->and()
                ->where('gadget', 'Users')
                ->and()
                ->where('action', 'LoginBox')
                ->fetchOne();
            if (!Jaws_Error::IsError($exists) && empty($exists)) {
                $elModel = $this->gadget->model->loadAdmin('Elements');
                $elModel->NewElement(
                    'Index.Dashboard',
                    null,
                    'main',
                    'Users',
                    'LoginBox',
                    null,
                    'Login',
                    2
                );
            }
        }

        $GLOBALS['app']->Session->SetAttribute('layout', $user);
        return true;
    }

}
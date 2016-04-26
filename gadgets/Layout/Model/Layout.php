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
        $user = ($layout == 'Index.Dashboard')? $this->gadget->user : 0;
        $lyTable = Jaws_ORM::getInstance()->table('layout');
        $lyTable->select(
            'id', 'title', 'gadget', 'action', 'params',
            'filename', 'when', 'section', 'position'
        );
        $lyTable->where('user', $user)
            ->and()
            ->where('theme', $this->gadget->theme)
            ->and()
            ->where('locality', $this->gadget->locality)
            ->and()
            ->where('layout', $layout);
        if (!is_null($published)) {
            $lyTable->and()->where('published', (bool)$published);
        }
        $elements = $lyTable->orderBy('position asc')->fetchAll();
        if (Jaws_Error::IsError($elements)) {
            return $elements;
        }

        if (empty($elements)) {
            $elements   = array();
            $elements[] = array(
                'id'       => null,
                'gadget'   => '[REQUESTEDGADGET]',
                'action'   => '[REQUESTEDACTION]',
                'params'   => '',
                'filename' => '',
                'when'     => '*',
                'section'  => 'main',
                'position' => 0,
            );
        }

        return $elements;
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
        $user = ($layout == 'Index.Dashboard')? $this->gadget->user : 0;
        // REQUESTEDGADGET/REQUESTEDACTION
        $lyTable = Jaws_ORM::getInstance()->table('layout');
        $exists = $lyTable->select('count(id)')
            ->where('user', $user)
            ->and()
            ->where('theme', $this->gadget->theme)
            ->and()
            ->where('locality', $this->gadget->locality)
            ->and()
            ->where('layout', $layout)
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
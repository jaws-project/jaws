<?php
/**
 * Wiki Gadget Admin
 *
 * @category   GadgetAdmin
 * @package    Wiki
 */
class Blog_Actions_Admin_Types extends Blog_Actions_Admin_Default
{
    /**
     * Builds Types UI
     *
     * @access  public
     * @return  string  XHTML UI
     */
    function Types()
    {
        $this->gadget->CheckPermission('ManageTypes');

        $cHTML = Jaws_Gadget::getInstance('Categories')->action->loadAdmin('Categories');
        return $cHTML->Execute(
            'Categories',
            array($this->gadget->name, 'Types', $this->MenuBar('Types'))
        );
    }
}
<?php
/**
 * Quotes Gadget Admin
 *
 * @category   GadgetAdmin
 * @package    Quotes
 */
class Quotes_Actions_Admin_Categories extends Quotes_Actions_Admin_Default
{
    /**
     * Displays categories management
     *
     * @access  public
     * @return  string  XHTML UI
     */
    function categories()
    {
        if (!Jaws_Gadget::IsGadgetInstalled('Categories')) {
            return 'Categories gadget not installed!';
        }

        $cHTML = Jaws_Gadget::getInstance('Categories')->action->loadAdmin('Categories');
        return $cHTML->Categories($this->gadget->name, 'Quotes', $this->MenuBar('categories'));
    }
}
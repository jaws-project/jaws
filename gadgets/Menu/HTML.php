<?php
/**
 * Menu Gadget
 *
 * @category   Gadget
 * @package    Menu
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Jon Wood <jon@substance-it.co.uk>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Menu_HTML extends Jaws_Gadget_HTML
{
    /**
     * Default action
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function DefaultAction()
    {
        $layoutGadget = $GLOBALS['app']->LoadGadget('Menu', 'LayoutHTML');
        return $layoutGadget->Display($this->gadget->registry->fetch('default_group_id'));
    }

    /**
     * Returns menu image as stream data
     *
     * @access  public
     * @return  bool    True on successful, False otherwise
     */
    function LoadImage()
    {
        $request =& Jaws_Request::getInstance();
        $id = (int)$request->get('id', 'get');
        $model = $GLOBALS['app']->LoadGadget('Menu', 'Model');
        $image = $model->GetMenuImage($id);
        if (!Jaws_Error::IsError($image)) {
            require_once JAWS_PATH . 'include/Jaws/Image.php';
            $objImage = Jaws_Image::factory();
            if (!Jaws_Error::IsError($objImage)) {
                $objImage->setData($image, true);
                $res = $objImage->display('', null, 315360000);// cached for 10 years!
                if (!Jaws_Error::IsError($res)) {
                    return true;
                }
            }
        }

        return false;
    }

}
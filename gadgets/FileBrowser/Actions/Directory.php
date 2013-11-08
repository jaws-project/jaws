<?php
/**
 * Filebrowser Gadget
 *
 * @category   Gadget
 * @package    FileBrowser
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class FileBrowser_Actions_Directory extends Jaws_Gadget_Action
{
    /**
     * Prints all the files with their titles and contents of initial folder
     *
     * @access  public
     * @param   string  $path
     * @return  string  XHTML template content with titles and contents
     */
    function InitialFolder($path = '')
    {
        if (!$this->gadget->GetPermission('OutputAccess')) {
            return false;
        }

        if ($this->gadget->registry->fetch('frontend_avail') != 'true') {
            return false;
        }

        $tpl = $this->gadget->template->load('FileBrowser.html');
        $tpl->SetBlock('initial_folder');
        $tpl->SetVariable('title', _t('FILEBROWSER_NAME'));

        $model = $this->gadget->model->load('Directory');
        $items = $model->ReadDir($path);
        if (!Jaws_Error::IsError($items)) {
            foreach ($items as $item) {
                $tpl->SetBlock('initial_folder/item');
                $tpl->SetVariable('icon',  $item['mini_icon']);
                $tpl->SetVariable('name',  Jaws_XSS::filter($item['filename']));
                $tpl->SetVariable('title', Jaws_XSS::filter($item['title']));
                if ($item['is_dir']) {
                    $relative = Jaws_XSS::filter($item['relative']) . '/';
                    $url = $GLOBALS['app']->Map->GetURLFor('FileBrowser',
                        'Display',
                        array('path' => $relative));
                } else {
                    if (empty($item['id'])) {
                        $url = Jaws_XSS::filter($item['url']);
                    } else {
                        $fid = empty($item['fast_url']) ? $item['id'] : Jaws_XSS::filter($item['fast_url']);
                        $url = $GLOBALS['app']->Map->GetURLFor('FileBrowser',
                            'Download',
                            array('id' => $fid));
                    }
                }
                $tpl->SetVariable('url', $url);
                $tpl->ParseBlock('initial_folder/item');
            }
        }
        $tpl->ParseBlock('initial_folder');

        return $tpl->Get();
    }
}
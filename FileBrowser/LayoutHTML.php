<?php
/**
 * FileBrowser Layout HTML file (for layout purposes)
 *
 * @category   GadgetLayout
 * @package    FileBrowser
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class FileBrowserLayoutHTML
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
        if (!$GLOBALS['app']->Session->GetPermission('FileBrowser', 'OutputAccess')) {
            return false;
        }

        if ($this->GetRegistry('frontend_avail') != 'true') {
            return false;
        }

        $tpl = new Jaws_Template('gadgets/FileBrowser/templates/');
        $tpl->Load('FileBrowser.html');
        $tpl->SetBlock('initial_folder');
        $tpl->SetVariable('title', _t('FILEBROWSER_NAME'));

        $model = $GLOBALS['app']->LoadGadget('FileBrowser', 'Model');
        $items = $model->ReadDir($path);
        if (!Jaws_Error::IsError($items)) {
            $xss = $GLOBALS['app']->loadClass('XSS', 'Jaws_XSS');
            foreach ($items as $item) {
                $tpl->SetBlock('initial_folder/item');
                $tpl->SetVariable('icon',  $item['mini_icon']);
                $tpl->SetVariable('name',  $xss->filter($item['filename']));
                $tpl->SetVariable('title', $xss->filter($item['title']));
                if ($item['is_dir']) {
                    $relative = $xss->filter($item['relative']) . '/';
                    $url = $GLOBALS['app']->Map->GetURLFor('FileBrowser',
                                                           'Display',
                                                           array('path' => $relative),
                                                           false);
                } else {
                    if (empty($item['id'])) {
                        $url = $xss->filter($item['url']);
                    } else {
                        $fid = empty($item['fast_url']) ? $item['id'] : $xss->filter($item['fast_url']);
                        $url = $GLOBALS['app']->Map->GetURLFor('FileBrowser',
                                                               'Download',
                                                               array('id' => $fid),
                                                               false);
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
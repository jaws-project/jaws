<?php
/**
 * Filebrowser Gadget
 *
 * @category   Gadget
 * @package    FileBrowser
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class FileBrowserHTML extends Jaws_Gadget_HTML
{
    /**
     * Default action to be run if none is defined.
     *
     * @access  public
     * @return  string   XHTML template content of Default action
     */
    function DefaultAction()
    {
        return $this->Display();
    }

    /**
     * Prints all the files with their titles and contents
     *
     * @access  public
     * @return  mixed  XHTML template content with titles and contents or false or error
     */
    function Display()
    {
        if ($this->GetRegistry('frontend_avail') != 'true') {
            return false;
        }

        if (!$GLOBALS['app']->Session->GetPermission('FileBrowser', 'OutputAccess')) {
            if ($GLOBALS['app']->Session->Logged()) {
                return _t('GLOBAL_ERROR_ACCESS_DENIED');
            } else {
                return _t('GLOBAL_ERROR_ACCESS_RESTRICTED',
                          $GLOBALS['app']->Map->GetURLFor('Users', 'LoginBox'),
                          $GLOBALS['app']->Map->GetURLFor('Users', 'Registration'));
            }
        }

        $request =& Jaws_Request::getInstance();
        if ($request->get('path', 'get')) {
            $path = $request->get('path', 'get');
        } elseif ($request->get('path', 'post')) {
            $path = $request->get('path', 'post');
        } else {
            $path = '';
        }

        $page = $request->get('page', 'get');
        if (is_null($page) || $page <= 0 ) {
            $page = 1;
        }

        $model = $GLOBALS['app']->LoadGadget('FileBrowser', 'Model');
        $locationTree = $model->GetCurrentRootDir($path);
        if (Jaws_Error::IsError($locationTree)) {
            return false;
        }

        $tpl = new Jaws_Template('gadgets/FileBrowser/templates/');
        $tpl->Load('FileBrowser.html');
        $tpl->SetBlock('filebrowser');
        $tpl->SetVariable('title', _t('FILEBROWSER_NAME'));
        $this->SetTitle(_t('FILEBROWSER_NAME'));

        $parentPath = '';
        $tpl->SetVariable('location', _t('FILEBROWSER_LOCATION'));
        foreach ($locationTree as $_path => $dir) {
            if (!empty($dir) && $_path{0} == '/') {
                $_path = substr($_path, 1);
            }

            $dbFile = $model->DBFileInfo($parentPath, $dir);
            if (Jaws_Error::IsError($dbFile) || empty($dbFile)) {
                $dirTitle = $dir;
            } else {
                $dirTitle = $dbFile['title'];
            }

            $parentPath = $_path;
            if (empty($_path)) {
                $tpl->SetVariable('root', _t('FILEBROWSER_ROOT'));
                $tpl->SetVariable('root-path', $this->GetURLFor('Display', array('path' => $_path), false));
            } else {
                $tpl->SetBlock('filebrowser/tree');
                $tpl->SetVariable('dir-name', $dirTitle);
                $tpl->SetVariable('dir-path', $this->GetURLFor('Display', array('path' => $_path), false));
                $tpl->ParseBlock('filebrowser/tree');
            }

            if ($path == $_path && !empty($dbFile)) {
                $tpl->SetVariable('text', $this->ParseText($dbFile['description'], 'FileBrowser'));
            }
        }

        $limit  = (int) $this->GetRegistry('views_limit');
        $offset = ($page - 1) * $limit;
        $items = $model->ReadDir($path, $limit, $offset);
        if (!Jaws_Error::IsError($items)) {
            $xss  = $GLOBALS['app']->loadClass('XSS', 'Jaws_XSS');
            $date = $GLOBALS['app']->loadDate();
            foreach ($items as $item) {
                $tpl->SetBlock('filebrowser/item');
                $tpl->SetVariable('icon',  $item['icon']);
                $tpl->SetVariable('name',  $xss->filter($item['filename']));
                $tpl->SetVariable('title', $xss->filter($item['title']));
                if ($item['is_dir']) {
                    $relative = $xss->filter($item['relative']) . '/';
                    $url = $this->GetURLFor('Display', array('path' => $relative), false);
                    $tpl->SetVariable('url', $url);
                } else {
                    if (empty($item['id'])) {
                        $tpl->SetVariable('url', $xss->filter($item['url']));
                    } else {
                        $fid = empty($item['fast_url']) ? $item['id'] : $xss->filter($item['fast_url']);
                        $tpl->SetVariable('url', $this->GetURLFor('Download',
                                                                  array('id' => $fid),
                                                                  false));
                        $tpl->SetBlock('filebrowser/item/info');
                        $tpl->SetVariable('lbl_info', _t('FILEBROWSER_FILEINFO'));
                        $tpl->SetVariable('info_url', $this->GetURLFor('FileInfo',
                                                                       array('id' => $fid),
                                                                       false));
                        $tpl->ParseBlock('filebrowser/item/info');
                    }
                }

                $tpl->SetVariable('date', $date->Format($item['date']));
                $tpl->SetVariable('size', $item['size']);
                $tpl->ParseBlock('filebrowser/item');
            }
        }

        if ($tpl->VariableExists('navigation')) {
            $total  = $model->GetDirContentsCount($path);
            $params = array('path'  => $path);
            $tpl->SetVariable('navigation',
                              $this->GetNumberedPageNavigation($page, $limit, $total, 'Display', $params));
        }

        $tpl->ParseBlock('filebrowser');
        return $tpl->Get();
    }

    /**
     * Get page navigation links
     * 
     * @access  private
     * @param   string  $page
     * @param   string  $page_size
     * @param   string  $total
     * @param   string  $action
     * @param   array   $params
     * @return  string  XHTML template content
     */
    function GetNumberedPageNavigation($page, $page_size, $total, $action, $params = array())
    {
        $tpl = new Jaws_Template('gadgets/FileBrowser/templates/');
        $tpl->Load('PageNavigation.html');
        $tpl->SetBlock('pager');

        $model = $GLOBALS['app']->LoadGadget('FileBrowser', 'Model');
        $pager = $model->GetEntryPagerNumbered($page, $page_size, $total);
        if (count($pager) > 0) {
            $tpl->SetBlock('pager/numbered-navigation');
            $tpl->SetVariable('total', _t('FILEBROWSER_ENTRIES_COUNT', $pager['total']));

            $pager_view = '';
            foreach ($pager as $k => $v) {
                $tpl->SetBlock('pager/numbered-navigation/item');
                $params['page'] = $v;
                if ($k == 'next') {
                    if ($v) {
                        $tpl->SetBlock('pager/numbered-navigation/item/next');
                        $tpl->SetVariable('lbl_next', _t('GLOBAL_NEXT'));
                        $url = $this->GetURLFor($action, $params);
                        $tpl->SetVariable('url_next', $url);
                        $tpl->ParseBlock('pager/numbered-navigation/item/next');
                    } else {
                        $tpl->SetBlock('pager/numbered-navigation/item/no_next');
                        $tpl->SetVariable('lbl_next', _t('GLOBAL_NEXT'));
                        $tpl->ParseBlock('pager/numbered-navigation/item/no_next');
                    }
                } elseif ($k == 'previous') {
                    if ($v) {
                        $tpl->SetBlock('pager/numbered-navigation/item/previous');
                        $tpl->SetVariable('lbl_previous', _t('GLOBAL_PREVIOUS'));
                        $url = $this->GetURLFor($action, $params);
                        $tpl->SetVariable('url_previous', $url);
                        $tpl->ParseBlock('pager/numbered-navigation/item/previous');
                    } else {
                        $tpl->SetBlock('pager/numbered-navigation/item/no_previous');
                        $tpl->SetVariable('lbl_previous', _t('GLOBAL_PREVIOUS'));
                        $tpl->ParseBlock('pager/numbered-navigation/item/no_previous');
                    }
                } elseif ($k == 'separator1' || $k == 'separator2') {
                    $tpl->SetBlock('pager/numbered-navigation/item/page_separator');
                    $tpl->ParseBlock('pager/numbered-navigation/item/page_separator');
                } elseif ($k == 'current') {
                    $tpl->SetBlock('pager/numbered-navigation/item/page_current');
                    $url = $this->GetURLFor($action, $params);
                    $tpl->SetVariable('lbl_page', $v);
                    $tpl->SetVariable('url_page', $url);
                    $tpl->ParseBlock('pager/numbered-navigation/item/page_current');
                } elseif ($k != 'total' && $k != 'next' && $k != 'previous') {
                    $tpl->SetBlock('pager/numbered-navigation/item/page_number');
                    $url = $this->GetURLFor($action, $params);
                    $tpl->SetVariable('lbl_page', $v);
                    $tpl->SetVariable('url_page', $url);
                    $tpl->ParseBlock('pager/numbered-navigation/item/page_number');
                }
                $tpl->ParseBlock('pager/numbered-navigation/item');
            }

            $tpl->ParseBlock('pager/numbered-navigation');
        }

        $tpl->ParseBlock('pager');

        return $tpl->Get();
    }

    /**
     * Action for display file info
     *
     * @access  public
     * @return  string  XHTML template content with titles and contents
     */
    function FileInfo()
    {
        $request =& Jaws_Request::getInstance();
        $id = $request->get('id', 'get');

        $xss  = $GLOBALS['app']->loadClass('XSS', 'Jaws_XSS');
        $id = $xss->defilter($id, true);

        $model = $GLOBALS['app']->LoadGadget('FileBrowser', 'Model');
        $dbInfo = $model->DBFileInfoByIndex($id);
        if (Jaws_Error::IsError($dbInfo) || empty($dbInfo)) {
            return false;
        }

        $date = $GLOBALS['app']->loadDate();
        $tpl = new Jaws_Template('gadgets/FileBrowser/templates/');
        $tpl->Load('FileBrowser.html');
        $tpl->SetBlock('fileinfo');

        $Info = $model->GetFileProperties($dbInfo['path'], $dbInfo['filename']);

        $tpl->SetVariable('icon',  $Info['mini_icon']);
        $tpl->SetVariable('name',  $xss->filter($Info['filename']));
        $tpl->SetVariable('title', $xss->filter($dbInfo['title']));
        $tpl->SetVariable('url',   $xss->filter($Info['url']));
        $tpl->SetVariable('date',  $date->Format($Info['date']));
        $tpl->SetVariable('size',  $Info['size']);
        $tpl->SetVariable('text',  $this->ParseText($dbInfo['description'], 'FileBrowser'));

        $locationTree = $model->GetCurrentRootDir($dbInfo['path']);
        if (Jaws_Error::IsError($locationTree)) {
            return false;
        }

        $parentPath = '';
        $tpl->SetVariable('location', _t('FILEBROWSER_LOCATION'));
        foreach ($locationTree as $path => $dir) {
            if (!empty($dir) && $path{0} == '/') {
                $path = substr($path, 1);
            }

            $dbFile = $model->DBFileInfo($parentPath, $dir);
            if (Jaws_Error::IsError($dbFile) || empty($dbFile)) {
                $dirTitle = $dir;
            } else {
                $dirTitle = $dbFile['title'];
            }

            $parentPath = $path;
            if (empty($path)) {
                $tpl->SetVariable('root', _t('FILEBROWSER_ROOT'));
                $tpl->SetVariable('root-path', $this->GetURLFor('Display', array('path' => $path), false));
            } else {
                $tpl->SetBlock('fileinfo/tree');
                $tpl->SetVariable('dir-name', $dirTitle);
                $tpl->SetVariable('dir-path', $this->GetURLFor('Display', array('path' => $path), false));
                $tpl->ParseBlock('fileinfo/tree');
            }
        }

        $tpl->ParseBlock('fileinfo');
        return $tpl->Get();
    }

    /**
     * Action for providing download file
     *
     * @access  public
     * @return  string   Requested file content or HTML error page
     */
    function Download()
    {
        $request =& Jaws_Request::getInstance();
        $id = $request->get('id', 'get');

        require_once JAWS_PATH . 'include/Jaws/HTTPError.php';
        $xss  = $GLOBALS['app']->loadClass('XSS', 'Jaws_XSS');
        $id = $xss->defilter($id, true);

        $fModel = $GLOBALS['app']->LoadGadget('FileBrowser', 'Model');
        $iFile  = $fModel->DBFileInfoByIndex($id);
        if (Jaws_Error::IsError($iFile)) {
            $this->SetActionMode('Download', 'NormalAction', 'StandaloneAction');
            return Jaws_HTTPError::Get(500);
        }

        if (!empty($iFile)) {
            if ($iFile['path'] == '/') {
                $iFile['path'] = '';
            }
            $filepath = $fModel->GetFileBrowserRootDir() . $iFile['path'] . $iFile['filename'];
            if (file_exists($filepath)) {
                // increase download hits
                $fModel->HitFileDownload($iFile['id']);
                if (Jaws_Utils::Download($filepath, $iFile['filename'])) {
                    return;
                }

                $this->SetActionMode('Download', 'NormalAction', 'StandaloneAction');
                return Jaws_HTTPError::Get(500);
            }
        }

        $this->SetActionMode('Download', 'NormalAction', 'StandaloneAction');
        return Jaws_HTTPError::Get(404);
    }

}
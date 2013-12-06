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
class FileBrowser_Actions_Files extends Jaws_Gadget_Action
{

    /**
     * Prints all the files with their titles and contents
     *
     * @access  public
     * @return  mixed  XHTML template content with titles and contents or false or error
     */
    function Display()
    {
        if ($this->gadget->registry->fetch('frontend_avail') != 'true') {
            return false;
        }

        if (!$this->gadget->GetPermission('OutputAccess')) {
            if ($GLOBALS['app']->Session->Logged()) {
                return _t('GLOBAL_ERROR_ACCESS_DENIED');
            } else {
                return _t('GLOBAL_ERROR_ACCESS_RESTRICTED',
                    $GLOBALS['app']->Map->GetURLFor('Users', 'LoginBox'),
                    $GLOBALS['app']->Map->GetURLFor('Users', 'Registration'));
            }
        }

        $path = jaws()->request->fetch('path');
        $path = trim((string)$path, '/');
        $page = jaws()->request->fetch('page', 'get');
        if (is_null($page) || $page <= 0 ) {
            $page = 1;
        }

        $dModel = $this->gadget->model->load('Directory');
        $fModel = $this->gadget->model->load('Files');
        $locationTree = $dModel->GetCurrentRootDir($path);
        if (Jaws_Error::IsError($locationTree)) {
            return false;
        }

        $tpl = $this->gadget->template->load('FileBrowser.html');
        $tpl->SetBlock('filebrowser');
        $tpl->SetVariable('title', _t('FILEBROWSER_NAME'));
        $this->SetTitle(_t('FILEBROWSER_NAME'));

        $parentPath = '';
        $tpl->SetVariable('location', _t('FILEBROWSER_LOCATION'));
        foreach ($locationTree as $_path => $dir) {
            $_path = trim($_path, '/');
            $dbFile = $fModel->DBFileInfo($parentPath, $dir);
            if (Jaws_Error::IsError($dbFile) || empty($dbFile)) {
                $dirTitle = $dir;
            } else {
                $dirTitle = $dbFile['title'];
                // check directory access permission
                if (empty($parentPath) && !$this->gadget->GetPermission('OutputAccess', $dbFile['id'])) {
                    return Jaws_HTTPError::Get(404);
                }
            }

            $parentPath = $_path;
            if (empty($_path)) {
                $tpl->SetVariable('root', _t('FILEBROWSER_ROOT'));
                $tpl->SetVariable('root-path', $this->gadget->urlMap('Display'));
            } else {
                $tpl->SetBlock('filebrowser/tree');
                $tpl->SetVariable('dir-name', $dirTitle);
                $tpl->SetVariable('dir-path', $this->gadget->urlMap('Display', array('path' => $_path)));
                $tpl->ParseBlock('filebrowser/tree');
            }

            if ($path == $_path && !empty($dbFile)) {
                $tpl->SetVariable('text', $this->gadget->ParseText($dbFile['description']));
            }
        }

        $limit  = (int) $this->gadget->registry->fetch('views_limit');
        $offset = ($page - 1) * $limit;
        $items = $dModel->ReadDir($path, $limit, $offset);
        if (!Jaws_Error::IsError($items)) {
            $date = Jaws_Date::getInstance();
            foreach ($items as $item) {
                $tpl->SetBlock('filebrowser/item');
                $tpl->SetVariable('icon',  $item['icon']);
                $tpl->SetVariable('name',  Jaws_XSS::filter($item['filename']));
                $tpl->SetVariable('title', Jaws_XSS::filter($item['title']));
                if ($item['is_dir']) {
                    $relative = Jaws_XSS::filter($item['relative']) . '/';
                    $url = $this->gadget->urlMap('Display', array('path' => $relative));
                    $tpl->SetVariable('url', $url);
                } else {
                    if (empty($item['id'])) {
                        $tpl->SetVariable('url', Jaws_XSS::filter($item['url']));
                    } else {
                        $fid = empty($item['fast_url']) ? $item['id'] : Jaws_XSS::filter($item['fast_url']);
                        $tpl->SetVariable('url', $this->gadget->urlMap('Download',
                            array('id' => $fid)));
                        $tpl->SetBlock('filebrowser/item/info');
                        $tpl->SetVariable('lbl_info', _t('FILEBROWSER_FILEINFO'));
                        $tpl->SetVariable('info_url', $this->gadget->urlMap('FileInfo',
                            array('id' => $fid)));
                        $tpl->ParseBlock('filebrowser/item/info');
                    }
                }

                $tpl->SetVariable('date', $date->Format($item['date']));
                $tpl->SetVariable('size', $item['size']);
                $tpl->ParseBlock('filebrowser/item');
            }
        }

        if ($tpl->VariableExists('navigation')) {
            $total  = $dModel->GetDirContentsCount($path);
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
        $tpl = $this->gadget->template->load('PageNavigation.html');
        $tpl->SetBlock('pager');

        $model = $this->gadget->model->load('Files');
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
                        $url = $this->gadget->urlMap($action, $params);
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
                        $url = $this->gadget->urlMap($action, $params);
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
                    $url = $this->gadget->urlMap($action, $params);
                    $tpl->SetVariable('lbl_page', $v);
                    $tpl->SetVariable('url_page', $url);
                    $tpl->ParseBlock('pager/numbered-navigation/item/page_current');
                } elseif ($k != 'total' && $k != 'next' && $k != 'previous') {
                    $tpl->SetBlock('pager/numbered-navigation/item/page_number');
                    $url = $this->gadget->urlMap($action, $params);
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
        $id = jaws()->request->fetch('id', 'get');
        $id = Jaws_XSS::defilter($id);

        $fModel = $this->gadget->model->load('Files');
        $dModel = $this->gadget->model->load('Directory');
        $dbInfo = $fModel->DBFileInfoByIndex($id);
        if (Jaws_Error::IsError($dbInfo) || empty($dbInfo)) {
            return false;
        }

        $date = Jaws_Date::getInstance();
        $tpl = $this->gadget->template->load('FileBrowser.html');
        $tpl->SetBlock('fileinfo');

        $Info = $fModel->GetFileProperties($dbInfo['path'], $dbInfo['filename']);

        $tpl->SetVariable('icon',  $Info['mini_icon']);
        $tpl->SetVariable('name',  Jaws_XSS::filter($Info['filename']));
        $tpl->SetVariable('title', Jaws_XSS::filter($dbInfo['title']));
        $tpl->SetVariable('url',   Jaws_XSS::filter($Info['url']));
        $tpl->SetVariable('date',  $date->Format($Info['date']));
        $tpl->SetVariable('size',  $Info['size']);
        $tpl->SetVariable('text',  $this->gadget->ParseText($dbInfo['description']));

        $locationTree = $dModel->GetCurrentRootDir($dbInfo['path']);
        if (Jaws_Error::IsError($locationTree)) {
            return false;
        }

        $parentPath = '';
        $tpl->SetVariable('location', _t('FILEBROWSER_LOCATION'));
        foreach ($locationTree as $path => $dir) {
            if (!empty($dir) && $path{0} == '/') {
                $path = substr($path, 1);
            }

            $dbFile = $fModel->DBFileInfo($parentPath, $dir);
            if (Jaws_Error::IsError($dbFile) || empty($dbFile)) {
                $dirTitle = $dir;
            } else {
                $dirTitle = $dbFile['title'];
            }

            $parentPath = $path;
            if (empty($path)) {
                $tpl->SetVariable('root', _t('FILEBROWSER_ROOT'));
                $tpl->SetVariable('root-path', $this->gadget->urlMap('Display', array('path' => $path), false));
            } else {
                $tpl->SetBlock('fileinfo/tree');
                $tpl->SetVariable('dir-name', $dirTitle);
                $tpl->SetVariable('dir-path', $this->gadget->urlMap('Display', array('path' => $path), false));
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
        $id = jaws()->request->fetch('id', 'get');
        $id = Jaws_XSS::defilter($id);

        $fModel = $this->gadget->model->load('Files');
        $iFile  = $fModel->DBFileInfoByIndex($id);
        if (Jaws_Error::IsError($iFile)) {
            $this->SetActionMode('Download', 'normal', 'standalone');
            return Jaws_HTTPError::Get(500);
        }

        if (!empty($iFile)) {
            $filepath = $fModel->GetFileBrowserRootDir(). $iFile['path']. '/'. $iFile['filename'];
            if (file_exists($filepath)) {
                // increase download hits
                $fModel->HitFileDownload($iFile['id']);
                if (Jaws_Utils::Download($filepath, $iFile['filename'])) {
                    return;
                }

                $this->SetActionMode('Download', 'normal', 'standalone');
                return Jaws_HTTPError::Get(500);
            }
        }

        $this->SetActionMode('Download', 'normal', 'standalone');
        return Jaws_HTTPError::Get(404);
    }
}
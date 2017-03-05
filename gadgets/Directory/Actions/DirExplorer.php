<?php
/**
 * Directory Gadget
 *
 * @category    Gadget
 * @package     Directory
 */
class Directory_Actions_DirExplorer extends Jaws_Gadget_Action
{
    /**
     * Builds directory and file navigation UI
     *
     * @access  public
     * @return  string  XHTML UI
     */
    function DirExplorer()
    {
        $browserLayout = new Jaws_Layout();
        $browserLayout->Load('gadgets/Directory/Templates', 'DirExplorer.html');
        $browserLayout->addScript('gadgets/Directory/Resources/index.js');
        $this->gadget->define('type', jaws()->request->fetch('type', 'get'));
        $tpl = $browserLayout->_Template;
        // bookmark default layout
        $mainLayout = $GLOBALS['app']->Layout;
        $GLOBALS['app']->Layout = $browserLayout;
        if ($GLOBALS['app']->Session->Logged()) {
            $tpl->SetBlock('layout/upload');
            $tpl->SetVariable('lbl_upload', _t('DIRECTORY_UPLOAD_FILE'));
            $tpl->ParseBlock('layout/upload');
        }
        $tpl->SetVariable('referrer', bin2hex(Jaws_Utils::getRequestURL(true)));
        $tpl->SetVariable('lbl_file', _t('DIRECTORY_FILE'));
        $tpl->SetVariable('lbl_thumbnail', _t('DIRECTORY_THUMBNAIL'));
        $tpl->SetVariable('lbl_title', _t('DIRECTORY_FILE_TITLE'));
        $tpl->SetVariable('lbl_description', _t('DIRECTORY_FILE_DESC'));
        $tpl->SetVariable('lbl_tags', _t('DIRECTORY_FILE_TAGS'));
        $tpl->SetVariable('lbl_cancel', _t('GLOBAL_CANCEL'));
        $tpl->SetVariable('lbl_ok', _t('GLOBAL_OK'));

        $description =& $GLOBALS['app']->LoadEditor('Directory', 'description', '', false);
        $description->setId('description');
        $description->TextArea->SetRows(8);
        $tpl->SetVariable('description', $description->Get());

        // restore default layout
        $GLOBALS['app']->Layout = $mainLayout;
        return $browserLayout->Get(true);
    }

    /**
     * Get contacts list
     *
     * @access  public
     * @return  JSON
     */
    function GetDirectory()
    {
        $params = array(
            'user' => (int)$GLOBALS['app']->Session->GetAttribute('user'),
            'file_type' => jaws()->request->fetch('type'),
            'public' => false,
        );

        $modelFiles = $this->gadget->model->load('Files');
        $files = $modelFiles->GetFiles($params);
        foreach($files as $key => $file) {
            $file['url'] = $this->gadget->urlMap(
                'Download',
                array('id' => $file['id'], 'key' => $file['key'])
            );
            $file['src'] = $modelFiles->GetThumbnailURL($file['host_filename']);
            $files[$key] = $file;
        }
        $total = $this->gadget->model->load('Files')->GetFiles($params, true);

        return $GLOBALS['app']->Session->GetResponse(
            '',
            RESPONSE_NOTICE,
            array(
                'total'   => $total,
                'records' => $files
            )
        );
    }

}
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
     * @param   int     $type       File type (for normal action = null)
     * @param   int     $orderBy    Order by
     * @param   int     $limit      Forms limit
     * @access  public
     * @return  string  XHTML UI
     */
    function DirExplorer($type = null, $orderBy = 0, $limit = 0)
    {
        $browserLayout = new Jaws_Layout();
        $browserLayout->Load('gadgets/Directory/Templates', 'DirExplorer.html');
        $browserLayout->addScript('gadgets/Directory/Resources/index.js');
        $browserLayout->_Template->SetVariable(
            'referrer',
            bin2hex(Jaws_Utils::getRequestURL(true))
        );
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
            'public' => false
        );

        $modelFiles = $this->gadget->model->load('Files');
        $files = $modelFiles->GetFiles($params);
        foreach($files as $key => $file) {
            $file['url'] = $this->gadget->urlMap('Download', array('id' => $file['id']));
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
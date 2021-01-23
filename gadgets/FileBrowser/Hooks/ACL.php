<?php
/**
 * FileBrowser - ACL hook
 *
 * @category    GadgetHook
 * @package     FileBrowser
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2008-2021 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class FileBrowser_Hooks_ACL extends Jaws_Gadget_Hook
{
    /**
     * Defines translate statements of dynamic ACL keys
     *
     * @access  public
     * @return  void
     */
    function Execute()
    {
        $language = $this->gadget->registry->fetch('admin_language', 'Settings');
        $model = $this->gadget->model->load('Directory');
        $items = $model->ReadDir();
        if (!Jaws_Error::IsError($items)) {
            foreach ($items as $item) {
                if ($item['is_dir'] && !empty($item['id'])) {
                    $this->gadget->translate->insert(
                        'ACL_OUTPUTACCESS_'. $item['id'],
                        _t('FILEBROWSER_DIRECTORY_ACCESS', $item['title'])
                    );
                }
            }
        }

    }

}
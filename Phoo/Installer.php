<?php
/**
 * Phoo Installer
 *
 * @category    GadgetModel
 * @package     Phoo
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2012 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class Phoo_Installer extends Jaws_Gadget_Installer
{
    /**
     * Install Phoo gadget in Jaws
     *
     * @access  public
     * @return  mixed   True on successful installation, Jaws_Error otherwise
     */
    function Install()
    {
        if (!Jaws_Utils::is_writable(JAWS_DATA)) {
            return new Jaws_Error(_t('GLOBAL_ERROR_FAILED_DIRECTORY_UNWRITABLE', JAWS_DATA));
        }

        $new_dir = JAWS_DATA . 'phoo' . DIRECTORY_SEPARATOR;
        if (!Jaws_Utils::mkdir($new_dir)) {
            return new Jaws_Error(_t('GLOBAL_ERROR_FAILED_CREATING_DIR', $new_dir), _t('PHOO_NAME'));
        }

        $result = $this->installSchema('schema.xml');
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        // Registry keys
        $this->gadget->AddRegistry('default_action',    'AlbumList');
        $this->gadget->AddRegistry('thumbsize',         '133x100');
        $this->gadget->AddRegistry('mediumsize',        '400x300');
        $this->gadget->AddRegistry('moblog_album',      '');
        $this->gadget->AddRegistry('moblog_limit',      '10');
        $this->gadget->AddRegistry('photoblog_album',   '');
        $this->gadget->AddRegistry('photoblog_limit',   '5');
        $this->gadget->AddRegistry('allow_comments',    'true');
        $this->gadget->AddRegistry('published',         'true');
        $this->gadget->AddRegistry('plugabble',         'true');
        $this->gadget->AddRegistry('show_exif_info',    'false');
        $this->gadget->AddRegistry('keep_original',     'true');
        $this->gadget->AddRegistry('thumbnail_limit',   '0');
        $this->gadget->AddRegistry('use_antispam',      'true');
        $this->gadget->AddRegistry('comment_status',    'approved');
        $this->gadget->AddRegistry('albums_order_type', 'name');
        $this->gadget->AddRegistry('photos_order_type', 'id');

        return true;
    }

    /**
     * Uninstalls the gadget
     *
     * @access  public
     * @return  mixed   True on Success or Jaws_Error on Failure
     */
    function Uninstall()
    {
        $tables = array('phoo_album',
                        'phoo_image',
                        'phoo_image_album');
        foreach ($tables as $table) {
            $result = $GLOBALS['db']->dropTable($table);
            if (Jaws_Error::IsError($result)) {
                $gName  = _t('PHOO_NAME');
                $errMsg = _t('GLOBAL_ERROR_GADGET_NOT_UNINSTALLED', $gName);
                $GLOBALS['app']->Session->PushLastResponse($errMsg, RESPONSE_ERROR);
                return new Jaws_Error($errMsg, $gName);
            }
        }

        // Registry keys
        $this->gadget->DelRegistry('default_action');
        $this->gadget->DelRegistry('thumbsize');
        $this->gadget->DelRegistry('mediumsize');
        $this->gadget->DelRegistry('moblog_album');
        $this->gadget->DelRegistry('moblog_limit');
        $this->gadget->DelRegistry('photoblog_album');
        $this->gadget->DelRegistry('photoblog_limit');
        $this->gadget->DelRegistry('allow_comments');
        $this->gadget->DelRegistry('published');
        $this->gadget->DelRegistry('plugabble');
        $this->gadget->DelRegistry('show_exif_info');
        $this->gadget->DelRegistry('keep_original');
        $this->gadget->DelRegistry('thumbnail_limit');
        $this->gadget->DelRegistry('use_antispam');
        $this->gadget->DelRegistry('comment_status');
        $this->gadget->DelRegistry('albums_order_type');
        $this->gadget->DelRegistry('photos_order_type');

        return true;
    }

    /**
     * Upgrades the gadget
     *
     * @access  public
     * @param   string  $old    Current version (in registry)
     * @param   string  $new    New version (in the $gadgetInfo file)
     * @return  mixed   True on Success or Jaws_Error onFailure
     */
    function Upgrade($old, $new)
    {
        if (version_compare($old, '0.8.0', '<')) {
            $result = $this->installSchema('0.8.0.xml', '', "$old.xml");
            if (Jaws_Error::IsError($result)) {
                return $result;
            }

            $this->gadget->AddRegistry('image_quality', '75');
        }

        if ($old == '0.7.0') {
            // Update allow_comments and status in all albums.
            $params = array('published' => true, 'allow_comments' => true);
            $sql = "UPDATE [[phoo_album]] SET [published] = {published}, [allow_comments] = {allow_comments}";
            $result   = $GLOBALS['db']->query($sql, $params);
            if (Jaws_Error::IsError($result)) {
                return new Jaws_Error(_t('GLOBAL_ERROR_FAILED_QUERY_FILE', '(Update phoo_album SET published = true, allow_comments = true)'),
                                     _t('PHOO_NAME'));
            }

            $this->gadget->AddRegistry('comment_status', 'approved');
            $this->gadget->AddRegistry('order_type','name');
        }

        if (version_compare($old, '0.8.1', '<')) {
            $albums_order_type = $this->gadget->GetRegistry('order_type');
            $this->gadget->AddRegistry('albums_order_type',
                                              Jaws_Error::IsError($albums_order_type)? 'name' : $albums_order_type);
            $this->gadget->AddRegistry('photos_order_type', 'id');
            $this->gadget->DelRegistry('order_type');
        }

        if (version_compare($old, '0.8.2', '<')) {
            // ACL keys
            $GLOBALS['app']->ACL->NewKey('/ACL/gadgets/Phoo/ManagePhotos',  'false');
        }

        if (version_compare($old, '0.8.3', '<')) {
            $base_path = $GLOBALS['app']->getDataURL() . 'phoo/';
            $sql = '
                SELECT [id], [filename]
                FROM [[phoo_image]]';
            $photos = $GLOBALS['db']->queryAll($sql);
            if (!Jaws_Error::IsError($photos)) {
                foreach ($photos as $photo) {
                    if (!empty($photo['filename'])) {
                        if (strpos($photo['filename'], $base_path) !== 0) {
                            continue;
                        }
                        $photo['filename'] = substr($photo['filename'], strlen($base_path));
                        $sql = '
                            UPDATE [[phoo_image]] SET
                                [filename] = {filename}
                            WHERE [id] = {id}';
                        $res = $GLOBALS['db']->query($sql, $photo);
                    }
                }
            }
        }

        if (version_compare($old, '0.8.4', '<')) {
            $result = $this->installSchema('schema.xml', '', "0.8.0.xml");
            if (Jaws_Error::IsError($result)) {
                return $result;
            }

            $this->gadget->DelRegistry('resize_method');
            $this->gadget->DelRegistry('image_quality');
        }

        return true;
    }

}
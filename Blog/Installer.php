<?php
/**
 * Blog Installer
 *
 * @category    GadgetModel
 * @package     Blog
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2012-2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class Blog_Installer extends Jaws_Gadget_Installer
{
    /**
     * Install Blog gadget in Jaws
     *
     * @access  public
     * @return  mixed   True on successful installation, Jaws_Error otherwise
     */
    function Install()
    {
        if (!Jaws_Utils::is_writable(JAWS_DATA)) {
            return new Jaws_Error(_t('GLOBAL_ERROR_FAILED_DIRECTORY_UNWRITABLE', JAWS_DATA));
        }

        $new_dir = JAWS_DATA . 'xml' . DIRECTORY_SEPARATOR;
        if (!Jaws_Utils::mkdir($new_dir)) {
            return new Jaws_Error(_t('GLOBAL_ERROR_FAILED_CREATING_DIR', $new_dir), _t('BLOG_NAME'));
        }

        $result = $this->installSchema('schema.xml');
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        $variables = array();
        $variables['timestamp'] = $GLOBALS['db']->Date();

        $result = $this->installSchema('insert.xml', $variables, 'schema.xml', true);
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        // Registry keys
        $this->gadget->registry->insert('columns',                   '1');
        $this->gadget->registry->insert('default_view',              'last_entries');
        $this->gadget->registry->insert('last_entries_limit',        '20');
        $this->gadget->registry->insert('popular_limit',             '10');
        $this->gadget->registry->insert('xml_limit',                 '10');
        $this->gadget->registry->insert('default_category',          '1');
        $this->gadget->registry->insert('allow_comments',            'true');
        $this->gadget->registry->insert('comment_status',            'approved');
        $this->gadget->registry->insert('last_comments_limit',       '20');
        $this->gadget->registry->insert('last_recentcomments_limit', '20');
        $this->gadget->registry->insert('generate_xml',              'true');
        $this->gadget->registry->insert('generate_category_xml',     'true');
        $this->gadget->registry->insert('trackback',                 'true');
        $this->gadget->registry->insert('trackback_status',          'approved');
        $this->gadget->registry->insert('plugabble',                 'true');
        $this->gadget->registry->insert('use_antispam',              'true');
        $this->gadget->registry->insert('pingback',                  'true');

        return true;
    }

    /**
     * Uninstalls the gadget
     *
     * @access  public
     * @return  mixed  True on success and Jaws_Error otherwise
     */
    function Uninstall()
    {
        $tables = array('blog',
                        'blog_trackback',
                        'blog_category',
                        'blog_entrycat');
        foreach ($tables as $table) {
            $result = $GLOBALS['db']->dropTable($table);
            if (Jaws_Error::IsError($result)) {
                $gName  = _t('BLOG_NAME');
                $errMsg = _t('GLOBAL_ERROR_GADGET_NOT_UNINSTALLED', $gName);
                $GLOBALS['app']->Session->PushLastResponse($errMsg, RESPONSE_ERROR);
                return new Jaws_Error($errMsg, $gName);
            }
        }

        // Registry keys
        $this->gadget->registry->delete('columns');
        $this->gadget->registry->delete('default_view');
        $this->gadget->registry->delete('last_entries_limit');
        $this->gadget->registry->delete('popular_limit');
        $this->gadget->registry->delete('xml_limit');
        $this->gadget->registry->delete('default_category');
        $this->gadget->registry->delete('allow_comments');
        $this->gadget->registry->delete('comment_status');
        $this->gadget->registry->delete('last_comments_limit');
        $this->gadget->registry->delete('last_recentcomments_limit');
        $this->gadget->registry->delete('generate_xml');
        $this->gadget->registry->delete('generate_category_xml');
        $this->gadget->registry->delete('trackback');
        $this->gadget->registry->delete('trackback_status');
        $this->gadget->registry->delete('plugabble');
        $this->gadget->registry->delete('use_antispam');
        $this->gadget->registry->delete('pingback');

        return true;
    }

    /**
     * Upgrades the gadget
     *
     * @access  public
     * @param   string  $old    Current version (in registry)
     * @param   string  $new    New version (in the $gadgetInfo file)
     * @return  mixed   True on Success, Jaws_Error on Failure
     */
    function Upgrade($old, $new)
    {
        if (version_compare($old, '0.8.8', '<')) {
            $result = $this->installSchema('schema.xml', '', "0.8.4.xml");
            if (Jaws_Error::IsError($result)) {
                return $result;
            }
        }

        if (version_compare($old, '0.8.9', '<')) {
            // ACL keys
            $this->gadget->acl->insert('ModifyPublishedEntries');
        }

        return true;
    }

}
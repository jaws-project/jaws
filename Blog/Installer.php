<?php
/**
 * Blog Installer
 *
 * @category    GadgetModel
 * @package     Blog
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2012 Jaws Development Group
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
        $this->gadget->AddRegistry('columns',                   '1');
        $this->gadget->AddRegistry('default_view',              'last_entries');
        $this->gadget->AddRegistry('last_entries_limit',        '20');
        $this->gadget->AddRegistry('popular_limit',             '10');
        $this->gadget->AddRegistry('xml_limit',                 '10');
        $this->gadget->AddRegistry('default_category',          '1');
        $this->gadget->AddRegistry('allow_comments',            'true');
        $this->gadget->AddRegistry('comment_status',            'approved');
        $this->gadget->AddRegistry('last_comments_limit',       '20');
        $this->gadget->AddRegistry('last_recentcomments_limit', '20');
        $this->gadget->AddRegistry('generate_xml',              'true');
        $this->gadget->AddRegistry('generate_category_xml',     'true');
        $this->gadget->AddRegistry('trackback',                 'true');
        $this->gadget->AddRegistry('trackback_status',          'approved');
        $this->gadget->AddRegistry('plugabble',                 'true');
        $this->gadget->AddRegistry('use_antispam',              'true');
        $this->gadget->AddRegistry('pingback',                  'true');

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
        $this->gadget->DelRegistry('columns');
        $this->gadget->DelRegistry('default_view');
        $this->gadget->DelRegistry('last_entries_limit');
        $this->gadget->DelRegistry('popular_limit');
        $this->gadget->DelRegistry('xml_limit');
        $this->gadget->DelRegistry('default_category');
        $this->gadget->DelRegistry('allow_comments');
        $this->gadget->DelRegistry('comment_status');
        $this->gadget->DelRegistry('last_comments_limit');
        $this->gadget->DelRegistry('last_recentcomments_limit');
        $this->gadget->DelRegistry('generate_xml');
        $this->gadget->DelRegistry('generate_category_xml');
        $this->gadget->DelRegistry('trackback');
        $this->gadget->DelRegistry('trackback_status');
        $this->gadget->DelRegistry('plugabble');
        $this->gadget->DelRegistry('use_antispam');
        $this->gadget->DelRegistry('pingback');

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
            $GLOBALS['app']->ACL->NewKey('/ACL/gadgets/Blog/ModifyPublishedEntries',  'false');
        }

        return true;
    }

}
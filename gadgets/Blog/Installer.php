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
     * Gadget Registry keys
     *
     * @var     array
     * @access  private
     */
    var $_RegKeys = array(
        array('columns', '1'),
        array('default_view', 'last_entries'),
        array('last_entries_limit', '20'),
        array('popular_limit', '10'),
        array('xml_limit', '10'),
        array('default_category', '1'),
        array('allow_comments', 'true'),
        array('comment_status', 'approved'),
        array('last_comments_limit', '20'),
        array('last_recentcomments_limit', '20'),
        array('generate_xml', 'true'),
        array('generate_category_xml', 'true'),
        array('trackback', 'true'),
        array('trackback_status', 'approved'),
        array('plugabble', 'true'),
        array('use_antispam', 'true'),
        array('pingback', 'true'),
    );

    /**
     * Gadget ACLs
     *
     * @var     array
     * @access  private
     */
    var $_ACLKeys = array(
        'AddEntries',
        'ModifyOthersEntries',
        'DeleteEntries',
        'PublishEntries',
        'ModifyPublishedEntries',
        'ManageComments',
        'ManageTrackbacks',
        'ManageCategories',
        'Settings',
    );

    /**
     * Install the gadget
     *
     * @access  public
     * @param   string  $input_schema       Schema file path
     * @param   array   $input_variables    Schema variables
     * @return  mixed   True on success or Jaws_Error on failure
     */
    function Install($input_schema = '', $input_variables = array())
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

        if (!empty($input_schema)) {
            $result = $this->installSchema($input_schema, $input_variables, 'schema.xml', true);
            if (Jaws_Error::IsError($result)) {
                return $result;
            }
        }

        // Install listener for update comment
        $this->gadget->event->insert('UpdateComment');

        $this->gadget->acl->insert('CategoryAccess', 1, true);
        $this->gadget->acl->insert('CategoryManage', 1, true);
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
                return new Jaws_Error($errMsg, $gName);
            }
        }

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
        if (version_compare($old, '0.9.0', '<')) {
            // Update layout actions
            $layoutModel = Jaws_Gadget::getInstance('Layout')->model->loadAdmin('Layout');
            if (!Jaws_Error::isError($layoutModel)) {
                $layoutModel->EditGadgetLayoutAction('Blog', 'CategoryEntries', 'CategoryEntries', 'Posts');
                $layoutModel->EditGadgetLayoutAction('Blog', 'EntriesByCategory', 'CategoryEntries', 'Posts');
                $layoutModel->EditGadgetLayoutAction('Blog', 'CategoriesList', 'CategoriesList', 'Categories');
                $layoutModel->EditGadgetLayoutAction('Blog', 'PopularPosts', 'PopularPosts', 'Posts');
                $layoutModel->EditGadgetLayoutAction('Blog', 'PostsAuthors', 'PostsAuthors', 'Posts');
                $layoutModel->EditGadgetLayoutAction('Blog', 'MonthlyHistory', 'MonthlyHistory', 'DatePosts');
                $layoutModel->EditGadgetLayoutAction('Blog', 'Calendar', 'Calendar', 'DatePosts');
                $layoutModel->EditGadgetLayoutAction('Blog', 'RecentPosts', 'RecentPosts', 'Posts');
                $layoutModel->EditGadgetLayoutAction('Blog', 'ShowTagCloud', 'ShowTagCloud', 'Tags');
            }
        }

        return true;
    }

}
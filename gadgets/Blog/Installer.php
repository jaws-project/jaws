<?php
/**
 * Blog Installer
 *
 * @category    GadgetModel
 * @package     Blog
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2012-2015 Jaws Development Group
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
        array('favorite_limit', '10'),
        array('xml_limit', '10'),
        array('default_category', '1'),
        array('allow_comments', 'true'),
        array('last_comments_limit', '20'),
        array('last_recentcomments_limit', '20'),
        array('generate_xml', 'true'),
        array('generate_category_xml', 'true'),
        array('trackback', 'false'),
        array('trackback_status', 'approved'),
        array('plugabble', 'true'),
        array('use_antispam', 'true'),
        array('pingback', 'false'),
        array('category_image_size', '128x128'),
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
        'ManageTypes',
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

        $new_dir = JAWS_DATA . 'blog' . DIRECTORY_SEPARATOR . 'images';
        if (!Jaws_Utils::mkdir($new_dir, 1)) {
            return new Jaws_Error(_t('GLOBAL_ERROR_FAILED_CREATING_DIR', $new_dir));
        }

        $new_dir = JAWS_DATA . 'blog' . DIRECTORY_SEPARATOR . 'categories';
        if (!Jaws_Utils::mkdir($new_dir, 1)) {
            return new Jaws_Error(_t('GLOBAL_ERROR_FAILED_CREATING_DIR', $new_dir));
        }

        $new_dir = JAWS_DATA . 'xml' . DIRECTORY_SEPARATOR;
        if (!Jaws_Utils::mkdir($new_dir)) {
            return new Jaws_Error(_t('GLOBAL_ERROR_FAILED_CREATING_DIR', $new_dir));
        }

        $result = $this->installSchema('schema.xml');
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        $variables = array();
        $variables['timestamp'] = Jaws_DB::getInstance()->date();

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
            $result = Jaws_DB::getInstance()->dropTable($table);
            if (Jaws_Error::IsError($result)) {
                $errMsg = _t('GLOBAL_ERROR_GADGET_NOT_UNINSTALLED', $this->gadget->title);
                return new Jaws_Error($errMsg);
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
        if (version_compare($old, '1.0.0', '<')) {
            $result = $this->installSchema('1.0.0.xml', array(), '0.9.0.xml');
            if (Jaws_Error::IsError($result)) {
                return $result;
            }

            // set dynamic ACLs of categories
            $catModel = $this->gadget->model->load('Categories');
            $categories = $catModel->GetCategories(false);
            foreach ($categories as $category) {
                $this->gadget->acl->insert('CategoryAccess', $category['id'], true);
                $this->gadget->acl->insert('CategoryManage', $category['id'], true);
            }

            // filling categories filed
            $blogTable = Jaws_ORM::getInstance()->table('blog');
            $blogTable->select('blog.id:integer', 'blog_entrycat.category_id');
            $blogTable->join('blog_entrycat', 'blog.id', 'blog_entrycat.entry_id');
            $result = $blogTable->orderBy('blog.id desc')->fetchAll();
            if (Jaws_Error::IsError($result)) {
                return $result;
            }
            $posts = array();
            foreach ($result as $post) {
                $posts[$post['id']] = (isset($posts[$post['id']])?
                    ($posts[$post['id']].',') : '') . $post['category_id'];
            }

            // update categories filed
            $blogTable = Jaws_ORM::getInstance()->table('blog');
            foreach ($posts as $id => $categories) {
                $blogTable->update(array('categories' => $categories))->where('id', (int)$id)->exec();
            }
        }

        if (version_compare($old, '1.1.0', '<')) {
            $result = $this->installSchema('1.1.0.xml', array(), '1.0.0.xml');
            if (Jaws_Error::IsError($result)) {
                return $result;
            }
        }

        if (version_compare($old, '1.2.0', '<')) {
            $this->gadget->registry->delete('comment_status');
            $this->gadget->registry->insert('recommended', ',Comments,Tags,');

            $new_dir = JAWS_DATA . 'blog' . DIRECTORY_SEPARATOR . 'images';
            if (!Jaws_Utils::mkdir($new_dir, 1)) {
                return new Jaws_Error(_t('GLOBAL_ERROR_FAILED_CREATING_DIR', $new_dir));
            }
        }

        if (version_compare($old, '1.3.0', '<')) {
            $result = $this->installSchema('1.3.0.xml', array(), '1.1.0.xml');
            if (Jaws_Error::IsError($result)) {
                return $result;
            }
        }

        if (version_compare($old, '1.4.0', '<')) {
            // registry keys
            $this->gadget->registry->insert('category_image_size', '128x128');
            // make directories
            $new_dir = JAWS_DATA . 'blog' . DIRECTORY_SEPARATOR . 'categories';
            if (!Jaws_Utils::mkdir($new_dir, 1)) {
                return new Jaws_Error(_t('GLOBAL_ERROR_FAILED_CREATING_DIR', $new_dir));
            }

            // Update layout actions
            $layoutModel = Jaws_Gadget::getInstance('Layout')->model->loadAdmin('Layout');
            if (!Jaws_Error::isError($layoutModel)) {
                $layoutModel->EditGadgetLayoutAction('Blog', 'PopularPosts', 'PopularPosts', 'PopularPosts');
            }
        }

        if (version_compare($old, '1.5.0', '<')) {
            $result = $this->installSchema('schema.xml', array(), '1.3.0.xml');
            if (Jaws_Error::IsError($result)) {
                return $result;
            }
        }

        return true;
    }

}
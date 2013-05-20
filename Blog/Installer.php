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
        $GLOBALS['app']->Listener->AddListener($this->gadget->name, 'UpdateComment');

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
        return true;
    }

}
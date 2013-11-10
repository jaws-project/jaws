<?php
/**
 * Comments Installer
 *
 * @category    GadgetModel
 * @package     Comments
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2012-2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Comments_Installer extends Jaws_Gadget_Installer
{
    /**
     * Gadget Registry keys
     *
     * @var     array
     * @access  private
     */
    var $_RegKeys = array(
        array('allow_duplicate', 'no'),
        array('allow_comments', 'true'),
        array('comments_per_page', '10'),
        array('recent_comment_limit', '10'),
        array('default_comment_status', '1'),
    );

    /**
     * Gadget ACLs
     *
     * @var     array
     * @access  private
     */
    var $_ACLKeys = array(
        'ManageComments',
        'ReplyComments',
        'Settings',
    );

    /**
     * Install the gadget
     *
     * @access  public
     * @param   bool    $upgrade_from_08x   Upgrade from 0.8.x
     * @return  mixed   True on success or Jaws_Error on failure
     */
    function Install($upgrade_from_08x = false)
    {
        // Install listener for removing comments related to uninstalled gadget
        $this->gadget->event->insert('UninstallGadget');

        if ($upgrade_from_08x) {
            return $this->Upgrade('0.8.0', '1.0.0');
        } else {
            $result = $this->installSchema('schema.xml');
            if (Jaws_Error::IsError($result)) {
                return $result;
            }
        }

        return true;
    }

    /**
     * Uninstalls the gadget
     *
     * @access  public
     * @return  bool    Success/Failure (Jaws_Error)
     */
    function Uninstall()
    {
        return true;
    }

    /**
     * Upgrades the gadget
     *
     * @access  public
     * @param   string  $old    Current version (in registry)
     * @param   string  $new    New version (in the $gadgetInfo file)
     * @return  bool    Success/Failure (Jaws_Error)
     */
    function Upgrade($old, $new)
    {
        if (version_compare($old, '0.9.0', '<')) {
            $result = $this->installSchema('0.9.0.xml', '', '0.8.0.xml');
            if (Jaws_Error::IsError($result)) {
                return $result;
            }

            $sql = 'SELECT [id], [gadget], [old_status] FROM [[comments]]';
            $comments = $GLOBALS['db']->queryAll($sql);
            if (Jaws_Error::IsError($comments)) {
                return $comments;
            }

            $gadgetof = array();
            $gadgetof['Blog'] = 'Blog';
            $gadgetof['Phoo'] = 'Phoo';
            $gadgetof['Chatbox'] = 'Shoutbox';

            $actionof = array();
            $actionof['Blog'] = 'Post';
            $actionof['Phoo'] = 'Image';
            $actionof['Chatbox'] = '';

            $statusof = array();
            $statusof['approved'] = Comments_Info::COMMENTS_STATUS_APPROVED;
            $statusof['waiting'] = Comments_Info::COMMENTS_STATUS_WAITING;
            $statusof['spam'] = Comments_Info::COMMENTS_STATUS_SPAM;

            $sql = '
                UPDATE [[comments]] SET
                    [gadget] = {gadget},
                    [action] = {action},
                    [new_status] = {status}
                WHERE [id] = {id}';

            $params = array();
            foreach ($comments as $comment) {
                $params['id'] = $comment['id'];
                $params['gadget'] = $gadgetof[$comment['gadget']];
                $params['action'] = $actionof[$comment['gadget']];
                $params['status'] = $statusof[$comment['old_status']];

                $result = $GLOBALS['db']->query($sql, $params);
                if (Jaws_Error::IsError($result)) {
                    return $result;
                }
            }

            $result = $this->installSchema('1.0.0.xml', '', '0.9.0.xml');
            if (Jaws_Error::IsError($result)) {
                return $result;
            }

            $result = $this->installSchema('schema.xml', '', '1.0.0.xml');
            if (Jaws_Error::IsError($result)) {
                return $result;
            }
        }

        if (version_compare($old, '1.0.0', '<')) {
            $result = $this->installSchema('1.0.0.xml', '', '0.9.0.xml');
            if (Jaws_Error::IsError($result)) {
                return $result;
            }

            $result = $this->installSchema('schema.xml', '', '1.0.0.xml');
            if (Jaws_Error::IsError($result)) {
                return $result;
            }
        }

        if (version_compare($old, '1.1.0', '<')) {
            $result = $this->installSchema('schema.xml', '', '1.0.0.xml');
            if (Jaws_Error::IsError($result)) {
                return $result;
            }
        }

        return true;
    }

}
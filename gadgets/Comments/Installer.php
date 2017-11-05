<?php
/**
 * Comments Installer
 *
 * @category    GadgetModel
 * @package     Comments
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2012-2015 Jaws Development Group
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
        array('order_type', '1'),
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
        $tables = array(
            'comments_details',
            'comments',
        );
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
     * @return  bool    Success/Failure (Jaws_Error)
     */
    function Upgrade($old, $new)
    {
        if (version_compare($old, '1.1.0', '<')) {
            // Registry key
            $this->gadget->registry->insert('order_type', '1');
        }

        if (version_compare($old, '1.2.0', '<')) {
            $result = $this->installSchema('1.2.0.xml', array(), '1.1.0.xml');
            if (Jaws_Error::IsError($result)) {
                return $result;
            }

            // fetch all old comments records
            $oldTable = Jaws_ORM::getInstance()->table('old_comments');
            $oldTable->select(
                'gadget', 'action', 'reference:integer', 'user:integer', 'name', 'email', 'url', 'ip',
                'msg_txt', 'reply', 'replier:integer', 'createtime', 'status:integer'
            );
            $comments = $oldTable->orderBy('gadget', 'action', 'reference')->fetchAll();
            if (Jaws_Error::IsError($comments)) {
                return $comments;
            }

            // preparing to insert comments records to new tables
            $newTable1 = Jaws_ORM::getInstance()->table('comments2');
            $newTable2 = Jaws_ORM::getInstance()->table('comments_details');

            //Start Transaction
            $newTable1->beginTransaction();
            foreach ($comments as $comment) {
                $ctime = strtotime($comment['createtime']);
                //insert/update master record of comment
                $gar = $newTable1->upsert(
                    array(
                        'gadget'     => $comment['gadget'],
                        'action'     => $comment['action'],
                        'reference'  => $comment['reference'],
                        'comments_count' => 0,
                        'restricted'  => false,
                        'allowed'     => true,
                        'last_update' => $ctime
                    ),
                    array(
                        'comments_count' => $newTable1->expr('comments_count + ?', 1)
                    )
                )->exec();
                if (Jaws_Error::IsError($gar)) {
                    return $gar;
                }

                // insert detail of comment
                $result = $newTable2->insert(
                    array(
                        'cid'   => $gar,
                        'user'  => $comment['user'],
                        'name'  => $comment['name'],
                        'email' => $comment['email'],
                        'url'   => $comment['url'],
                        'uip'   => bin2hex(inet_pton($comment['ip'])),
                        'msg_txt' => $comment['msg_txt'],
                        'hash'    => crc32($comment['msg_txt']),
                        'reply'   => $comment['reply'],
                        'replier' => $comment['replier'],
                        'status'  => $comment['status'],
                        'insert_time' => $ctime,
                        'update_time' => $ctime
                    )
                )->exec();
                if (Jaws_Error::IsError($result)) {
                    return $result;
                }
            }

            //Commit Transaction
            $newTable1->commit();

        }

        if (version_compare($old, '1.3.0', '<')) {
            $result = $this->installSchema('1.3.0.xml', array(), '1.2.0.xml');
            if (Jaws_Error::IsError($result)) {
                return $result;
            }

            // Registry
            $this->gadget->registry->delete('allow_duplicate');
        }

        if (version_compare($old, '1.4.0', '<')) {
            /*
            $result = $this->installSchema('schema.xml', array(), '1.3.0.xml');
            if (Jaws_Error::IsError($result)) {
                return $result;
            }
            */

            $objORM = Jaws_ORM::getInstance()->table('comments');
            $entries = $objORM->select('id:integer', 'gadget', 'action', 'reference:integer')->fetchAll();
            if (!Jaws_Error::IsError($entries) || !empty($entries)) {
                foreach ($entries as $entry) {
                    $objHook = Jaws_Gadget::getInstance($entry['gadget'])->hook->load('Comments');
                    if (Jaws_Error::IsError($objHook)) {
                        continue;
                    }

                    $reference = $objHook->Execute($entry['action'], $entry['reference']);
                    if (empty($reference)) {
                        continue;
                    }

                    $result = $objORM->update(
                        array(
                            'reference_title' => $reference['title'],
                            'reference_link'   => $reference['url'],
                        ))->where('id', $entry['id'])
                        ->exec();
                    if (Jaws_Error::IsError($result)) {
                        //do nothing
                    }
                }
            }

        }

        return true;
    }

}
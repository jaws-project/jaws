<?php
/**
 * Directory Gadget
 *
 * @category    GadgetModel
 * @package     Directory
 * @author      Mohsen Khahani <mkhahani@gmail.com>
 * @copyright   2013-2014 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class Directory_Model_Share extends Jaws_Gadget_Model
{
    /**
     * Fetches list of users the file is shared for
     *
     * @access  public
     * @param   int     $id  File ID
     * @return  mixed   Query result
     */
    function GetFileUsers($id)
    {
        $table = Jaws_ORM::getInstance()->table('directory');
        $table->select('users.id', 'users.username');
        $table->join('users', 'directory.user', 'users.id');
        $table->where('directory.reference', $id);
        return $table->fetchAll();
    }

    /**
     * Creates shortcuts of the file for passed users
     *
     * @access  public
     * @param   int     $id     File ID
     * @param   array   $users  Users ID's
     * @return  mixed   True or Jaws_Error
     */
    function UpdateFileUsers($id, $users)
    {
        $table = Jaws_ORM::getInstance()->table('directory');

        // Fetch file info
        $table->select('is_dir:boolean', 'title', 'description', 
            'filename', 'filetype', 'filesize', 'url', 'updatetime',
            'user', 'owner', 'reference', 'shared:boolean');
        $file = $table->where('id', $id)->fetchRow();
        if (Jaws_Error::IsError($file)) {
            return $file;
        }

        // Fetch file users
        $table->reset();
        $table->select('user');
        $table->where('reference', $id);
        $current_users = $table->fetchColumn();
        if (Jaws_Error::IsError($current_users)) {
            return $current_users;
        }
        $old_ids = array_diff($current_users, $users);
        $new_ids = array_diff($users, $current_users);

        // Delete old shortcuts
        if (!empty($old_ids)) {
            $table->reset();
            $table->delete();
            $table->where('reference', $id)->and();
            $table->where('user', $old_ids, 'in');
            $res = $table->exec();
            if (Jaws_Error::IsError($res)) {
                return $res;
            }
        }

        // Create new shortcuts
        if (!empty($new_ids)) {
            $shortcut = $file;
            $shortcut['parent'] = 0;
            $shortcut['shared'] = false;
            $shortcut['reference'] = !empty($file['reference'])? $file['reference'] : $id;
            $shortcut['createtime'] = time();
            foreach ($new_ids as $uid) {
                $shortcut['user'] = $uid;
                $table->reset();
                $res = $table->insert($shortcut)->exec();
                if (Jaws_Error::IsError($res)) {
                    return $res;
                }
            }
        }

        // Update `shared` status
        $shared = !empty($users);
        if ($file['shared'] !== $shared) {
            $table->reset();
            $table->update(array('shared' => $shared));
            $res = $table->where('id', $id)->exec();
            if (Jaws_Error::IsError($res)) {
                return $res;
            }
        }

        return true;
    }
}
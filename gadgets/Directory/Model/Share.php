<?php
/**
 * Directory Gadget
 *
 * @category    GadgetModel
 * @package     Directory
 * @author      Mohsen Khahani <mkhahani@gmail.com>
 * @copyright   2013 Jaws Development Group
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
        $table->select('users.id', 'users.nickname');
        $table->join('users', 'directory.user', 'users.id');
        $table->where('directory.reference', $id);
        return $table->fetchAll();
    }

    /**
     * Creates shortcuts of the file record for passed users
     *
     * @access  public
     * @param   int     $id  File ID
     * @param   array   Users ID's
     * @return  mixed   True or Jaws_Error
     */
    function UpdateFileUsers($id, $users)
    {
        $table = Jaws_ORM::getInstance()->table('directory');
        $table->select('is_dir:boolean', 'title', 'description', 
            'user', 'owner', 'reference', 'shared:boolean');
        $file = $table->fetchRow();
        if (Jaws_Error::IsError($file)) {
            return $file;
        }

        if (!empty($users)) {
            $shortcut = array(
                'parent' => 0,
                'shared' => false,
                'is_dir' => $file['is_dir'],
                'title' => $file['title'],
                'description' => $file['description'],
                'owner' => $file['owner'],
                'reference' => !empty($file['reference'])? $file['reference'] : $id,
            );
            foreach ($users as $uid) {
                $shortcut['user'] = $uid;
                $table->reset();
                $res = $table->insert($shortcut)->exec();
                if (Jaws_Error::IsError($res)) {
                    return $res;
                }
            }
        }

        $shared = !empty($users);
        if ($file['shared'] !== $shared) {
            $table->reset();
            $res = $table->update(array('shared' => $shared))->exec();
            if (Jaws_Error::IsError($res)) {
                return $res;
            }
        }

        return true;
    }
}
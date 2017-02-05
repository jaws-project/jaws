<?php
/**
 * Users Bookmarks model 
 *
 * @category   Gadget
 * @package    Users
 */
class Users_Model_Bookmarks extends Jaws_Gadget_Model
{
    /**
     * Updates bookmark
     *
     * @access  public
     * @param   int     $user       User ID
     * @param   array   $data       Bookmark data
     * @param   bool    $bookmarked bookmarked?
     * @return  mixed   True if successful otherwise Jaws_Error
     */
    function UpdateBookmark($user, $data, $bookmarked)
    {
        $data['user'] = (int)$user;
        $data['insert_time'] = time();
        $bookmarked = (bool)$bookmarked;
        $objORM = Jaws_ORM::getInstance()->table('user_bookmarks');
        if ($bookmarked) {
            $result = $objORM->upsert($data
                )->where('user', (int)$user)
                ->and()
                ->where('gadget', $data['gadget'])
                ->and()
                ->where('action', $data['action'])
                ->and()
                ->where('reference', $data['reference'])
                ->exec();
        } else {
            $result = $objORM->delete()
                ->where('user', (int)$user)
                ->and()
                ->where('gadget', $data['gadget'])
                ->and()
                ->where('action', $data['action'])
                ->and()
                ->where('reference', $data['reference'])
                ->exec();
        }

        return $result;
    }

    /**
     * Gets user's bookmarks
     *
     * @access  public
     * @param   array   $filters        Filters
     * @param   int     $limit          Count of posts to be returned
     * @param   int     $offset         Offset of data array
     * @return  mixed   Bookmarks array if successful otherwise Jaws_Error
     */
    function GetBookmarks($filters = array(), $limit = 0, $offset = null)
    {
        $bTable = Jaws_ORM::getInstance()->table('user_bookmarks')
            ->select('user_bookmarks.id:integer', 'gadget', 'action', 'reference:integer', 'user_bookmarks.title',
                'user_bookmarks.description', 'user_bookmarks.url', 'user_bookmarks.insert_time:integer')
            ->join('users', 'users.id', 'user_bookmarks.user', 'left');

        if (!empty($filters) && count($filters) > 0) {
            // term
            if (isset($filters['term']) && ($filters['term'] !== "")) {
                $bTable->and()->where(
                    $bTable->lower('user_bookmarks.title'),
                    Jaws_UTF8::strtolower($filters['term']),
                    'like'
                );
            }
            // user
            if (isset($filters['user']) && (!empty($filters['user'] )> 0)) {
                if (is_numeric($filters['user'])) {
                    $bTable->and()->where('users.id', $filters['user']);
                } else {
                    $bTable->and()->where('users.username', $filters['user']);
                }
            }
            // gadget
            if (isset($filters['gadget']) && (!empty($filters['gadget']) > 0)) {
                $bTable->and()->where('user_bookmarks.gadget', $filters['gadget']);
            }
            // action
            if (isset($filters['action']) && (!empty($filters['action']) > 0)) {
                $bTable->and()->where('user_bookmarks.action', $filters['action']);
            }
        }

        return $bTable->limit($limit, $offset)->orderBy('insert_time desc')->fetchAll();
    }

    /**
     * Gets user's bookmarks count
     *
     * @access  public
     * @param   array   $filters        Filters
     * @return  mixed   Bookmarks array if successful otherwise Jaws_Error
     */
    function GetBookmarksCount($filters = array())
    {
        $bTable = Jaws_ORM::getInstance()->table('user_bookmarks')
            ->select('count(user_bookmarks.id):integer')
            ->join('users', 'users.id', 'user_bookmarks.user', 'left');

        if (!empty($filters) && count($filters) > 0) {
            // term
            if (isset($filters['term']) && ($filters['term'] !== "")) {
                $bTable->and()->where(
                    $bTable->lower('user_bookmarks.title'),
                    Jaws_UTF8::strtolower($filters['term']),
                    'like'
                );
            }
            // user
            if (isset($filters['user']) && (!empty($filters['user'] )> 0)) {
                if (is_numeric($filters['user'])) {
                    $bTable->and()->where('users.id', $filters['user']);
                } else {
                    $bTable->and()->where('users.username', $filters['user']);
                }
            }
            // gadget
            if (isset($filters['gadget']) && (!empty($filters['gadget']) > 0)) {
                $bTable->and()->where('user_bookmarks.gadget', $filters['gadget']);
            }
            // action
            if (isset($filters['action']) && (!empty($filters['action']) > 0)) {
                $bTable->and()->where('user_bookmarks.action', $filters['action']);
            }
        }

        return $bTable->fetchOne();
    }

    /**
     * Gets a bookmark info
     *
     * @access  public
     * @param   int     $id           Bookmark id
     * @param   int     $user         User id
     * @return  mixed   Bookmarks info array if successful otherwise Jaws_Error
     */
    function GetBookmark($id, $user)
    {
        return  Jaws_ORM::getInstance()->table('user_bookmarks')
            ->select('user_bookmarks.id:integer', 'gadget', 'action', 'reference:integer', 'user_bookmarks.title',
                'user_bookmarks.description', 'user_bookmarks.url', 'user_bookmarks.insert_time:integer')
            ->join('users', 'users.id', 'user_bookmarks.user', 'left')
            ->where('user_bookmarks.id', (int)$id)
            ->and()->where('user_bookmarks.user', (int)$user)
            ->fetchRow();
    }

    /**
     * Delete a bookmark
     *
     * @access  public
     * @param   int     $id      Wiki ID
     * @param   int     $user    User ID
     * @return  mixed   True or Jaws_Error on failure
     */
    function DeleteBookmark($id, $user)
    {
        return Jaws_ORM::getInstance()->table('user_bookmarks')
            ->delete()->where('id', (int)$id)->and()->where('user', (int)$user)
            ->exec();
    }

}
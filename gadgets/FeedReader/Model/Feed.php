<?php
/**
 * FeedReader Gadget
 *
 * @category   GadgetModel
 * @package    FeedReader
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Ali Fazelzadeh  <afz@php.net>
 * @copyright  2005-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class FeedReader_Model_Feed extends Jaws_Gadget_Model
{
    /**
     * Gets list of possible feed sites
     *
     * @access  public
     * @param   bool    $onlyVisible    Visible sites only
     * @param   int     $user           Return global feeds or custom user's feed
     * @param   int     $limit          Number of data to retrieve (false = returns all)
     * @param   int     $offset         Data offset
     * @return  mixed   Array of feed sites or Jaws_Error on failure
     */
    function GetFeeds($onlyVisible = false, $user = 0, $limit = 0, $offset = null)
    {
        $objORM = Jaws_ORM::getInstance()->table('feeds');
        $objORM->select(
            'id:integer', 'user:integer', 'title', 'url', 'cache_time', 'view_type:integer',
            'count_entry:integer', 'title_view:integer', 'visible:integer'
        );
        $objORM->limit($limit, $offset);
        if ($onlyVisible) {
            $objORM->where('visible', 1);
        }
        if (empty($user)) {
            $objORM->and()->where('user', 0);
        } else {
            $objORM->and()->where('user', (int)$user);
        }
        $objORM->orderBy('id asc');
        return $objORM->fetchAll();
    }

    /**
     * Gets count of possible feed sites
     *
     * @access  public
     * @param   bool    $onlyVisible    Visible sites only
     * @param   int     $user           Return global feeds or custom user's feed
     * @return  mixed   Array of feed sites or Jaws_Error on failure
     */
    function GetFeedsCount($onlyVisible = false, $user = 0)
    {
        $objORM = Jaws_ORM::getInstance()->table('feeds');
        $objORM->select('count(id):integer');
        if ($onlyVisible) {
            $objORM->where('visible', 1);
        }
        if (empty($user)) {
            $objORM->and()->where('user', 0);
        } else {
            $objORM->and()->where('user', (int)$user);
        }
        return $objORM->fetchOne();
    }

    /**
     * Gets information of the feed site
     *
     * @access  public
     * @param   int     $id    Feed Site ID
     * @param   int     $user  User ID
     * @return  mixed   Array of the feed site data or Jaws_Error on failure
     */
    function GetFeed($id, $user = null)
    {
        $objORM = Jaws_ORM::getInstance()->table('feeds');
        $objORM->select(
            'id:integer', 'user:integer', 'title', 'url', 'cache_time', 'view_type:integer',
            'count_entry:integer', 'title_view:integer', 'visible:integer'
        );
         $objORM->where('id', (int)$id);
        if (!is_null($user)) {
            $objORM->and()->where('user', (int)$user);
        }
        return $objORM->fetchRow();
    }

    /**
     * Update user's feed
     *
     * @access  public
     * @param   array   $data        Feed's ids
     * @return  mixed   True or Jaws_Error on failure
     */
    function InsertUserFeed($data)
    {
        $fTable = Jaws_ORM::getInstance()->table('feeds');
        return $fTable->insert($data)->exec();
    }

    /**
     * Update user's feed
     *
     * @access  public
     * @param   int     $id          Feed id
     * @param   array   $data        Feed's ids
     * @param   int     $user        User id
     * @return  mixed   True or Jaws_Error on failure
     */
    function UpdateUserFeed($id, $data, $user)
    {
        $fTable = Jaws_ORM::getInstance()->table('feeds');
        return $fTable->update($data)->where('user', $user)->and()->where('id', $id)->exec();
    }

    /**
     * Delete user's feeds
     *
     * @access  public
     * @param   int     $user       User id
     * @param   array   $ids        Feed's ids
     * @return  mixed   True or Jaws_Error on failure
     */
    function DeleteUserFeeds($user, $ids)
    {
        $fTable = Jaws_ORM::getInstance()->table('feeds');
        return $fTable->delete()->where('user', $user)->and()->where('id', $ids, 'in')->exec();
    }
}
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
     * @param   array   $filters        Filters
     * @param   int     $user           Return global feeds or custom user's feed
     * @param   int     $limit          Number of data to retrieve (false = returns all)
     * @param   int     $offset         Data offset
     * @return  mixed   Array of feed sites or Jaws_Error on failure
     */
    function GetFeeds($filters = array(), $user = 0, $limit = 0, $offset = null)
    {
        $fTable = Jaws_ORM::getInstance()->table('feeds');
        $fTable->select(
            'id:integer', 'user:integer', 'title', 'url', 'cache_time', 'view_type:integer',
            'count_entry:integer', 'title_view:integer', 'visible:integer'
        );

        if (!empty($filters)) {
            if (isset($filters['only_visible']) && !is_null($filters['only_visible'])) {
                $fTable->where('visible', $filters['visible']);
            }
            if (isset($filters['term']) && !empty($filters['term'])) {
                $fTable->and()->where('title', $filters['term'], 'like');
            }
        }

        if (empty($user)) {
            $fTable->and()->where('user', 0);
        } else {
            $fTable->and()->where('user', (int)$user);
        }
        return $fTable->limit($limit, $offset)->orderBy('id asc')->fetchAll();
    }

    /**
     * Gets count of possible feed sites
     *
     * @access  public
     * @param   array   $filters        Filters
     * @param   int     $user           Return global feeds or custom user's feed
     * @return  mixed   Array of feed sites or Jaws_Error on failure
     */
    function GetFeedsCount($filters = array(), $user = 0)
    {
        $fTable = Jaws_ORM::getInstance()->table('feeds');
        $fTable->select('count(id):integer');
        if (!empty($filters)) {
            if (isset($filters['only_visible']) && !is_null($filters['only_visible'])) {
                $fTable->where('visible', $filters['visible']);
            }
            if (isset($filters['term']) && !empty($filters['term'])) {
                $fTable->and()->where('title', $filters['term'], 'like');
            }
        }
        if (empty($user)) {
            $fTable->and()->where('user', 0);
        } else {
            $fTable->and()->where('user', (int)$user);
        }
        return $fTable->fetchOne();
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
     * Delete user's feed
     *
     * @access  public
     * @param   int     $user       User id
     * @param   int     $id         Feed's id
     * @return  mixed   True or Jaws_Error on failure
     */
    function DeleteUserFeed($user, $id)
    {
        $fTable = Jaws_ORM::getInstance()->table('feeds');
        return $fTable->delete()->where('user', $user)->and()->where('id', $id)->exec();
    }
}
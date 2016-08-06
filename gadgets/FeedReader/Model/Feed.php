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
     * Gets information of the feed site
     *
     * @access  public
     * @param   int     $id    Feed Site ID
     * @return  mixed   Array of the feed site data or Jaws_Error on failure
     */
    function GetFeed($id)
    {
        $objORM = Jaws_ORM::getInstance()->table('feeds');
        $objORM->select(
            'id:integer', 'user:integer', 'title', 'url', 'cache_time', 'view_type:integer',
            'count_entry:integer', 'title_view:integer', 'visible:integer'
        );
        return $objORM->where('id', (int)$id)->fetchRow();
    }
}
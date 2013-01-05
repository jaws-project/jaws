<?php
/**
 * Banner Gadget
 *
 * @category   GadgetModel
 * @package    Banner
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Jon Wood <jon@jellybob.co.uk>
 * @author     Amir Mohammad Saied <amir@php.net>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Banner_Model extends Jaws_Gadget_Model
{
    /**
     * Retrieve banner
     *
     * @access  public
     * @param   int     $bid    banner ID
     * @return  mixed   An array of banner's data and Jaws_Error on error
     */
    function GetBanner($bid)
    {
        $bannersTable = Jaws_ORM::getInstance()->table('banners');
        $bannersTable->select(
            'id:integer', 'title', 'url', 'gid:integer', 'banner', 'template', 'views:integer',
            'views_limitation:integer', 'clicks:integer', 'clicks_limitation:integer', 'start_time',
            'stop_time', 'rank:integer', 'random:integer', 'published:boolean'
        );

        return $bannersTable->where('id', $bid)->getRow();
    }

    /**
     * Retrieve banners
     *
     * @access  public
     * @param   int     $bid     banner ID
     * @param   int     $gid     group ID
     * @param   int     $limit
     * @param   int     $offset
     * @param   int     $columns
     * @return  mixed   An array of available banners or Jaws_Error on error
     */
    function GetBanners($bid = -1, $gid = -1, $limit = 0, $offset = null, $columns = null)
    {
        $bannersTable = Jaws_ORM::getInstance()->table('banners');
        if (empty($columns)) {
            $columns = array('id:integer', 'title', 'url', 'gid:integer', 'banner', 'template', 'views:integer',
                        'views_limitation:integer', 'clicks:integer', 'clicks_limitation:integer', 'start_time',
                        'stop_time', 'createtime', 'updatetime', 'random:integer', 'published:boolean');
        }

        $bannersTable->select($columns);

        if (($bid != -1) && ($gid != -1)) {
            $bannersTable->where('id', $bid)->and()->where('gid', $gid);
            $bannersTable->orderBy('rank ASC');
        } elseif ($gid != -1) {
            $bannersTable->where('gid', $gid);
            $bannersTable->orderBy('rank ASC');
        } elseif ($bid != -1) {
            $bannersTable->where('id', $bid);
        } else {
            $bannersTable->orderBy('id ASC');
        }

        return $bannersTable->limit($limit, $offset)->getAll();
    }

    /**
     * Retrieve group's info
     *
     * @access  public
     * @param   int     $gid    group ID
     * @return  mixed   An array of group's data or Jaws_Error on error
     */
    function GetGroup($gid)
    {
        $bgroupsTable = Jaws_ORM::getInstance()->table('banners_groups');
        $bgroupsTable->select(
            'id:integer', 'title', 'limit_count:integer', 'show_title:boolean', 'show_type:integer', 'published:boolean'
        );

        return $bgroupsTable->where('id', $gid)->getRow();
    }

    /**
     * Retrieve groups
     *
     * @access  public
     * @param   int     $gid    group ID
     * @param   int     $bid    banner ID
     * @param   int     $columns    
     * @return  mixed   An array of available banners or Jaws_Error on error
     */
    function GetGroups($gid = -1, $bid = -1, $columns = null)
    {
        $bgroupsTable = Jaws_ORM::getInstance()->table('banners_groups');
        if (empty($columns)) {
            $columns = array('id:integer', 'title', 'limit_count:integer', 'published:boolean');
        }

        $bgroupsTable->select($columns);

        if (($gid != -1) && ($bid != -1)) {
            $bgroupsTable->join('banners', 'banners.gid', 'banners_groups.id');
            $bgroupsTable->where('banners_groups.id', $gid)->and()->where('banners.id', $bid);
            $bgroupsTable->orderBy('banners_groups.id ASC');
        } elseif ($bid != -1) {
            $bgroupsTable->join('banners', 'banners.gid', 'banners_groups.id');
            $bgroupsTable->where('banners.id', $bid);
            $bgroupsTable->orderBy('banners_groups.id ASC');
        } elseif ($gid != -1) {
            $bgroupsTable->where('id', $gid);
        } else {
            $bgroupsTable->orderBy('id ASC');
        }

        return $bgroupsTable->getAll();
    }

    /**
     * Retrieve banners that can be visible
     *
     * @access  public
     * @param   int     $gid   group ID
     * @param   int     $random
     * @return  mixed   An array of available banners or False on error
     */
    function GetEnableBanners($gid = 0, $random = 0)
    {
        $bannersTable = Jaws_ORM::getInstance()->table('banners');
        $bannersTable->select('id:integer', 'title', 'url', 'banner', 'template');

        $bannersTable->where('published', true)->and()->where('random', $random)->and();
        $bannersTable->openWhere('views_limitation', 0)->or();
        $bannersTable->closeWhere('views', $bannersTable->expr('views_limitation'), '<')->and();
        $bannersTable->openWhere('clicks_limitation', 0)->or();
        $bannersTable->closeWhere('clicks', $bannersTable->expr('clicks_limitation'), '<')->and();
        $bannersTable->openWhere('start_time', '', 'is null')->or();
        $bannersTable->closeWhere('start_time', $GLOBALS['db']->Date(), '<=')->and();
        $bannersTable->openWhere('stop_time', '', 'is null')->or();
        $bannersTable->closeWhere('stop_time', $GLOBALS['db']->Date(), '>=');

        if ($gid == 0) {
            $bannersTable->orderBy('id ASC');
        } else {
            $bannersTable->and()->where('gid', $gid);
            $bannersTable->orderBy('id ASC');
        }

        $res = $bannersTable->getAll();
        if (Jaws_Error::IsError($res)) {
            return false;
        }
        return $res;
    }

    /**
     * Retrieve visible banners
     *
     * @access  public
     * @param   int     $gid         group ID
     * @param   int     $limit_count
     * @return  array   An array of available banners
     */
    function GetVisibleBanners($gid, $limit_count)
    {
        $limit_count = empty($limit_count)? 256 : $limit_count;
        if (($always_array = $this->GetEnableBanners($gid, 0)) == false) {
            $always_array = array();
        }

        if (($random_array = $this->GetEnableBanners($gid, 1)) == false) {
            $random_array = array();
        }

        $res_array = array();
        if ((count($always_array) + count($random_array)) > $limit_count) {
            if(count($always_array) > $limit_count) {
                while (count($always_array) > $limit_count) {
                    array_splice($always_array, mt_rand(0, count($always_array)-1), 1);
                }
                $res_array = $always_array;
            } else {
                while (count($random_array) > ($limit_count - count($always_array))) {
                    array_splice($random_array, mt_rand(0, count($random_array)-1), 1);
                }
                $res_array = array_merge($always_array, $random_array);
            }
        } else {
            $res_array = array_merge($always_array, $random_array);
        }

        return $res_array;
    }

    /**
     * Increment the number of clicks a banner has had by 1.
     *
     * @access  public
     * @param   int     $bid    The id of the banner to increment
     * @return  mixed   True or Jaws_Error
     */
    function ClickBanner($bid)
    {
        $bannersTable = Jaws_ORM::getInstance()->table('banners');
        $res = $bannersTable->update(
            array(
                'clicks' => $bannersTable->expr('clicks + ?', 1)
            )
        )->where('id', $bid)->exec();

        if (Jaws_Error::IsError($res)) {
            return new Jaws_Error($res->getMessage(), 'SQL');
        }

        return true;
    }

    /**
     * Increment the number of views a banner has had by 1.
     *
     * @access  public
     * @param   int     $bid     The id of the banner to increment.
     * @return  mixed   True on success and Jaws_Error on error
     */
    function ViewBanner($bid)
    {
        $bannersTable = Jaws_ORM::getInstance()->table('banners');
        $res = $bannersTable->update(
            array(
                'views' => $bannersTable->expr('views + ?', 1)
            )
        )->where('id', $bid)->exec();

        if (Jaws_Error::IsError($res)) {
            return new Jaws_Error($res->getMessage(), 'SQL');
        }

        return true;
    }

}
<?php
/**
 * SysInfo Gadget
 *
 * @category   GadgetModel
 * @package    SysInfo
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2008-2014 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class SysInfo_Model_DirInfo extends Jaws_Gadget_Model
{
    /**
     * Gets directory permission
     *
     * @access  public
     * @param   string  $path   Diretory path
     * @return  string  Full permissions of directory
     */
    function GetFSPermission($path)
    {
        $path = JAWS_PATH . $path;
        $perms = @decoct(@fileperms($path) & 0777);
        if (strlen($perms) < 3) {
            return '---------';
        }

        $str = '';
        for ($i = 0; $i < 3; $i ++) {
            $str .= ($perms[$i] & 04) ? 'r' : '-';
            $str .= ($perms[$i] & 02) ? 'w' : '-';
            $str .= ($perms[$i] & 01) ? 'x' : '-';
        }

        return $str;
    }

    /**
     * Gets permissions on some Jaws directories
     *
     * @access  public
     * @return  array   Directories permissions
     */
    function GetDirsPermissions()
    {
        return array(
            array('title' => '/',
                'value' => $this->GetFSPermission('')),
            array('title' => '/config',
                'value' => $this->GetFSPermission('config')),
            array('title' => '/data',
                'value' => $this->GetFSPermission('data')),
            array('title' => '/data/themes',
                'value' => $this->GetFSPermission('data/themes')),
            array('title' => '/gadgets',
                'value' => $this->GetFSPermission('gadgets')),
            array('title' => '/images',
                'value' => $this->GetFSPermission('images')),
            array('title' => '/include',
                'value' => $this->GetFSPermission('include')),
            array('title' => '/install',
                'value' => $this->GetFSPermission('install')),
            array('title' => '/languages',
                'value' => $this->GetFSPermission('languages')),
            array('title' => '/libraries',
                'value' => $this->GetFSPermission('libraries')),
            array('title' => '/plugins',
                'value' => $this->GetFSPermission('plugins')),
            array('title' => '/upgrade',
                'value' => $this->GetFSPermission('upgrade')),
        );
    }

}
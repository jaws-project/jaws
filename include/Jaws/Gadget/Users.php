<?php
/**
 * Jaws Gadget Users
 *
 * @category    Gadget
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2020 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Gadget_Users
{
    /**
     * Jaws app object
     *
     * @var     object
     * @access  public
     */
    public $app = null;

    /**
     * Jaws_Gadget object
     *
     * @var     object
     * @access  protected
     */
    var $gadget = null;

    /**
     * constructor
     *
     * @access  public
     * @param   object  $gadget Jaws_Gadget object
     * @return  void
     */
    function __construct($gadget)
    {
        $this->gadget = $gadget;
        $this->app = Jaws::getInstance();
    }

    /**
     * Insert custom user's attributes of gadget
     *
     * @access  public
     * @param   int     $user   User ID
     * @param   array   $attrs  User's attributes
     * @return  bool    Returns True or Jaws_Error on Failure
     */
    function insertAttributes($user, $attrs = array())
    {
        array_unshift($attrs, array('user' => $user));
        $objORM = Jaws_ORM::getInstance()->table('users_'.$this->gadget->name);
        return $objORM->insert($attrs)->exec();
    }

    /**
     * Update custom user's attributes of gadget
     *
     * @access  public
     * @param   int     $user   User ID
     * @param   array   $attrs  User's attributes
     * @return  bool    Returns True or Jaws_Error on Failure
     */
    function updateAttributes($user, $attrs = array())
    {
        $objORM = Jaws_ORM::getInstance()->table('users_'.$this->gadget->name);
        return $objORM->update($attrs)->where('user', (int)$user)->exec();
    }

    /**
     * delete custom user's attributes of gadget
     *
     * @access  public
     * @param   int     $user   User ID
     * @return  bool    Returns True or Jaws_Error on Failure
     */
    function deleteAttributes($user)
    {
        $objORM = Jaws_ORM::getInstance()->table('users_'.$this->gadget->name);
        return $objORM->delete()->where('user', (int)$user)->exec();
    }

    /**
     * Fetch custom user's attributes of gadget
     *
     * @access  public
     * @param   int     $user   User ID
     * @return  mixed   Returns array of attributes or Jaws_Error on Failure
     */
    function fetchAttributes($user, $customAttributes, $defaultAttributes = array())
    {
        $tableName = strtolower('users_'.$this->gadget->name);
        array_walk(
            $customAttributes,
            function(&$value, $key, $prefix) {
                $value = $prefix. '.'. $value;
            },
            $tableName
        );

        array_walk(
            $defaultAttributes,
            function(&$value, $key) {
                $value = 'users.'. $value;
            }
        );

        return Jaws_ORM::getInstance()->table($tableName)
            ->select(array_merge($defaultAttributes, $customAttributes))
            ->join('users', 'users.id', $tableName.'.user')
            ->where('users.id', (int)$user)
            ->fetchRow();
    }

}
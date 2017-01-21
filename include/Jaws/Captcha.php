<?php
/**
 * Base class of Captcha drivers
 *
 * @category    Captcha
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2013-2015 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Captcha
{
    /**
     * Captcha driver name
     *
     * @var     string
     * @access  private
     */
    private $_driver;

    /**
     * Captcha entry label
     *
     * @var     string
     * @access  private
     */
    private $_label = 'GLOBAL_CAPTCHA_CODE';

    /**
     * Captcha entry description
     *
     * @var     string
     * @access  private
     */
    private $_description = 'GLOBAL_CAPTCHA_CODE_DESC';

    /**
     * Constructor
     *
     * @access  public
     * @param   string  $driver Captcha driver name
     * @return  void
     */
    function __construct($driver)
    {
        $this->_driver = $driver;
    }

    /**
     * Get a Jaws_Captcha instance
     *
     * @access  public
     * @param   string  $driver Captcha driver name
     * @return  object  Jaws_Captcha instance
     */
    static function getInstance($driver)
    {
        static $instances;
        if (!isset($instances)) {
            $instances = array();
        }

        if (!isset($instances[$driver])) {
            $className = 'Jaws_Captcha_'. $driver;
            $instances[$driver] = new $className($driver);
        }

        return $instances[$driver];
    }

    /**
     * Install captcha driver
     *
     * @access  public
     * @return  mixed   True on success otherwise Jaws_Error on failure
     */
    function install()
    {
        return true;
    }

    /**
     * Returns an array with the captcha image field and a text entry
     *
     * @access  public
     * @return  array    Array indexed by captcha (the image entry) and entry (the input)
     */
    function get()
    {
        $key = $this->insert();
        $res = array();
        $res['key']   = $key;
        $res['text']  = '';
        $res['label'] = _t($this->_label);
        $res['title'] = _t($this->_label);
        $res['description'] = _t($this->_description);
        return $res;
    }

    /**
     * Check if a captcha key is valid
     *
     * @access  public
     * @return  bool    Valid/Not Valid
     */
    function check()
    {
        $post = jaws()->request->fetch(array('captcha_key', 'entry_value'), 'post');
        list($key, $value) = array_values($post);

        $matched = false;
        $result = $this->fetch((int)$key);
        if (!Jaws_Error::IsError($result) && is_string($value)) {
            $matched = !empty($value) && (strtolower($result) === strtolower($value));
        }

        $this->delete((int)$key);
        return $matched;
    }

    /**
     * Get the real value of a captcha by a given key
     *
     * @access  protected
     * @param   string  $key    Captcha key
     * @return  Mixed   Captcha value on success otherwise Jaws_Error on failure
     */
    function fetch($key)
    {
        $tblCaptcha = Jaws_ORM::getInstance()->table('captcha');
        return $tblCaptcha->select('result')->where('id', $key)->fetchOne();
    }

    /**
     * Get new captcha key
     *
     * @access  protected
     * @param   string  $value  Captcha value
     * @return  mixed   Captcha key on success or Jaws_Error on failure
     */
    function insert($value = '')
    {
        $tblCaptcha = Jaws_ORM::getInstance()->table('captcha');
        $tblCaptcha->insert(array('result' => $value, 'updatetime' => time()));
        return $tblCaptcha->exec();
    }

    /**
     * Update captcha value
     *
     * @access  protected
     * @param   int     Captcha key
     * @param   string  Captcha value
     * @return  mixed   True on success or Jaws_Error on failure
     */
    function update($key, $value)
    {
        $tblCaptcha = Jaws_ORM::getInstance()->table('captcha');
        $tblCaptcha->update(array('result' => $value, 'updatetime' => time()))->where('id', (int)$key);
        return $tblCaptcha->exec();
    }

    /**
     * Delete captcha record or outdated captchas
     *
     * @access  protected
     * @param   int     $key    Captcha key
     * @return  mixed   Deleted rows count on success or Jaws_Error on failure
     */
    function delete($key = 0)
    {
        $tblCaptcha = Jaws_ORM::getInstance()->table('captcha');
        $tblCaptcha->delete()->where('id', $key)->or()->where('updatetime', time() - 600, '<');
        return $tblCaptcha->exec();
    }

    /**
     * Displays the captcha image
     *
     * @access  public
     * @param   int     $key    Captcha key
     * @return  mixed   Captcha raw image data or Jaws_Error if this method not supported
     */
    function image($key)
    {
        return Jaws_Error::raiseError('image() method not supported by this captcha.', __FUNCTION__);
    }

}
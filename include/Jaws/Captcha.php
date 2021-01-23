<?php
/**
 * Base class of Captcha drivers
 *
 * @category    Captcha
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2008-2021 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Captcha
{
    /**
     * Captcha types
     *
     * @var     object
     * @access  public
     */
    const CAPTCHA_TEXT  = 1;
    const CAPTCHA_IMAGE = 2;
    const CAPTCHA_BLOCK = 3;

    /**
     * Jaws app object
     *
     * @var     object
     * @access  public
     */
    public $app = null;

    /**
     * Captcha driver type
     *
     * @var     int
     * @access  protected
     */
    protected $type = Jaws_Captcha::CAPTCHA_IMAGE;

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
     * @return  void
     */
    function __construct()
    {
        $this->app = Jaws::getInstance();
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
            $instances[$driver] = new $className();
        }

        // delete expired captcha
        if (mt_rand(1, 10) == mt_rand(1, 10)) {
            $instances[$driver]->delete(0);
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
        $res['type']  = $this->type;
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
     * @param   bool    $cleanup    Delete captcha key after check
     * @return  bool    Valid/Not Valid
     */
    function check($cleanup = true)
    {
        $post = $this->app->request->fetch(array('captcha_key', 'entry_value'), 'post');
        list($key, $value) = array_values($post);

        $matched = null;
        $result = $this->fetch((int)$key);
        if (!Jaws_Error::IsError($result) && !is_null($result)) {
            $matched = strtolower($result) === strtolower($value);

            if ($cleanup) {
                $this->delete((int)$key);
            } else {
                $this->update((int)$key, Jaws_Utils::RandomText());
            }
        }

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
        $tblCaptcha->delete()->where('id', $key)->or()->where('updatetime', time() - 300, '<');
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
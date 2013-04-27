<?php
/**
 * Base class of Captcha drivers
 *
 * @category    Captcha
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Captcha
{
    /**
     * Captcha driver name
     * @var string
     */
    var $_driver;

    /**
     * Captcha field
     * @var string
     */
    var $_field;

    /**
     * Captcha entry label
     * @var string
     */
    var $_label = 'GLOBAL_CAPTCHA_CODE';

    /**
     * Captcha entry description
     * @var string
     */
    var $_description = 'GLOBAL_CAPTCHA_CODE_DESC';

    /**
     * Constructor
     *
     * @access  public
     * @param   string  $driver Captcha driver name
     * @param   string  $field  Captcha field
     * @return  void
     */
    function Jaws_Captcha($driver, $field = 'default')
    {
        $this->_driver = $driver;
        $this->_field  = $field;
    }

    /**
     * Get a Jaws_Captcha instance
     *
     * @access  public
     * @param   string  $driver Captcha driver name
     * @param   string  $field  Captcha field
     * @return  object  Jaws_Captcha instance
     */
    function &getInstance($driver, $field = 'default')
    {
        static $instances;
        if (!isset($instances)) {
            $instances = array();
        }

        if (!isset($instances[$driver])) {
            $className = 'Jaws_Captcha_'. $driver;
            $instances[$driver] = new $className($driver, $field);
        }

        return $instances[$driver];
    }

    /**
     * Install captcha driver
     *
     * @access  public
     */
    function install()
    {
        return true;
    }

    /**
     * Returns an array with the captcha image field and a text entry so user can type
     *
     * @access  public
     * @return  array    Array indexed by captcha (the image entry) and entry (the input)
     */
    function get()
    {
        $key = $this->insert();
        $imgSrc = $GLOBALS['app']->Map->GetURLFor(
            'Policy',
            'Captcha',
            array('field' => $this->_field, 'key' => $key)
        );

        $res = array();
        $res['key'] =& Piwi::CreateWidget('HiddenEntry', 'captcha_key', $key);
        $res['key']->SetID("captcha_key_$key");
        $res['captcha'] =& Piwi::CreateWidget('Image', '', '');
        $res['captcha']->SetTitle(_t($this->_label));
        $res['captcha']->SetID("captcha_image_$key");
        $res['captcha']->SetClass('captcha');
        $res['captcha']->SetSrc($imgSrc);
        $res['entry'] =& Piwi::CreateWidget('Entry', 'captcha_value', '');
        $res['entry']->SetID("captcha_value_$key");
        $res['entry']->SetStyle('direction: ltr;');
        $res['entry']->SetTitle(_t('GLOBAL_CAPTCHA_CASE_INSENSITIVE'));
        $res['label'] =& Piwi::CreateWidget('Label', _t($this->_label).':', $res['entry']);
        $res['description'] = _t($this->_description);
        return $res;
    }

    /**
     * Check if a captcha key is valid
     *
     * @access  public
     * @param   bool     Valid/Not Valid
     */
    function check()
    {
        $request =& Jaws_Request::getInstance();
        $post = $request->get(array('captcha_key', 'captcha_value'), 'post');
        list($key, $value) = array_values($post);

        $matched = false;
        $result = $this->fetch($key);
        if (!Jaws_Error::IsError($result)) {
            $matched = (strtolower($result) === strtolower($value));
        }

        $this->delete($key);
        return $matched;
    }

    /**
     * Get the real value of a captcha by a given key
     *
     * @access  protected
     * @param   string  $key    Captcha key
     * @return  string  Captcha value
     */
    function fetch($key)
    {
        $tblCaptcha = Jaws_ORM::getInstance()->table('captcha');
        return $tblCaptcha->select('result')->where('id', $key)->getOne();
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
        $key = $tblCaptcha->exec();
        return $key;
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
        $key = $tblCaptcha->exec();
        return $key;
    }

    /**
     * Delete captcha record or outdated captchas
     *
     * @access  protected
     * @param   string  $key  Captcha key
     * @return  void
     */
    function delete($key = 0)
    {
        $tblCaptcha = Jaws_ORM::getInstance()->table('captcha');
        $tblCaptcha->delete()->where('id', $key)->or()->where('updatetime', time() - 600, '<');
        $result = $tblCaptcha->exec();
    }

    /**
     * Displays the captcha image
     *
     * @access  public
     */
    function image($key)
    {
        return Jaws_Error::raiseError('image() method not supported by this captcha.', __FUNCTION__);
    }

}
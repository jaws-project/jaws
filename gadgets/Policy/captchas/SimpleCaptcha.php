<?php
/**
 * SimpleCaptcha
 *
 * @category    Captcha
 * @package     Policy
 * @author      Jonathan Hernandez <ion@suavizado.com>
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2006-2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class SimpleCaptcha
{
    /**
     * Constructor
     *
     * @access  public
     */
    function SimpleCaptcha()
    {
        // If not installed try to install it
        if ($GLOBALS['app']->Registry->Get('simple_captcha', 'Policy') != 'installed') {
            $GLOBALS['app']->Registry->NewKey('simple_captcha', 'installed', 'Policy');
        }
    }

    /**
     * Returns an array with the captcha image field and a text entry so user can type
     *
     * @access  public
     * @return  array    Array indexed by captcha (the image entry) and entry (the input)
     */
    function Get($field)
    {
        $key = $this->GetKey();
        $imgSrc = $GLOBALS['app']->Map->GetURLFor(
            'Policy',
            'Captcha',
            array('field' => $field, 'key' => $key)
        );

        $res = array();
        $res['key'] =& Piwi::CreateWidget('HiddenEntry', 'captcha_key', $key);
        $res['key']->SetID("captcha_key_$key");
        $res['label'] = _t('GLOBAL_CAPTCHA_CODE');
        $res['captcha'] =& Piwi::CreateWidget('Image', '', '');
        $res['captcha']->SetTitle(_t('GLOBAL_CAPTCHA_CODE'));
        $res['captcha']->SetID("captcha_image_$key");
        $res['captcha']->SetClass('captcha');
        $res['captcha']->SetSrc($imgSrc);
        $res['entry'] =& Piwi::CreateWidget('Entry', 'captcha_value', '');
        $res['entry']->SetID("captcha_value_$key");
        $res['entry']->SetStyle('direction: ltr;');
        $res['entry']->SetTitle(_t('GLOBAL_CAPTCHA_CASE_INSENSITIVE'));
        $res['description'] = _t('GLOBAL_CAPTCHA_CODE_DESC');
        return $res;
    }

    /**
     * Check if a captcha key is valid
     *
     * @access  public
     * @param   bool     Valid/Not Valid
     */
    function Check()
    {
        $request =& Jaws_Request::getInstance();
        $post = $request->get(array('captcha_key', 'captcha_value'), 'post');
        list($key, $value) = array_values($post);

        $captcha_value = $this->GetValue($key);
        $result = ($captcha_value !== false) && (strtolower($captcha_value) === strtolower($value));

        $this->Delete($key);
        return $result;
    }

    /**
     * Remove a captcha key once it has been used (with success or failure)
     *
     * @access  public
     * @param   string  $key  Captcha key
     */
    function Delete($key = 0)
    {
        $tblCaptcha = Jaws_ORM::getInstance()->table('captcha');
        $tblCaptcha->delete()->where('id', $key)->or()->where('updatetime', time() - 600, '<');
        $result = $tblCaptcha->exec();
    }

    /**
     * Get the real value of a captcha by a given key
     *
     * @access  public
     * @param   string  $key    Captcha key
     * @return  string  Captcha value
     */
    function GetValue($key)
    {
        $tblCaptcha = Jaws_ORM::getInstance()->table('captcha');
        $result = $tblCaptcha->select('result')->where('id', $key)->getOne();
        if (Jaws_Error::IsError($result) || empty($result)) {
            $result = '';
        }

        return $result;
    }

    /**
     * Get the key of the current captcha (it creates the captcha and then returns its key)
     *
     * @access  public
     * @return  string  Captcha's key
     */
    function GetKey()
    {
        $value = $this->GenerateRandomValue();
        $tblCaptcha = Jaws_ORM::getInstance()->table('captcha');
        $tblCaptcha->insert(array('result' => $value, 'updatetime' => time()));
        $key = $tblCaptcha->exec();
        if (Jaws_Error::IsError($key)) {
            $key = '';
        }

        return $key;
    }

    /**
     * Create the random string that user will see in the browser
     *
     * @access  private
     * @return  string    random string
     */
    function GenerateRandomValue($lenght = 5, $use_letters = true, $use_numbers = false)
    {
        $string = "";
        $letters = "abcdefghijklmnopqrstuvwxyz";
        $numbers = "01234567890";
        $possible = "";
        if ($use_letters) {
            $possible .= $letters;
        }
        if ($use_numbers) {
            $possible .= $numbers;
        }

        for ($i = 1; $i <= $lenght; $i++) {
            $string .= substr($possible, rand(0, strlen($possible)-1), 1);
        }
        return $string;
    }

    /**
     * Displays the captcha image
     *
     * @access  public
     */
    function Image($key)
    {
        $bg = dirname(__FILE__) . '/SimpleCaptcha/bg.png';
        $im = imagecreatefrompng($bg);
        imagecolortransparent($im, imagecolorallocate($im, 255, 255, 255));
        $value = $this->GetValue($key);
        // Write it in a random position..
        $text_length = strlen($value);
        $darkgray = imagecolorallocate($im, 0x10, 0x70, 0x70);
        $x = 5; 
        $y = 20;
        for ($i = 0; $i < $text_length; $i++) {
            $fnt = rand(7,10);
            $y = rand(6, 10);
            imagestring($im, $fnt, $x, $y, $value{$i} , $darkgray);
            $x = $x + rand(15, 25);
        }
        header("Content-Type: image/png");
        imagepng($im);
        imagedestroy($im);
    }

}

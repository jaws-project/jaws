<?php
/**
 * MathCaptcha
 *
 * @category    Captcha
 * @package     Policy
 * @author      Pablo Fischer <pablo@pablo.com.mx>
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2006-2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class MathCaptcha
{
    /**
     * Constructor
     *
     * @access  public
     */
    function MathCaptcha()
    {
        // If not installed try to install it
        if ($GLOBALS['app']->Registry->Get('math_captcha', 'Policy') != 'ver2_installed') {
            $GLOBALS['app']->Registry->NewKey('math_captcha', 'ver2_installed', 'Policy');
            $GLOBALS['app']->Registry->NewKey('math_accessibility', 'false', 'Policy');
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
        list($key, $value1, $oprt, $value2) = $this->GetKey();
        if ($GLOBALS['app']->Registry->Get('math_accessibility', 'Policy') === 'true') {
            switch ($oprt) {
                case '+':
                    $title = _t('POLICY_CAPTCHA_MATH_PLUS', $value1, $value2);
                    break;

                case '-':
                    $title = _t('POLICY_CAPTCHA_MATH_MINUS', $value1, $value2);
                    break;

                case '*':
                    $title = _t('POLICY_CAPTCHA_MATH_MULTIPLY', $value1, $value2);
                    break;

                default:
                    $title = _t('GLOBAL_CAPTCHA_QUESTION');
            }
        } else {
            $title = _t('GLOBAL_CAPTCHA_QUESTION');
        }

        $imgSrc = $GLOBALS['app']->Map->GetURLFor(
            'Policy',
            'Captcha',
            array('field' => $field, 'key' => $key)
        );

        $res = array();
        $res['key'] =& Piwi::CreateWidget('HiddenEntry', 'captcha_key', $key);
        $res['key']->SetID("captcha_key_$key");
        $res['label'] = _t('GLOBAL_CAPTCHA_QUESTION');
        $res['captcha'] =& Piwi::CreateWidget('Image', '', '');
        $res['captcha']->SetTitle($title);
        $res['captcha']->SetID("captcha_image_$key");
        $res['captcha']->SetClass('captcha');
        $res['captcha']->SetSrc($imgSrc);
        $res['entry'] =& Piwi::CreateWidget('Entry', 'captcha_value', '');
        $res['entry']->SetID("captcha_value_$key");
        $res['entry']->SetStyle('direction: ltr;');
        $res['entry']->SetTitle(_t('GLOBAL_CAPTCHA_CASE_INSENSITIVE'));
        $res['description'] = _t('GLOBAL_CAPTCHA_QUESTION_DESC');
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

        $captcha_value = '$captcha_value = '. $this->GetValue($key).';';
        eval($captcha_value);
        $result = ($captcha_value !== false) && ($captcha_value === (int) $value);

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
        $tblCaptcha->insert(array('result' => implode('', $value), 'updatetime' => time()));
        $key = $tblCaptcha->exec();
        if (Jaws_Error::IsError($key)) {
            $key = 0;
        }

        array_unshift($value, $key);
        return $value;
    }

    /**
     * Create the random string that user will see in the browser
     *
     * @access  private
     * @return  string    random string
     */
    function GenerateRandomValue()
    {
        $fnum = rand(1, 9);
        $snum = rand(1, 9);
        $oprt = rand(0, 2);
        switch ($oprt) {
            case 0:
                $oprt = '+';
                break;

            case 1:
                $oprt = '-';
                // exchange value of variables
                if ($fnum < $snum) {
                    list($fnum, $snum) = array($snum, $fnum);
                }
                break;

            case 2:
                $oprt = '*';
        }

        return array((string)$fnum, $oprt, (string)$snum);
    }

    /**
     * Displays the captcha image
     *
     * @access  public
     */
    function Image($key)
    {
        $bg = dirname(__FILE__) . '/MathCaptcha/bg.png';
        $im = imagecreatefrompng($bg);
        imagecolortransparent($im, imagecolorallocate($im, 255, 255, 255));
        $value = $this->GetValue($key);
        $value .= '=?';
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
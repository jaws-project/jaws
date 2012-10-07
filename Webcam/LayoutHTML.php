<?php
/**
 * Webcam Gadget - Layout actions
 *
 * @category   GadgetLayout
 * @package    Webcam
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class WebcamLayoutHTML
{
    /**
     * Handle the layout action of Display webcams
     *
     * @access  public
     * @return  string  HTML content of Webcams for layout action
     */
    function Display()
    {
        $tpl = new Jaws_Template('gadgets/Webcam/templates/');
        $tpl->Load('Webcam.html');
        $model = $GLOBALS['app']->LoadGadget('Webcam', 'Model');
        $webcams = $model->GetWebcams();
        if (!Jaws_Error::IsError($webcams)) {
            $xss = $GLOBALS['app']->loadClass('XSS', 'Jaws_XSS');
            $tpl->SetBlock('webcam');
            $tpl->SetVariable('title', _t('WEBCAM_WEBCAMS'));
            foreach ($webcams as $webcam) {
                $tpl->SetBlock('webcam/item');
                $tpl->SetVariable('url',     $xss->filter($webcam['url']));
                $tpl->SetVariable('title',   $xss->filter($webcam['title']));
                $tpl->SetVariable('id',      $webcam['id']);
                $tpl->SetVariable('refresh', $webcam['refresh']);
                $tpl->ParseBlock('webcam/item');
            }
            $tpl->ParseBlock('webcam');
        }

        return $tpl->Get();
    }

    /**
     * Gets a random webcam and prints it
     *
     * @access  public
     * @return  string  HTML content of radom webcam
     */
    function Random()
    {
        $tpl = new Jaws_Template('gadgets/Webcam/templates/');
        $tpl->Load('Webcam.html');
        $model = $GLOBALS['app']->LoadGadget('Webcam', 'Model');
        $webcam = $model->GetRandomWebCam();
        if (!Jaws_Error::IsError($webcam)) {
            $xss = $GLOBALS['app']->loadClass('XSS', 'Jaws_XSS');
            $tpl->SetBlock('webcam');
            $tpl->SetVariable('title', _t('WEBCAM_WEBCAMS'));
            $tpl->SetBlock('webcam/item');
            $tpl->SetVariable('url',     $xss->filter($webcam['url']));
            $tpl->SetVariable('title',   $xss->filter($webcam['title']));
            $tpl->SetVariable('id',      $webcam['id']);
            $tpl->SetVariable('refresh', $webcam['refresh']);
            $tpl->ParseBlock('webcam/item');
            $tpl->ParseBlock('webcam');
        }

        return $tpl->Get();
    }
}
?>

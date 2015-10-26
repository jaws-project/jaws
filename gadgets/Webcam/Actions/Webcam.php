<?php
/**
 * Webcam Gadget
 *
 * @category   Gadget
 * @package    Webcam
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2004-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Webcam_Actions_Webcam extends Jaws_Gadget_Action
{
    /**
     * Displays webcams
     *
     * @access  public
     * @return  string  XHTML content of webcams
     */
    function Display()
    {
        $tpl = $this->gadget->template->load('Webcam.html');
        $model = $this->gadget->model->load('Webcam');
        $webcams = $model->GetWebcams();
        if (!Jaws_Error::IsError($webcams)) {
            $tpl->SetBlock('webcam');
            $tpl->SetVariable('title', _t('WEBCAM_WEBCAMS'));
            foreach ($webcams as $webcam) {
                $tpl->SetBlock('webcam/item');
                $tpl->SetVariable('url',     $webcam['url']);
                $tpl->SetVariable('title',   $webcam['title']);
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
     * @return  string  XHTML content of the webcam
     */
    function Random()
    {
        $tpl = $this->gadget->template->load('Webcam.html');
        $model = $this->gadget->model->load('Webcam');
        $webcam = $model->GetRandomWebCam();
        if (!Jaws_Error::IsError($webcam)) {
            $tpl->SetBlock('webcam');
            $tpl->SetVariable('title', _t('WEBCAM_WEBCAMS'));
            $tpl->SetBlock('webcam/item');
            $tpl->SetVariable('url',     $webcam['url']);
            $tpl->SetVariable('title',   $webcam['title']);
            $tpl->SetVariable('id',      $webcam['id']);
            $tpl->SetVariable('refresh', $webcam['refresh']);
            $tpl->ParseBlock('webcam/item');
            $tpl->ParseBlock('webcam');
        }

        return $tpl->Get();
    }
}
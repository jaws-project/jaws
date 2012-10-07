<?php
/**
 * Webcam - URL List gadget hook
 *
 * @category   GadgetHook
 * @package    Webcam
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2007-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class WebcamURLListHook
{
    /**
     * Returns an array with all available items the Menu gadget 
     * can use
     *
     * @access  public
     */
    function Hook()
    {
        $urls[] = array('url'   => $GLOBALS['app']->Map->GetURLFor('Webcam', 'DefaultAction'),
                        'title' => _t('WEBCAM_NAME'));
        return $urls;
    }
}

<?php
/**
 * Sitemap Admin Gadget
 *
 * @category   GadgetAdmin
 * @package    Sitemap
 * @author     Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright  2008-2020 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Sitemap_Actions_Admin_Ping extends Jaws_Gadget_Action
{
    /**
     * Ping Search Engines
     *
     * @access  public
     * @return  string  XHTML content
     */
    function PingSearchEngines()
    {
        $model = $this->gadget->model->loadAdmin('Ping');
        $res = $model->PingSearchEngines();
        if (Jaws_Error::IsError($res) || $res === false) {
            $this->gadget->session->push(
                _t('SITEMAP_ERROR_CANT_PING_SEARCHENGINES'),
                RESPONSE_ERROR
            );
        } else {
            $this->gadget->session->push(
                _t('SITEMAP_SEARCHENGINES_PINGED'),
                RESPONSE_NOTICE
            );
        }

        return $this->gadget->session->pop();
    }

}
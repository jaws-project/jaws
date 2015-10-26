<?php
/**
 * Sitemap Gadget
 *
 * @category   Gadget
 * @package    Sitemap
 * @author     Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright  2014-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Sitemap_Actions_Robots extends Jaws_Gadget_Action
{
    /**
     *  Get robots.txt content
     *
     * @access  public
     * @return  string  XML content
     */
    function Robots()
    {
        header('Content-Type: text/plain; charset=utf-8');
        $robots = $this->gadget->registry->fetch('robots.txt');
        return $robots;
    }
}
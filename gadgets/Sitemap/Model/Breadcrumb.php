<?php
/**
 * Sitemap Gadget
 *
 * @category   GadgetModel
 * @package    Sitemap
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2006-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Sitemap_Model_Breadcrumb extends Jaws_Gadget_Model
{
    /**
     * Returns an array with info of each path element of a given path
     *
     * @access  public
     * @param   string  $path   URL Path
     * @return  array   Array with info of each path element
     */
    function GetBreadcrumb($path)
    {

        $breadcrumb = array();
        $breadcrumb['/'] = _t('SITEMAP_HOME');
        $apath = explode('/',$path);
        $a = $this->_items;
        for ($i = 0; $i < count($apath); $i++) {
            for ($j = 0; $j < count($a); $j++) {
                if ($a[$j]['shortname'] == $apath[$i]) {
                    $breadcrumb[$a[$j]['url']] = $a[$j]['title'];
                    $a = $a[$j]['childs'];
                    break;
                }
            }
        }

        return $breadcrumb;
    }

}
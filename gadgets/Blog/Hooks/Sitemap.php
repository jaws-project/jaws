<?php
/**
 * Blog - Sitemap gadget hook
 *
 * @category    GadgetHook
 * @package     Blog
 * @author      Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright   2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class Blog_Hooks_Sitemap extends Jaws_Gadget_Hook
{
    /**
     * Returns the category and items for sitemap
     *
     * @access  public
     * @param   int     $level   0=>just return one level of categories
     *                           1=>return all categories
     *                           2=>return all categories and items
     * @return  array   An array of entries that matches a certain pattern
     */
    function Execute($level)
    {
        if ($level == 0 || $level == 1) {
            $model = $this->gadget->model->load('Categories');
            $categories = $model->GetCategories();

            $result = array();
            foreach($categories as $cat) {
                $data['id']     =  $cat['id'];
                $data['name']   =  $cat['name'];
                $result[] = $data;
            }
        } else if($level == 2) {
            $result = '';
        }

        return $result;
    }

}
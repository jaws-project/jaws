<?php
/**
 * Faq - URL List gadget hook
 *
 * @category   GadgetHook
 * @package    Faq
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2007-2014 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Faq_Hooks_Menu extends Jaws_Gadget_Hook
{
    /**
     * Returns an array with all available items the Menu gadget 
     * can use
     *
     * @access  public
     * @return  array   URLs array
     */
    function Execute()
    {
        $urls   = array();
        $urls[] = array('url'   => $this->gadget->urlMap('View'),
                        'title' => $this->gadget->title);

        //Load model
        $model = $this->gadget->model->load('Category');
        $categories = $model->GetCategories();
        if (!Jaws_Error::isError($categories)) {
            $max_size = 20;
            foreach ($categories as $category) {
                $url = $this->gadget->urlMap('ViewCategory', array('id' => $category['id']));
                $urls[] = array('url'   => $url,
                                'title' => (Jaws_UTF8::strlen($category['category']) > $max_size)?
                                            Jaws_UTF8::substr($category['category'], 0, $max_size).'...' :
                                            $category['category']);
            }
        }
        return $urls;
    }
}

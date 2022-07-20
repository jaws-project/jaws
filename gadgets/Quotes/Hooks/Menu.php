<?php
/**
 * Quotes - URL List gadget hook
 *
 * @category   GadgetHook
 * @package    Quotes
 */
class Quotes_Hooks_Menu extends Jaws_Gadget_Hook
{
    /**
     * Returns an array with all available items the Menu gadget can use
     *
     * @access  public
     * @return  array   URLs array
     */
    function Execute()
    {
        $urls = array();
        $urls[] = array(
            'url'   => $this->gadget->urlMap('quotes'),
            'title' => $this::t('ACTIONS_QUOTES_TITLE')
        );

        $categories = Jaws_Gadget::getInstance('Categories')
            ->model->load('Categories')
            ->getCategories(
                array('gadget' => $this->gadget->name, 'action' => 'Quotes')
            );
        if (!empty($categories) && !Jaws_Error::IsError($categories)) {
            foreach ($categories as $category) {
                $urls[] = array(
                    'url' => $this->gadget->urlMap('quotes', array('category' => $category['id'])),
                    'title' => $category['title']
                );
            }
        }

        return $urls;
    }
}
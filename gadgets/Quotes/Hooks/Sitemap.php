<?php
/**
 * Quotes - Sitemap hook
 *
 * @category    GadgetHook
 * @package     Quotes
 */
class Quotes_Hooks_Sitemap extends Jaws_Gadget_Hook
{
    /**
     * Fetch items can be included in sitemap
     *
     * @access  public
     * @param   int     $data_type      Data type
     * @param   int     $updated_time   Last updated time
     *          (0: first level of categories, 1: all levels of categories, 2: flatted all items)
     * @return  mixed   Array of data otherwise Jaws_Error
     */
    function Execute($data_type = 0, $updated_time = 0)
    {
        $result = array(
            '/' => array(
                'id'     => 0,
                'parent' => 0,
                'title'  => $this::t('TITLE'),
                'url'    => $this->gadget->urlMap('quotes', array(), array('absolute' => true))
            ),
            'levels' => array(),
            'items'  => array()
        );
        if ($data_type == 0) {
            $categories = Jaws_Gadget::getInstance('Categories')
                ->model->load('Categories')
                ->getCategories(
                    array('gadget' => $this->gadget->name, 'action' => 'Quotes')
                );
            if (Jaws_Error::IsError($categories)) {
                return $categories;
            }

            foreach ($categories as $category) {
                $result['levels'][] = array(
                    'id'     => $category['id'],
                    'title'  => $category['title'],
                );
            }
        } elseif ($data_type == 1 || $data_type == 2) {
            $categories = Jaws_Gadget::getInstance('Categories')
                ->model->load('Categories')
                ->getCategories(
                    array('gadget' => $this->gadget->name, 'action' => 'Quotes')
                );
            if (Jaws_Error::IsError($categories)) {
                return $categories;
            }
            foreach ($categories as $category) {
                $cat = $category['id'];
                $result['levels'][] = array(
                    'id'     => $category['id'],
                    'parent' => $category['id'],
                    'title'  => $category['title'],
                    'lastmod'=> null,
                    'url'    => $this->gadget->urlMap(
                        'quotes',
                        array('category' => $cat),
                        array('absolute' => true)
                    ),
                );
            }

            if ($data_type == 2) {
                $quotes = $this->gadget->model->load('Quotes')->list();
                if (Jaws_Error::IsError($quotes)) {
                    return $quotes;
                }
                foreach ($quotes as $quote) {
                    $result['items'][] = array(
                        'id'      => $quote['id'],
                        'parent'  => $quote['category']['id'],
                        'title'   => $quote['title'],
                        'lastmod' => $quote['updated'],
                        'url' => $this->gadget->urlMap(
                            'quote',
                            array('id' => $quote['id'], 'metaurl' => $quote['meta_keywords']),
                            array('absolute' => true)
                        ),
                    );
                }
            }
        }
        return $result;
    }

}
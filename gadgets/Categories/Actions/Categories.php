<?php
/**
 * Categories Gadget
 *
 * @category   Gadget
 * @package    Categories
 */
class Categories_Actions_Categories extends Jaws_Gadget_Action
{
    /**
     * Get reference categories interface(new template engine version)
     *
     * @access  public
     * @param   array   $interface  Gadget interface(gadget, action, reference, ...)
     * @param   array   $options    User interface control options(labels, ...)
     * @return  array   Array of categories interface data & options
     */
    function xloadReferenceCategories($interface = array(), $options = array())
    {
        $defaultOptions = array(
            'labels' => array(
                'title' => '',
                'placeholder' => '',
            ),
            'multiple'   => false,
            'autoinsert' => false,
            'direction'  => Jaws::t('LANG_DIRECTION'),
        );
        $options = array_merge($defaultOptions, $options);

        $defaultInterface = array(
            'gadget'    => '',
            'action'    => '',
            'reference' => 0,
            'selected'  => 0
        );
        $interface = array_merge($defaultInterface, $interface);
        // optional input_reference for new record(without reference id)
        // or update/insert multi references together
        if (!array_key_exists('input_reference', $interface)) {
            $interface['input_reference'] = $interface['reference'];
        }

        $this->AjaxMe('index.js');

        // initiate assign with option array 
        $assigns = $options;
        $assigns['interface'] = $interface;
        $assigns['input_action'] = strtolower($interface['action']);
        $assigns['input_reference'] = strtolower($interface['input_reference']);

        // categories
        $assigns['categories'] = array();
        $categories = $this->gadget->model->load('Categories')->getCategories($interface);
        if (!Jaws_Error::IsError($categories)) {
            $assigns['categories'] = $categories;
        }

        // fetch categories that reference belong to
        if (!empty($interface['reference']) && empty($interface['selected'])) {
            $assigns['selected'] = array();
            $selected = $this->gadget->model->load('Categories')->getReferenceCategories($interface);
            if (!Jaws_Error::IsError($selected)) {
               $assigns['selected'] = $selected;
            }
        }

        return $assigns;
    }

    /**
     * Insert/Update reference categories
     *
     * @access  public
     * @param   array   $interface  Gadget interface(gadget, action, reference, ...)
     * @param   array   $options    User interface control options(labels, ...)
     * @return  mixed   TRUE otherwise Jaws_Error on error
     */
    function updateReferenceCategories($interface, $options = array())
    {
        $defaultOptions = array(
            'multiple'   => false,
            'autoinsert' => false,
        );
        $options = array_merge($defaultOptions, $options);

        $defaultInterface = array(
            'gadget'    => '',
            'action'    => '',
            'reference' => 0,
            'selected'  => 0
        );
        $interface = array_merge($defaultInterface, $interface);
        // optional input_reference for new record(without reference id)
        // or update/insert multi references together
        if (!array_key_exists('input_reference', $interface)) {
            $interface['input_reference'] = $interface['reference'];
        }

        $input_categories = (array)$this->app->request->fetch(
            strtolower('category_'. $interface['action'] . '_'. $interface['input_reference']),
            'post'
        );
        if ($options['multiple'] && empty($input_categories)) {
            $input_categories = (array)$this->app->request->fetch(
                strtolower('category_'. $interface['action'] . '_'. $interface['input_reference']).':array',
                'post'
            );
        }

        $new_categories = preg_filter('/__(.*)__/', '$1', $input_categories);
        $old_categories = array_values(array_diff_key($input_categories, $new_categories));
        // insert new categories
        $new_categories = $this->gadget->model->load('Categories')->insertCategories($interface, $new_categories);
        if (Jaws_Error::IsError($new_categories)) {
            return $new_categories;
        }
        $input_categories = array_merge($old_categories, $new_categories);

        if (!empty($interface['reference'])) {
            $categories = $this->gadget->model->load('Categories')->getReferenceCategories($interface);
            if (Jaws_Error::IsError($categories)) {
                return $categories;
            }

            // get old/new/delete reference categories
            $old_ref_categories = array_column($categories, 'id');
            $del_ref_categories = array_diff($old_ref_categories, $input_categories);
            $new_ref_categories = array_diff($input_categories, $old_ref_categories);

            $result = $this->gadget->model->load('Categories')->setReferenceCategories(
                $interface,
                $new_ref_categories,
                $del_ref_categories
            );
            if (Jaws_Error::IsError($result)) {
                return $result;
            }
        }

        return $input_categories;
    }

}
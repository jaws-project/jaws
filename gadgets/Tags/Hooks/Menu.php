<?php
/**
 * Tags - URL List gadget hook
 *
 * @category    GadgetHook
 * @package     Tags
 */
class Tags_Hooks_Menu extends Jaws_Gadget_Hook
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
            'url' => $this->gadget->urlMap('TagCloud'),
            'title' => $this::t('TAG_CLOUD', Jaws::t('ALL'))
        );

        $model = $this->gadget->model->load('Tags');
        $gadgets = $model->GetTagableGadgets();
        foreach ($gadgets as $gadget => $title) {
            $urls[] = array(
                'url' => $this->gadget->urlMap('TagCloud', array('tagged_gadget' => $gadget)),
                'title' => $this::t('TAG_CLOUD', $title)
            );
        }

        return $urls;
    }

}
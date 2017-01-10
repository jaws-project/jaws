<?php
/**
 * Tags Gadget
 *
 * @category    Gadget
 * @package     Tags
 */
class Tags_Actions_Default extends Jaws_Gadget_Action
{
    /**
     * Displays menu bar according to selected action
     *
     * @access  public
     * @param   string  $selected_action    Selected action
     * @param   array   $visible_actions    Visible actions
     * @param   array   $action_params      action params
     * @return  string XHTML template content
     */
    function MenuBar($selected_action, $visible_actions, $action_params = null)
    {
        $actions = array('ManageTags', 'ViewTag');
        if (!in_array($selected_action, $actions)) {
            $selected_action = 'ManageTags';
        }

        $menubar = new Jaws_Widgets_Menubar();
        if (in_array('ViewTag', $visible_actions)) {
            $menubar->AddOption('ViewTag',_t('TAGS_VIEW_TAG'),
                $this->gadget->urlMap('ViewTag', $action_params), 'gadgets/Tags/Resources/images/view-tag-mini.png');
        }

        if (in_array('ManageTags', $visible_actions)) {
            $menubar->AddOption('ManageTags',_t('TAGS_MANAGE_TAGS'),
            $this->gadget->urlMap('ManageTags'), 'gadgets/Tags/Resources/images/manage-tags-mini.png');
        }

        $menubar->Activate($selected_action);

        return $menubar->Get();
    }

}
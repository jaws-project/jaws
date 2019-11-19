<?php
/**
 * Files Gadget
 *
 * @category   Gadget
 * @package    Files
 */
class Files_Actions_Files extends Jaws_Gadget_Action
{
    /**
     * Get reference tags
     *
     * @access  public
     * @param   string  $gadget         Gadget name
     * @param   string  $action         Action name
     * @param   int     $reference      Reference ID
     * @param   object  $tpl            Jaws_Template object
     * @param   string  $tpl_base_block Template block name
     * @param   int     $user           User owner of tag(0: for global tags)
     * @return  void
     */
    function loadReferenceFiles($gadget, $action, $reference, &$tpl, $tpl_base_block, $user = 0)
    {
        $tagsModel = $this->gadget->model->load('Tags');
        $tags = $tagsModel->GetReferenceTags($gadget, $action, $reference, $user);
        if (Jaws_Error::IsError($tags)) {
            return false;
        }

        if (!empty($tags)) {
            // store tags of main request for later use
            if ($this->app->inMainRequest) {
                self::$mainRequestReference = array(
                    'gadget' => $gadget,
                    'action' => $action,
                    'reference' => $reference
                );
                self::$mainRequestTags = array_column($tags, 'id');
            }

            $tpl->SetBlock("$tpl_base_block/tags");
            $tpl->SetVariable('lbl_tags', _t('GLOBAL_TAGS'));
            foreach($tags as $tag) {
                $tpl->SetBlock("$tpl_base_block/tags/tag");
                $tpl->SetVariable('name', $tag['name']);
                $tpl->SetVariable('title', $tag['title']);
                $tpl->SetVariable(
                    'url',
                    $this->gadget->urlMap('ViewTag', array('tag'=>$tag['name'], 'tagged_gadget'=>$gadget))
                );
                $tpl->ParseBlock("$tpl_base_block/tags/tag");
            }
            $tpl->ParseBlock("$tpl_base_block/tags");
        }

    }

}
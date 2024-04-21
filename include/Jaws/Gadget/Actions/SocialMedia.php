<?php
/**
 * Jaws Gadgets : HTML part
 *
 * @category    Gadget
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2020-2024 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Gadget_Actions_SocialMedia extends Jaws_Gadget_Class
{
    /**
     * Get Social Media share links
     *
     * @access  public
     * @param   object  $tpl        (Optional) Jaws Template object
     * @param   array   $options    (Optional) Parameters options
     * @return  string  XHTML template content
     */
    function links($tpl, $options = array())
    {
        if (empty($tpl)) {
            $tpl = new Jaws_Template();
            $tpl->Load('SocialMedia.html', 'include/Jaws/Resources');
            $block = '';
        } else {
            $block = $tpl->GetCurrentBlockPath();
        }

        // set undefined keys by default values
        $options = array_merge(
            array(
                'url'       => '',
                'subject'   => '',
                'summary'   => '',
                'image'     => '',
            ),
            $options
        );
        $options = array_map('urlencode', $options);

        $tpl->SetBlock("$block/social-media");
        $tpl->SetVariable('url',     $options['url']);
        $tpl->SetVariable('subject', $options['subject']);
        $tpl->SetVariable('summary', $options['summary']);
        $tpl->SetVariable('image',   $options['image']);

        $tpl->ParseBlock("$block/social-media");
        return $tpl->Get();
    }

    /**
     * Get Social Media share links assign array
     *
     * @access  public
     * @param   array   $options    (Optional) Parameters options
     * @return  array   Social Media share links assign array
     */
    function xlinks($options = array())
    {
        // set undefined keys by default values
        $options = array_merge(
            array(
                'url'       => '',
                'subject'   => '',
                'summary'   => '',
                'image'     => '',
            ),
            $options
        );
        $assigns = array_map('rawurlencode', $options);

        return $assigns;
    }

}
<?php
/**
 * Shoutbox - Comments gadget hook
 *
 * @category   GadgetHook
 * @package    Shoutbox
 * @author     ZehneZiba <zzb@zehneziba.ir>
 * @copyright   2014-2022 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Shoutbox_Hooks_Comments extends Jaws_Gadget_Hook
{
    /**
     * Returns an array about a Shoutbox
     *
     * @access  public
     * @param   string  $action     Action name
     * @param   int     $reference  Reference id
     * @return  array   entry info
     */
    function Execute($action, $reference)
    {
        return array(
            'reference_title' => '',
            'reference_link'  => '',
            'author_name'     => '',
            'author_nickname' => '',
            'author_email'    => '',

        );
    }
}
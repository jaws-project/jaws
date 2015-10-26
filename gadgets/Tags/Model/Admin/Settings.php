<?php
/**
 * Tags Gadget Admin
 *
 * @category    GadgetModel
 * @package     Tags
 * @author      Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright   2013-2015 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Tags_Model_Admin_Settings extends Jaws_Gadget_Model
{
    /**
     * Updates the Tag gadget settings
     *
     * @access  public
     * @param   string  $tagResultLimit  Allow comments?
     * @return  mixed   True on success or Jaws_Error on failure
     */
    function SaveSettings($tagResultLimit)
    {
        $res = $this->gadget->registry->update('tag_results_limit', $tagResultLimit);
        if ($res === false) {
            return Jaws_Error::raiseError(
                _t('TAGS_ERROR_CANT_UPDATE_PROPERTIES'),
                __FUNCTION__
            );
        }

        return true;
    }

}
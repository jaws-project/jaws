/**
 * Contact Javascript front-end actions
 *
 * @category    Ajax
 * @package     Contact
 */
function Jaws_Gadget_Contact() { return {
    // ASync callback method
    AjaxCallback : {
    },
}};

/**
 *
 */
function submitContactForm(form)
{
    $(form).find('button').attr("disabled", true);
    return true;
}

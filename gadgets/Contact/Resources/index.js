/**
 * Contact Javascript front-end actions
 *
 * @category    Ajax
 * @package     Contact
 */


/**
 *
 */
function submitContactForm(form)
{
    $(form).find('button').attr("disabled", true);
    return true;
}

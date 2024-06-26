/**
 * Shoutbox Javascript actions
 *
 * @category   Ajax
 * @package    Shoutbox
 * @author     Jonathan Hernandez <ion@gluch.org.mx>
 * @copyright   2005-2024 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
function Jaws_Gadget_Shoutbox() { return {
    // ASync callback method
    AjaxCallback : {
    },
}};
/**
 * Use async mode, create Callback
 */
var ShoutboxCallback = {
};

/**
 * Update the properties
 *
 */
function updateProperties(form)
{
    var limitEntries = form['limit_entries'].value,
        max_strlen = form['max_strlen'].value,
        authority = form['authority'].value;
    ShoutboxAjax.call('UpdateProperties', [limitEntries, max_strlen, authority]);
}

var ShoutboxAjax = new JawsAjax('Shoutbox', ShoutboxCallback);

var firstFetch = true;
var currentIndex = 0;

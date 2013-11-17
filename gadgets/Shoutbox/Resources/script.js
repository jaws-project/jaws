/**
 * Shoutbox Javascript actions
 *
 * @category   Ajax
 * @package    Shoutbox
 * @author     Jonathan Hernandez <ion@gluch.org.mx>
 * @copyright  2005-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
/**
 * Use async mode, create Callback
 */
var ShoutboxCallback = {

    UpdateProperties: function(response) {
        showResponse(response);
    }
}

/**
 * Update the properties
 *
 */
function updateProperties(form)
{
    var limitEntries = form.elements['limit_entries'].value;
    var max_strlen   = form.elements['max_strlen'].value;
    var authority    = form.elements['authority'].value;
    ShoutboxAjax.callAsync('UpdateProperties', limitEntries, max_strlen, authority);
}

var ShoutboxAjax = new JawsAjax('Shoutbox', ShoutboxCallback);

var firstFetch = true;
var currentIndex = 0;

/**
 * ControlPanel Javascript actions
 *
 * @category    Ajax
 * @package     ControlPanel
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
/**
 * Use async mode, create Callback
 */
var ControlPanelCallback = {
    JawsVersion: function(response) {
        $('#latest_jaws_version').html(response);
        if (!response) {
            $('div.notify_version').css('display', 'block');
        }
    }
}

/**
 * collapse/uncollapse notify boxes
 */
function toggleCollapse()
{
    $(this).toggleClass('collapsed');
    ControlPanelStorage.update(
        $('#sidebar').children().index($(this).parent()),
        $(this).attr('class')
    );
    $(this).next('div').toggle();
}

/**
 * Initiates script
 */
function init()
{
    $('#sidebar h2').on('click', toggleCollapse);
    $('#sidebar h2').each(function(i, el) {
        if (ControlPanelStorage.fetch(i)) {
            $(el).trigger('click');
        }
    });

    if ($('#do_checking') && $('#do_checking').value == 1) {
        ControlPanelAjax.callAsync('JawsVersion');
    }
}

var ControlPanelAjax = new JawsAjax('ControlPanel', ControlPanelCallback);
var ControlPanelStorage = new JawsStorage('ControlPanel');
ControlPanelAjax.backwardSupport();

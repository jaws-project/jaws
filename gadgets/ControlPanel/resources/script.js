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
        $('latest_jaws_version').set('html', response);
        if (!response.blank() && $('current_jaws_version').value != response) {
            $(document.body).getElement('div.notify_version').setStyle('display', 'block');
        }
    }
}

/**
 * collapse/uncollapse notify boxes
 */
function toggleCollapse()
{
    this.toggleClass('collapsed');
    ControlPanelStorage.update(
        $('sidebar').getChildren().indexOf(this.getParent()),
        this.getProperty('class')
    );
    this.getNext('div').toggle();
}

/**
 * Initiates script
 */
function init()
{
    $('sidebar').getElements('h2').addEvent('click', toggleCollapse);
    $('sidebar').getChildren().each(function(el, i) {
        if (ControlPanelStorage.fetch(i)) {
            el.getChildren('h2').fireEvent('click');
        }
    });

    ControlPanelAjax.callAsync('JawsVersion');
}

var ControlPanelAjax = new JawsAjax('ControlPanel', ControlPanelCallback);
var ControlPanelStorage = new JawsStorage('ControlPanel');
ControlPanelAjax.backwardSupport();

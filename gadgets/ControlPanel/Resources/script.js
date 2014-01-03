/**
 * ControlPanel Javascript actions
 *
 * @category    Ajax
 * @package     ControlPanel
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2013-2014 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
/**
 * Use async mode, create Callback
 */
var ControlPanelCallback = {
    JawsVersion: function(response) {
        $('latest_jaws_version').set('html', response);
        if (!response.blank() && response !== $('jaws_version').value) {
            $$('div.notify_version').setStyle('display', 'block');
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

    // compare current version with latest jaws version
    if (!$('latest_jaws_version').get('text').blank() &&
        $('latest_jaws_version').get('text') !== $('jaws_version').value)
    {
        $$('div.notify_version').setStyle('display', 'block');
    }

    // check jaws project website for latest version
    if ($('do_checking').value == 1) {
        ControlPanelAjax.callAsync('JawsVersion');
    }
}

var ControlPanelAjax = new JawsAjax('ControlPanel', ControlPanelCallback);
var ControlPanelStorage = new JawsStorage('ControlPanel');

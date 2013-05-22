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
}

/**
 * collapse/uncollapse notify boxes
 */
function toggleCollapse()
{
    this.toggleClass('collapsed');
    this.getNext('div').toggle();
}

/**
 * Initiates script
 */
function init()
{
    $('sidebar').getElements('h2').addEvent('click', toggleCollapse);
}

var ControlPanelAjax = new JawsAjax('ControlPanel', ControlPanelCallback);

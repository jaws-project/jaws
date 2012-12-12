/**
 * VisitCounter Javascript actions
 *
 * @category   Ajax
 * @package    VisitCounter
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
/**
 * Use async mode, create Callback
 */
var VisitCounterCallback = { 
    cleanentries: function(response) {
        if (response[0]['css'] == 'notice-message') {
            $('visitcounter_datagrid').setCurrentPage(0);
            $('visitcounter_datagrid').rowsSize = 0;
            $('visitcounter_datagrid').updatePageCounter();
            getDG();
            updateStats();
        }
        showResponse(response);
    },
    
    resetcounter: function(response) {
        if (response[0]['css'] == 'notice-message') {
            $('visitcounter_datagrid').setCurrentPage(0);
            $('visitcounter_datagrid').rowsSize = 0;
            $('visitcounter_datagrid').updatePageCounter();
            getDG();
            updateStats();
        }
        showResponse(response);
    }, 

    updateproperties: function(response) {
        showResponse(response);
    }
}

/**
 * Reset counter
 */
function resetCounter()
{
    VisitCounterAjax.callAsync('resetcounter');    
}

/**
 * Clean entries
 */
function cleanEntries()
{
    VisitCounterAjax.callAsync('cleanentries');    
}

/**
 * Update stats
 */
function updateStats()
{
    var vcSync = new visitcounteradminajax();

    $('stats_from').innerHTML  = vcSync.getstartdate();
    $('visitors').innerHTML    = 0;
    $('impressions').innerHTML = 0;
}

/**
 * Update the properties
 */
function updateProperties(form)
{
    var online     = form.elements['c_kind[]'].item(0).checked;
    var today      = form.elements['c_kind[]'].item(1).checked;
    var total      = form.elements['c_kind[]'].item(2).checked;
    var custom     = form.elements['c_kind[]'].item(3).checked;
    var numDays    = form.elements['period'].value;
    var type       = form.elements['type'].value;
    var mode       = form.elements['mode'].value;
    var customText = form.elements['custom_text'].value;
    
    VisitCounterAjax.callAsync('updateproperties', online, today, total, custom, numDays, type, mode, customText);
}

var VisitCounterAjax = new JawsAjax('VisitCounter', VisitCounterCallback);

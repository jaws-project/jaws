/**
 * VisitCounter Javascript actions
 *
 * @category   Ajax
 * @package    VisitCounter
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
/**
 * Use async mode, create Callback
 */
var VisitCounterCallback = { 
    cleanentries: function(response) {
        if (response[0]['css'] == 'notice-message') {
            _('visitcounter_datagrid').setCurrentPage(0);
            _('visitcounter_datagrid').rowsSize = 0;
            _('visitcounter_datagrid').updatePageCounter();
            getDG();
            updateStats();
        }
        showResponse(response);
    },
    
    resetcounter: function(response) {
        if (response[0]['css'] == 'notice-message') {
            _('visitcounter_datagrid').setCurrentPage(0);
            _('visitcounter_datagrid').rowsSize = 0;
            _('visitcounter_datagrid').updatePageCounter();
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
    _('stats_from').innerHTML  = VisitCounterAjax.callSync('getstartdate');
    _('visitors').innerHTML    = 0;
    _('impressions').innerHTML = 0;
}

/**
 * Update the properties
 */
function updateProperties(form)
{
    var numDays    = form.elements['period'].value,
        type       = form.elements['type'].value,
        mode       = form.elements['mode'].value,
        customText = form.elements['custom_text'].value,
        counters   = [];

    form.getElements('input[name=c_kind[]]').each(function(input) {
        if (input.checked) {
            counters.push(input.value);
        }
    });

    VisitCounterAjax.callAsync('updateproperties', counters.join(), numDays, type, mode, customText);
}

var VisitCounterAjax = new JawsAjax('VisitCounter', VisitCounterCallback);

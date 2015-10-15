/**
 * VisitCounter Javascript actions
 *
 * @category   Ajax
 * @package    VisitCounter
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2014 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
/**
 * Use async mode, create Callback
 */
var VisitCounterCallback = { 
    CleanEntries: function(response) {
        if (response[0]['type'] == 'response_notice') {
            $('visitcounter_datagrid')[0].setCurrentPage(0);
            $('visitcounter_datagrid')[0].rowsSize = 0;
            $('visitcounter_datagrid')[0].updatePageCounter();
            getDG();
            updateStats();
        }
        VisitCounterAjax.showResponse(response);
    },
    
    ResetCounter: function(response) {
        if (response[0]['type'] == 'response_notice') {
            $('visitcounter_datagrid')[0].setCurrentPage(0);
            $('visitcounter_datagrid')[0].rowsSize = 0;
            $('visitcounter_datagrid')[0].updatePageCounter();
            getDG();
            updateStats();
        }
        VisitCounterAjax.showResponse(response);
    }, 

    UpdateProperties: function(response) {
        VisitCounterAjax.showResponse(response);
    }
}

/**
 * Reset counter
 */
function resetCounter()
{
    VisitCounterAjax.callAsync('ResetCounter');    
}

/**
 * Clean entries
 */
function cleanEntries()
{
    VisitCounterAjax.callAsync('CleanEntries');    
}

/**
 * Update stats
 */
function updateStats()
{
    $('stats_from').innerHTML  = VisitCounterAjax.callSync('GetStartDate');
    $('visitors').innerHTML    = 0;
    $('impressions').innerHTML = 0;
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

    VisitCounterAjax.callAsync(
        'UpdateProperties',
        [counters.join(), numDays, type, mode, customText]
    );
}

var VisitCounterAjax = new JawsAjax('VisitCounter', VisitCounterCallback);

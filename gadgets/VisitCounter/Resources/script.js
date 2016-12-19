/**
 * VisitCounter Javascript actions
 *
 * @category   Ajax
 * @package    VisitCounter
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */

/**
 * Use async mode, create Callback
 */
var VisitCounterCallback = { 
    CleanEntries: function(response) {
        if (response[0]['type'] == 'alert-success') {
            $('#visitcounter_datagrid')[0].setCurrentPage(0);
            $('#visitcounter_datagrid')[0].rowsSize = 0;
            $('#visitcounter_datagrid')[0].updatePageCounter();
            getDG();
            updateStats();
        }
        VisitCounterAjax.showResponse(response);
    },
    
    ResetCounter: function(response) {
        if (response[0]['type'] == 'alert-success') {
            $('#visitcounter_datagrid')[0].setCurrentPage(0);
            $('#visitcounter_datagrid')[0].rowsSize = 0;
            $('#visitcounter_datagrid')[0].updatePageCounter();
            getDG();
            updateStats();
        }
        VisitCounterAjax.showResponse(response);
    }, 

    UpdateProperties: function(response) {
        VisitCounterAjax.showResponse(response);
    }
};

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
    $('#stats_from').html(VisitCounterAjax.callSync('GetStartDate'));
    $('#visitors').html(0);
    $('#impressions').html(0);
}

/**
 * Update the properties
 */
function updateProperties(form)
{
    var numDays = form.elements['period'].value,
        type = form.elements['type'].value,
        mode = form.elements['mode'].value,
        customText = form.elements['custom_text'].value,
        counters = [];

    $(form).find("input[name='c_kind[]']").each(function () {
        if ($(this).prop('checked')) {
            counters.push($(this).val());
        }
    });

    VisitCounterAjax.callAsync(
        'UpdateProperties',
        [counters.join(), numDays, type, mode, customText]
    );
}

var VisitCounterAjax = new JawsAjax('VisitCounter', VisitCounterCallback);

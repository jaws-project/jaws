/**
 * AbuseReporter Javascript actions
 *
 * @category    Ajax
 * @package     AbuseReporter
 */

/**
 * Use async mode, create Callback
 */
var AbuseReporterCallback = {
    SaveSettings: function(response) {
        AbuseReporterAjax.showResponse(response);
    }
}

/**
 * Stops doing a certain action
 */
function stopAction()
{
    selectedReport = 0;
}


$(document).ready(function () {

});

var AbuseReporterAjax = new JawsAjax('AbuseReporter', AbuseReporterCallback),
    selectedReport = 0;
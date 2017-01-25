/**
 * AbuseReporter Javascript actions
 *
 * @category    Ajax
 * @package     AbuseReporter
 */
var AbuseReporterCallback = {
    SaveReport: function (response) {
        AbuseReporterAjax.showResponse(response);
        $('#reportModal').modal('hide');
    },

}

/**
 * stop Action
 */
function stopAction() {
    $('.report-modal-body').html('');
}

/**
 * Open report windows
 */
function openReportWindows(gadget, action, reference, url) {
    var reportUI = AbuseReporterAjax.callSync(
        'ReportUI',
        {
            'report_gadget': gadget,
            'report_action': action,
            'report_reference': reference
        }
    );
    $("#report-dialog-" + gadget + '-' + action + '-' + reference).html(reportUI);
    $('#reportModal-'+ gadget + '-' + action + '-' + reference).modal();
}

/**
 * Save new report
 */
function saveReport(gadget, action, reference, url) {
    var formId = "#report-form-" + gadget + '-' + action + '-' + reference;
    AbuseReporterAjax.callAsync(
        'SaveReport',
        {
            'report_gadget': gadget,
            'report_action': action,
            'report_reference': reference,
            'url': url,
            'comment': $(formId + ' #comment').val(),
            'type': $(formId + ' #type').val(),
            'priority': $(formId + ' #priority').val(),
        }
    );

}

$(document).ready(function () {
});

var AbuseReporterAjax = new JawsAjax('AbuseReporter', AbuseReporterCallback);
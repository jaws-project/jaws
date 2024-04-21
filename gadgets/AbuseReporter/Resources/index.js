/**
 * AbuseReporter Javascript actions
 *
 * @category    Ajax
 * @package     AbuseReporter
 */
function Jaws_Gadget_AbuseReporter() { return {
    // ASync callback method
    AjaxCallback : {
    },
}};
var AbuseReporterCallback = {
    SaveReport: function (response) {
        var reportSign = response.data.gadget + '-' + response.data.action + '-' + response.data.reference;
        Jaws_Gadget.getInstance('AbuseReporter').message.show(
            response,
            $('#report-response-' + reportSign)
        );
        if (response.type == 'alert-success') {
            $('#reportModal-' + reportSign).modal('hide');
        }
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
    var reportUI = AbuseReporterAjax.call(
        'ReportUI',
        {
            'report_gadget': gadget,
            'report_action': action,
            'report_reference': reference
        },
        false, {'async': false}
    );
    $("#report-dialog-" + gadget + '-' + action + '-' + reference).html(reportUI);
    $('#reportModal-'+ gadget + '-' + action + '-' + reference).modal();
}

/**
 * Save new report
 */
function saveReport(gadget, action, reference, url) {
    var formId = "#report-form-" + gadget + '-' + action + '-' + reference;
    AbuseReporterAjax.call(
        'SaveReport',
        {
            'report_gadget': gadget,
            'report_action': action,
            'report_reference': reference,
            'url': url,
            'comment': $(formId + ' #comment').val(),
            'type': $(formId + ' #type').val(),
            'priority': $(formId + ' #priority').val(),
        },
        false,
        {'showMessage': false}
    );

}

$(document).ready(function () {
});

var AbuseReporterAjax = new JawsAjax('AbuseReporter', AbuseReporterCallback);
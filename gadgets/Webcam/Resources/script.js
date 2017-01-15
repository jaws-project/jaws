/**
 * Webcam Javascript actions
 *
 * @category   Ajax
 * @package    Webcam
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2005-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */

/**
 * Use async mode, create Callback
 */
var WebcamCallback = {
    NewWebcam: function(response) {
        if (response[0]['type'] == 'alert-success') {
            $('#webcam_datagrid')[0].addItem();
            $('#webcam_datagrid')[0].setCurrentPage(0);
            getDG();
        }
        WebcamAjax.showResponse(response);
    },

    DeleteWebcam: function(response) {
        if (response[0]['type'] == 'alert-success') {
            $('#webcam_datagrid')[0].deleteItem();
            getDG();
        }
        WebcamAjax.showResponse(response);
    },
    
    UpdateWebcam: function(response) {
        if (response[0]['type'] == 'alert-success') {
            getDG();
        }
        WebcamAjax.showResponse(response);
    },

    GetWebcam: function(response) {
        updateForm(response);
    },

    UpdateProperties: function(response) {
        WebcamAjax.showResponse(response);
    }
};

/**
 * Clean the form
 */
function cleanForm(form) 
{
    form.reset();
    form.elements['action'].value = 'AddWebcam';
}

/**
 * Update form with new values
 */
function updateForm(webcamInfo) {
    var webcamFormObj = $('#webcam_form');

    webcamFormObj.find('#id').val(webcamInfo['id']);
    webcamFormObj.find('#url').val(webcamInfo['url']);
    webcamFormObj.find('#title').val(webcamInfo['title'].defilter());
    webcamFormObj.find('#refresh').val(webcamInfo['refresh']);
    webcamFormObj.find('#action').val('UpdateWebcam');
}

/**
 * Add a webcam
 */
function addWebcam(form)
{
    var webcamTitle   = form.elements['title'].value,
        webcamUrl     = form.elements['url'].value,
        webcamRefresh = form.elements['refresh'].value;

    if (webcamTitle.blank()) {
        alert(jaws.gadgets.Webcam.incompleteWebcamFields);
        return false;
    }

    try {
        WebcamAjax.callAsync('NewWebcam', [webcamTitle, webcamUrl, webcamRefresh]);
    } catch(e) {
        alert(e);
    }
    cleanForm(form);
}

/**
 * Update a webcam
 */
function updateWebcam(form)
{
    var webcamId      = form.elements['id'].value,
        webcamTitle   = form.elements['title'].value,
        webcamUrl     = form.elements['url'].value,
        webcamRefresh = form.elements['refresh'].value;

    WebcamAjax.callAsync('UpdateWebcam', [webcamId, webcamTitle, webcamUrl, webcamRefresh]);
    cleanForm(form);
}

/**
 * Submit the button
 */
function submitForm(form)
{
    if (form.elements['action'].value == 'AddWebcam') {
        addWebcam(form);
    } else {
        updateWebcam(form);
    }
}

/**
 * Delete a webcam
 */
function deleteWebcam(id)
{
    WebcamAjax.callAsync('DeleteWebcam', [id]);
    cleanForm(document.getElementById('webcam_form'));
}

/**
 * Edit a webcam
 */
function editWebcam(id)
{
    WebcamAjax.callAsync('GetWebcam', [id]);
}

/**
 * Update the properties
 */
function updateProperties(form)
{
    var limitRandom = form.elements['limit_random'].value;
    WebcamAjax.callAsync('UpdateProperties', [limitRandom]);
}

$(document).ready(function() {
    initDataGrid('webcam_datagrid', WebcamAjax);
});

var WebcamAjax = new JawsAjax('Webcam', WebcamCallback);

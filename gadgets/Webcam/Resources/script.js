/**
 * Webcam Javascript actions
 *
 * @category   Ajax
 * @package    Webcam
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2005-2014 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
/**
 * Use async mode, create Callback
 */
var WebcamCallback = {
    NewWebcam: function(response) {
        if (response[0]['type'] == 'response_notice') {
            $('#webcam_datagrid')[0].addItem();
            $('#webcam_datagrid')[0].setCurrentPage(0);
            getDG();
        }
        WebcamAjax.showResponse(response);
    },

    DeleteWebcam: function(response) {
        if (response[0]['type'] == 'response_notice') {
            $('#webcam_datagrid')[0].deleteItem();
            getDG();
        }
        WebcamAjax.showResponse(response);
    },
    
    UpdateWebcam: function(response) {
        if (response[0]['type'] == 'response_notice') {
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
 *
 */
function cleanForm(form) 
{
    form[0].reset();
    form.find('#action').val('AddWebcam');
}

/**
 * Update form with new values
 *
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
    var webcamTitle   = form.find('#title').val(),
        webcamUrl     = form.find('#url').val(),
        webcamRefresh = form.find('#refresh').val();

    if (webcamTitle.blank()) {
        alert(incompleteWebcamFields);
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
    var webcamId      = form.find('#id').val(),
        webcamTitle   = form.find('#title').val(),
        webcamUrl     = form.find('#url').val(),
        webcamRefresh = form.find('#refresh').val();

    WebcamAjax.callAsync('UpdateWebcam', [webcamId, webcamTitle, webcamUrl, webcamRefresh]);
    cleanForm(form);
}

/**
 * Submit the button
 */
function submitForm(form)
{
    if (form.find('#action').val() == 'AddWebcam') {
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
    cleanForm($('#webcam_form'));
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
 *
 */
function updateProperties(form)
{
    var limitRandom = form.find('#limit_random').val();
    WebcamAjax.callAsync('UpdateProperties', [limitRandom]);
}

var WebcamAjax = new JawsAjax('Webcam', WebcamCallback);

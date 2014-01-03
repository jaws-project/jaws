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
            $('webcam_datagrid').addItem();
            $('webcam_datagrid').setCurrentPage(0);
            getDG();
        }
        showResponse(response);
    },

    DeleteWebcam: function(response) {
        if (response[0]['type'] == 'response_notice') {
            $('webcam_datagrid').deleteItem();          
            getDG();
        }
        showResponse(response);
    },
    
    UpdateWebcam: function(response) {
        if (response[0]['type'] == 'response_notice') {
            getDG();
        }
        showResponse(response);
    },

    GetWebcam: function(response) {
        updateForm(response);
    },

    UpdateProperties: function(response) {
        showResponse(response);
    }
}

/**
 * Clean the form
 *
 */
function cleanForm(form) 
{
    form.reset();
    form.elements['action'].value = 'AddWebcam';
}

/**
 * Update form with new values
 *
 */
function updateForm(webcamInfo) 
{
    $('webcam_form').elements['id'].value       = webcamInfo['id'];
    $('webcam_form').elements['url'].value      = webcamInfo['url'];
    $('webcam_form').elements['title'].value    = webcamInfo['title'].defilter();
    $('webcam_form').elements['refresh'].value  = webcamInfo['refresh'];
    $('webcam_form').elements['action'].value   = 'UpdateWebcam';
}

/**
 * Add a webcam
 */
function addWebcam(form)
{
    var webcamTitle   = form.elements['title'].value;
    var webcamUrl     = form.elements['url'].value;
    var webcamRefresh = form.elements['refresh'].value;

    if (webcamTitle.blank()) {
        alert(incompleteWebcamFields);
        return false;
    }

    try {
        WebcamAjax.callAsync('NewWebcam', webcamTitle, webcamUrl, webcamRefresh);
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
    var webcamId      = form.elements['id'].value;
    var webcamTitle   = form.elements['title'].value;
    var webcamUrl     = form.elements['url'].value;
    var webcamRefresh = form.elements['refresh'].value;
    
    WebcamAjax.callAsync('UpdateWebcam', webcamId, webcamTitle, webcamUrl, webcamRefresh);
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
    WebcamAjax.callAsync('DeleteWebcam', id);
    cleanForm($('webcam_form'));
}

/**
 * Edit a webcam
 */
function editWebcam(id)
{
    WebcamAjax.callAsync('GetWebcam', id);
}

/**
 * Update the properties
 *
 */
function updateProperties(form)
{
    var limitRandom = form.elements['limit_random'].value;
    WebcamAjax.callAsync('UpdateProperties', limitRandom);
}

var WebcamAjax = new JawsAjax('Webcam', WebcamCallback);

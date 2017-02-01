/**
 * Directory Javascript actions
 *
 * @category    Ajax
 * @package     Directory
 */

/**
 * Use async mode, create Callback
 */
var DirectoryCallback = {
    DeleteFile: function(response) {
        if (response.type === 'alert-success') {
            stopAction();
        }
        DirectoryAjax.showResponse(response);
    }
};

/**
 * stop Action
 */
function stopAction()
{
    $('#fileUIArea').hide();
    setEditorValue('#description', '');
    $('#file_form #file_link').html('');
    $('#file_form #title').val('');
    $('#file_form #tags').val('');
    $('#file_form #published').prop('checked', '');

    $('#file_form #id').val(0);
    $('#file_form #parent').val(0);

}

/**
 * Submits file data to create or update
 */
function deleteFile(id) {
    if (confirm(jaws.gadgets.Directory.confirmDelete)) {
        DirectoryAjax.callAsync('DeleteFile', {fileId: id});
    }
}

/**
 * display new file
 */
function newFile()
{
    $('#fileUIArea').show();
    $('html, body').animate({
        scrollTop: $("#fileUIArea").offset().top
    }, 1000);
}

/**
 * display edit file UI
 */
function editFile(id, parent)
{
    $('#fileUIArea').show();
    $('#file_form #id').val(id);
    $('#file_form #parent').val(parent);
    $('html, body').animate({
        scrollTop: $("#fileUIArea").offset().top
    }, 1000);

    var fileInfo = DirectoryAjax.callSync('GetFile', {id: id});
    console.log(fileInfo);
    $('#file_form #title').val(fileInfo['title']);
    setEditorValue('#description', fileInfo['description']);
    $('#file_form #tags').val(fileInfo['tags']);
    $('#file_form #published').prop('checked', fileInfo['published']? 'checked' : '');
}

$(document).ready(function() {
    switch (jaws.core.mainAction) {
        case 'Directory':
            initDatePicker('filter_from_date');
            initDatePicker('filter_to_date');
            break;
    }
});

var DirectoryAjax = new JawsAjax('Directory', DirectoryCallback, 'index.php');
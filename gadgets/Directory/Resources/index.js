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
    $('#file_form #id').val(0);
    $('#file_form #parent').val(0);
    $('#file_form #published').prop('checked', '');
}

/**
 * Submits file data to create or update
 */
function deleteFile(id) {
    if (confirm(jaws.Directory.Defines.confirmDelete)) {
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

// Define the data to be displayed in the repeater.
function directoryDataSource(options, callback)
{
    options.offset = options.pageIndex*options.pageSize;
    // define the columns for the grid
    var columns = [
        {
            'label': 'TITLE',
            'property': 'title',
            'sortable': false
        }
    ];

    DirectoryAjax.callAsync(
        'GetDirectory', {},
        function(response, status) {
            var dataSource = {};
            if (response['type'] == 'alert-success') {
                $.each(response['data'].records, function(key, file) {
                    response['data'].records[key].name = file.title;
                });
                // processing end item index of page
                options.end = options.offset + options.pageSize;
                options.end = (options.end > response['data'].total)? response['data'].total : options.end;
                dataSource = {
                    'page': options.pageIndex,
                    'pages': Math.ceil(response['data'].total/options.pageSize),
                    'count': response['data'].total,
                    'start': options.offset + 1,
                    'end':   options.end,
                    'columns': columns,
                    'items': response['data'].records
                };
            } else {
                dataSource = {
                    'page': 0,
                    'pages': 0,
                    'count': 0,
                    'start': 0,
                    'end':   0,
                    'columns': columns,
                    'items': {}
                };
            }
            // pass the datasource back to the repeater
            callback(dataSource);
            DirectoryAjax.showResponse(response);
        }
    );

}

/**
 * initiate contacts datagrid
 */
function initiateDirectoryDG()
{
    var repeater = $('#dirExplorer');
    repeater.repeater({
        defaultView: 'thumbnail',
        dataSource: directoryDataSource,
        staticHeight: 600,
        thumbnail_selectable: true,
        list_direction: $('.repeater-canvas').css('direction'),
    });
    $('#dirExplorer').on('selected.fu.repeaterThumbnail', function () {
        var urlParams = $.unserialize(window.location.search);
        var selectedFile = $('#dirExplorer').repeater('thumbnail_getSelectedItems')[0].data('item_data');

        if (top.tinymce) {
            top.tinymce.activeEditor.windowManager.getParams().oninsert(selectedFile.url);
            top.tinyMCE.activeEditor.windowManager.close();
        } else if (opener.CKEDITOR) {
            window.opener.CKEDITOR.tools.callFunction(urlParams.CKEditorFuncNum, selectedFile.url);
            close();
        } else if (opener.the_textarea) {
            opener.insertTags(opener.the_textarea, selectedFile.url, '', '');
            close();
        }

    });

}

$(document).ready(function() {
    switch (jaws.Defines.mainAction) {
        case 'Directory':
            initDatePicker('filter_from_date');
            initDatePicker('filter_to_date');
            break;

        case 'DirExplorer':
            initiateDirectoryDG();
            break;
    }
});

var DirectoryAjax = new JawsAjax('Directory', DirectoryCallback, 'index.php');
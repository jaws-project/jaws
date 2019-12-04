/**
 * Files Javascript actions
 *
 * @category   Ajax
 * @package    Files
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2019-2020 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
function Jaws_Gadget_Files() { return {
    // ASync callback method
    AjaxCallback : {
    },

    /**
     * add attachment interface then raise click event
     */
    extraFile: function(element)
    {
        let lastInput = $(element).parents().eq(1).find('.new_files>div').last().find('input').get(0);
        if (!lastInput || lastInput.files.length > 0) {
            $(element).parents().eq(1).children('.new_files').append(
                $(element).parents().eq(2).find('template').first().html()
            );
            lastInput = $(element).parents().eq(1).find('.new_files>div').last().find('input').get(0);
        }

        $(lastInput).click();
    },

    /**
     * remove file
     */
    removeFile: function(element, newfile = true)
    {
        // count of current files in the list
        let filesCount = $(element).parents().eq(3).children().length;

        if (newfile) {
            // find file index
            let fileIndex = $(element).parents().eq(3).children().index($(element).parents().eq(2)) - 1;

            let fileElement = $(element).parents().eq(4).find('input').get(0);
            let fileList = new DataTransfer();
            for(let i = 0; i < fileElement.files.length; i++) {
                if (i != fileIndex) {
                    fileList.items.add(fileElement.files[i]);
                }
            }
            fileElement.files = fileList.files;

            filesCount--;
        }

        // show browse button
        $(element).parents().eq(6).find('.btn_browse').show();
        if (filesCount < 2) {
            // if removed file was last one remove other elements too
            $(element).parents().eq(4).remove();
        } else {
            // remove DOM element
            $(element).parents().eq(2).remove();
        }
    },

    /**
     * browse file
     */
    browseFile: function(fileInput)
    {
        // allow max file size
        let maxsize = Number($(fileInput).data('maxsize'));
        // allow max files count
        let maxcount = Number($(fileInput).data('maxcount'));
        // allow file extensions
        let extensions = $(fileInput).data('extensions') || '*';
        extensions = (extensions == '*')? [] : extensions.split(',');
        // show preview flag
        let preview = Boolean($(fileInput).data('preview'));

        let ulElement = $(fileInput).parent().find('ul').first();
        // clear file list elements
        ulElement.children(':visible').remove();

        let fileList = new DataTransfer();
        for (let file of fileInput.files) {
            let fileName = file.name;
            // file extension
            if (fileName.lastIndexOf('.') >= 0) {
                file.extension = fileName.substring(fileName.lastIndexOf('.') + 1);
            } else {
                file.extension = '';
            }

            // file size
            if (file.size > maxsize) {
                console.log('File size is to big: ' + file.name);
                continue;
            }

            // file extensions
            if (extensions.length > 0 && extensions.indexOf(file.extension) < 0) {
                console.log('File type not valid: ' + file.name);
                continue;
            }

            if (maxcount > 0) {
                let filesCount = $(fileInput).parents().eq(2).find('.file_details:visible').length + 1;
                if (filesCount >= maxcount) {
                    $(fileInput).parents().eq(2).find('.btn_browse').hide();
                    if (filesCount > maxcount) {
                        console.log('Files count exceeded!');
                        continue;
                    }
                }
            }

            fileList.items.add(file);
            ulElement.append(
                ulElement.children().first().clone(true).show()
            );

            let liElement = ulElement.children().last();
            liElement.find("[data-type='name']").html(file.name);
            liElement.find("[data-type='size']").html(file.size);

            // show preview
            if (preview) {
                switch (file.type.substring(0, file.type.indexOf('/'))) {
                    case 'image':
                        let fReader = new FileReader();
                        fReader.readAsDataURL(file);
                        fReader.onload = function (event) {
                            liElement.find("[data-type='preview']").show().html(
                                '<img src="'+event.target.result+'" alt="" width="128">'
                            );
                        }
                        break;

                    case 'audio':
                        break;

                    case 'video':
                        break;
                }
            }
        }

        // reset file-input element selected files
        fileInput.files = fileList.files;
    },

    /**
     * initialize gadget actions
     */
    init: function(mainGadget, mainAction)
    {
        //
    },

}};
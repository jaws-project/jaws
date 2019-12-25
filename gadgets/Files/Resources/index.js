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
    uploadMoreFiles: function(element)
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
        // max image dimension
        let dimension = $(fileInput).data('dimension').split(/\*|x|\,/);
        if (dimension.length == 2) {
            dimension = {
                width: Number(dimension[0]),
                height: Number(dimension[0])
            };
        } else {
            dimension = false;
        }
        // allow max files count
        let maxcount = Number(
            $(fileInput)
            .parents()
            .eq(2)
            .find('[data-initialize=fileuploader]')
            .first()
            .data('maxcount')
        );
        // allow file extensions
        let extensions = $(fileInput).data('extensions') || '*';
        extensions = (extensions == '*')? [] : extensions.split(',');
        // show preview flag
        let preview = Boolean($(fileInput).data('preview'));

        let ulElement = $(fileInput).parent().find('ul').first();
        // clear file list elements
        ulElement.children(':visible').remove();

        let fileList = new DataTransfer();

        // define setter/getter for new property prepared files
        Object.defineProperty(fileInput.files, 'prepared', {
            get() {
                return this._prepared | 0;
            },
            set(value) {
                this._prepared = value;
                if (this._prepared >= this.length) {
                    fileInput.files = fileList.files;
                }
            }
        });

        // preparing all selected files
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
                fileInput.files.prepared++;
                continue;
            }

            // file extensions
            if (extensions.length > 0 && extensions.indexOf(file.extension) < 0) {
                console.log('File type not valid: ' + file.name);
                fileInput.files.prepared++;
                continue;
            }

            if (maxcount > 0) {
                let filesCount = $(fileInput).parents().eq(2).find('.file_details:visible').length + 1;
                if (filesCount >= maxcount) {
                    $(fileInput).parents().eq(2).find('.btn_browse').hide();
                    if (filesCount > maxcount) {
                        console.log('Files count exceeded!');
                        fileInput.files.prepared++;
                        continue;
                    }
                }
            }

            ulElement.append(
                ulElement.children().first().clone(true).show()
            );

            let liElement = ulElement.children().last();
            liElement.find("[data-type='name']").html(file.name);
            liElement.find("[data-type='size']").html(file.size);

            switch (file.type.substring(0, file.type.indexOf('/'))) {
                case 'image':
                    let fReader = new FileReader();
                    fReader.onload = function (event) {
                        let img = new Image();
                        img.onload = function() {
                            let canvas = document.createElement("canvas");
                            let ctx = canvas.getContext("2d");
                            // resize image dimension
                            let width = img.width;
                            let height = img.height;
                            if (dimension) {
                                if (width > height) {
                                    height *= dimension.width / width;
                                    width = dimension.width;
                                } else {
                                    width *= dimension.height / height;
                                    height = dimension.height;
                                }
                            }

                            canvas.width = width;
                            canvas.height = height;
                            ctx.drawImage(img, 0, 0, width, height);

                            let dataurl = canvas.toDataURL(file.type);
                            canvas.toBlob(
                                function(blob) {
                                    fileList.items.add(new File(
                                        [blob],
                                        file.name,
                                        {
                                            'type': file.type,
                                            'lastModified': file.lastModified
                                        }
                                    ));
                                    fileInput.files.prepared++;
                                },
                                file.type
                            );

                            // show preview
                            if (preview) {
                                liElement.find("[data-type='preview']").show().html(
                                    '<img src="'+dataurl+'" alt="" width="128">'
                                );
                            }

                        }
                        img.src = event.target.result;
                    }
                    fReader.readAsDataURL(file);
                    break;

                case 'audio':
                    fileList.items.add(file);
                    fileInput.files.prepared++;
                    break;

                case 'video':
                    fileList.items.add(file);
                    fileInput.files.prepared++;
                    break;

                default:
                    fileList.items.add(file);
                    fileInput.files.prepared++;
            }
        }

    },

    /**
     * initialize gadget actions
     */
    init: function(mainGadget, mainAction)
    {
        // initialize upload files configuration
        $('[data-initialize=fileuploader]').each(
            function() {
                let maxcount = Number($(this).data('maxcount'));
                let filesCount = $(this).parents().eq(1).find('.file_details:visible').length;
                if (maxcount > 0 && filesCount >= maxcount) {
                    $(this).parent().hide();
                }
            }
        );
    },

}};
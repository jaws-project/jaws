/**
 * Files Javascript actions
 *
 * @category   Ajax
 * @package    Files
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2019-2021 Jaws Development Group
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
        let lastInput = $(element).parents()
            .eq(1).find('.new_files>div')
            .last().find('input[type="file"]')
            .get(0);

        if (!lastInput || lastInput.files.length > 0) {
            $(element).parents().eq(1).children('.new_files').append(
                $(element).parents().eq(2).find('template').first().html()
            );
            lastInput = $(element).parents()
                .eq(1).find('.new_files>div')
                .last().find('input[type="file"]')
                .get(0);
        }

        $(lastInput).click();
    },

    /**
     * remove file
     */
    removeFile: function(element)
    {
        // count of current files in the list
        let filesCount = $(element).parents().eq(3).children().length;

        if ($(element).parents('.new_files').length == 1 ) {
            // find file index
            let fileIndex = $(element).parents().eq(3).children().index($(element).parents().eq(2)) - 1;

            let fileElement = $(element).parents().eq(4).find('input[type="file"]').get(0);
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

        try {
            // pre-check file size/extension/count

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
                    throw 'File size is to big: ' + file.name;
                }

                // file extensions
                if (extensions.length > 0 && extensions.indexOf(file.extension) < 0) {
                    throw 'File type not valid: ' + file.name;
                }
            }

            // max files count
            if (maxcount > 0) {
                let filesCount = $(fileInput).parents().eq(2).find('.file_details:visible').length;
                if ((fileInput.files.length + filesCount) > maxcount) {
                    throw 'Files count exceeded!';
                } else if ((fileInput.files.length + filesCount) == maxcount) {
                    $(fileInput).parents().eq(2).find('.btn_browse').hide();
                }
            }

        } catch(error) {
            // remove dom elements
            $(fileInput).parent().remove();
            this.gadget.message.show(
                {
                    'text': error,
                    'type': 'alert-danger'
                }
            );

            return;
        }

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

        // client side processing selected files
        for (let file of fileInput.files) {
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
     * Display reference files interface
     */
    displayReferenceFiles: function($tpl, $interface, $options = [])
    {
        this.gadget.ajax.callAsync(
            'displayReferenceFiles',
            $interface,
            function(response, status) {
                if (response['type'] == 'alert-success') {
                    var regex = new RegExp(
                        '\{\{(' + Object.keys(response['data'].files[0]).join('|') + ')\}\}',
                        'g'
                    );

                    $.each(response['data'].files, function (index, file) {
                        let tplStr = response['data'].template.replace(regex, (m, $1) => file[$1] || m);
                        $tpl.append(tplStr.replace(/{{lbl_file}}/g, $options.labels.title));
                    });
                }
            }
        )
    },

    /**
     * Get upload reference files interface
     */
    loadReferenceFiles: function($tpl, $interface, $options = [])
    {
        if (!$interface.hasOwnProperty('input_reference')) {
            $interface['input_reference'] = $interface['reference'];
        }

        let inputIndexName = $interface['action'].toLowerCase() + '_' +
            $interface['input_reference'] + '_' + $interface['type'] + '[]';

        let $fileInput = $tpl.find('template').contents().find('input[type="file"]').last();
        $fileInput.attr('name', 'new_files_' + inputIndexName);
        let preview = Boolean($fileInput.data('preview'));

        if ($interface['reference'] != 0) {
            this.gadget.ajax.callAsync(
                'loadReferenceFiles',
                $interface,
                function(response, status) {
                    if (response['type'] == 'alert-success') {
                        $.each(
                            response['data'],
                            function (index, file) {
                                let ulElement = $tpl.find('.old_files ul').first();
                                ulElement.append(
                                    $tpl.find('template').contents().find('.file_details').parent().html()
                                );
                                let liElement = ulElement.children().last();
                                liElement.find('input').attr('name', 'old_files_' + inputIndexName).val(file.id);
                                liElement.find("[data-type='name']").html(
                                    '<a href="'+file.fileurl +
                                    '" target="_blank">' + file.title +
                                    '</a>'
                                );
                                liElement.find("[data-type='size']").html(file.filesize);
                                // show preview
                                if (preview) {
                                    liElement.find("[data-type='preview']").show().html(
                                        '<a href="'+file.fileurl+
                                        '" target="_blank"><img src="'+
                                        file.fileurl+
                                        '" alt="" width="128"></a>'
                                    );
                                }
                                liElement.show();
                            }
                        );
                    }

                    //initialize file uploader
                    this.initFileUploader($tpl.find('[data-initialize=fileuploader]').first());
                },
                {
                    'baseScript': 'index.php'
                }
            );
        }
    },

    /**
     * Preparing FormData object of old/new files 
     */
    prepareFormDataFiles: function($tpl, formData)
    {
        let filesCount = 0;
        // old files
        $tpl.find('.old_files input[type="hidden"]').each(
            function (key, el) {
                filesCount++;
                formData.append(el.name, el.value);
            }
        );
        // new files
        $tpl.find('.new_files input[type="file"]').each(
            function (key, elFile) {
                $.each(
                    elFile.files,
                    function(key, file) {
                        filesCount++;
                        formData.append(elFile.name, file);
                    }
                );
            }
        );

        return filesCount;
    },

    /**
     * initialize file uploader
     */
    initFileUploader: function($fileuploader)
    {
        let maxcount = Number($fileuploader.data('maxcount'));
        let filesCount = $fileuploader.parents().eq(1).find('.file_details').length;
        if (maxcount > 0 && filesCount >= maxcount) {
            $fileuploader.parent().hide();
        } else {
            $fileuploader.parent().show();
        }
    },

    /**
     * initialize gadget actions
     */
    init: function(mainGadget, mainAction)
    {
        // initialize upload files configuration
        $('[data-initialize=fileuploader]').each(
            $.proxy(
                function(index, elFileUloader) {
                    this.initFileUploader($(elFileUloader));
                },
                this
            )
        );
    },

}};
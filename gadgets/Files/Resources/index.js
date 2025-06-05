/**
 * Files Javascript actions
 *
 * @category   Ajax
 * @package    Files
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright   2019-2024 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
function Jaws_Gadget_Files() { return {
    // ASync callback method
    AjaxCallback : {
    },

    async resizeImage(file, { type = file.type, dimension = false, quality = undefined})
    {
        // Get as image data
        const imageBitmap = await createImageBitmap(file);
        // create to canvas
        const canvas = document.createElement('canvas');

        if (dimension) {
            let ratio = Math.max(dimension.width, dimension.height)/Math.max(imageBitmap.width, imageBitmap.height);
            canvas.width = Math.round(ratio * imageBitmap.width);
            canvas.height = Math.round(ratio * imageBitmap.height);
        } else {
            canvas.width = imageBitmap.width;
            canvas.height = imageBitmap.height;
        }

        // Draw to canvas
        const ctx = canvas.getContext('2d');
        ctx.drawImage(imageBitmap, 0, 0, canvas.width, canvas.height);

        // convert to Blob
        const blob = await new Promise((resolve) =>
            canvas.toBlob(resolve, type, quality)
        );

        // convert Blob to File
        return new File([blob], file.name, {
            type: blob.type,
        });
    },

    /**
     * file browse dialog 
     */
    browseFile: function(element)
    {
        let lastInput = $(element).closest('.files-interface')
            .find('input[type="file"].files-interface-input')
            .last()
            .get(0);

        if (!lastInput || lastInput.files.length > 0) {
            $(element).closest('.files-interface').append(
                $(element).closest('.files-interface').find('template').contents().filter('input.files-interface-input').prop('outerHTML')
            );
            lastInput = $(element).closest('.files-interface')
                .find('input[type="file"].files-interface-input')
                .last()
                .get(0);
        }

        $(lastInput).click();
    },

    /**
     * remove file
     */
    removeFile: function(element)
    {
        let $filesInterface = $(element).closest('.files-interface');
        let inputFileUID = $(element).closest('.files-interface-item').data('input-file-uid');
        if (inputFileUID) {
            let inputFileElement = $filesInterface.find('input[data-input-file-uid="' + inputFileUID + '"]').get(0);
            // find file index
            let itemIndex = $filesInterface.find('.files-interface-item[data-input-file-uid="' + inputFileUID + '"]')
                .index($(element).closest('.files-interface-item'));

            let dataTransfer = new DataTransfer();
            Object.values(inputFileElement.files).forEach(function(file, index) {
                if (index != itemIndex) {
                    dataTransfer.items.add(file);
                }
            });
            inputFileElement.files = dataTransfer.files;
        }

        // enable file browser, before remove item
        $filesInterface.find('[data-initialize="fileuploader"]').prop('disabled', false);
        // remove DOM element
        $(element).closest('.files-interface-item').remove();
        // update files count
        $filesInterface.find('[data-role="count"]').html(this.t('files_count', [$filesInterface.find('.files-interface-item').length]));
    },

    /**
     * select file
     */
    async selectFile(inputFileElement)
    {
        let $filesInterface = $(inputFileElement).closest('.files-interface');
        // max file size
        let maxsize = Number($(inputFileElement).data('maxsize'));

        // image dimension
        let dimension = $(inputFileElement).data('dimension').split(/\*|x|\,/);
        if (dimension.length == 2) {
            dimension = {
                width: Number(dimension[0]),
                height: Number(dimension[1])
            };
        } else {
            dimension = false;
        }

        // max files count
        let maxcount = Number(
            $filesInterface
            .find('[data-initialize=fileuploader]')
            .first()
            .data('maxcount')
        );

        // file extensions
        let extensions = $(inputFileElement).data('extensions') || '*';
        extensions = (extensions == '*')? [] : extensions.split(',');
        // preview flag
        let preview = Boolean($(inputFileElement).data('preview'));

        try {
            // pre-check file size/extension/count
            for (let file of inputFileElement.files) {
                let fileName = file.name;
                // file extension
                if (fileName.lastIndexOf('.') >= 0) {
                    file.extension = fileName.substring(fileName.lastIndexOf('.') + 1);
                } else {
                    file.extension = '';
                }
                if (extensions.length > 0 && extensions.indexOf(file.extension.toLowerCase()) < 0) {
                    throw Jaws.t('error_upload_invalid_format', [file.name]);
                }
            }

            // max files count
            if (maxcount > 0) {
                let filesCount = $filesInterface.find('.files-interface-item').length;
                filesCount += inputFileElement.files.length;
                if (filesCount > maxcount) {
                    throw Jaws.t('error_upload_max_count');
                } else if (filesCount == maxcount) {
                    // disable file browser
                    $filesInterface.find('[data-initialize="fileuploader"]').prop('disabled', true);
                }
            }
        } catch(error) {
            // remove DOM elements
            $(inputFileElement).remove();
            this.gadget.message.show(
                {
                    'text': error,
                    'type': 'alert-danger'
                }
            );
            return;
        }

        let dataTransfer = new DataTransfer();
        let $container = $filesInterface.find('.files-interface-items');

        let inputFileUID = Date.now().toString() + Math.floor(Math.random()*100000).toString();
        $(inputFileElement).attr('data-input-file-uid', inputFileUID);
        // client side processing selected files
        for (let file of inputFileElement.files) {
            $container.append(
                $filesInterface.find('template').contents().filter('.files-interface-item').prop('outerHTML')
            );

            let $item = $container.children('.files-interface-item').last();
            $item.find('[data-type="name"]').html(file.name);
            $item.attr('data-input-file-uid', inputFileUID);

            try {
                switch (file.type.substring(0, file.type.indexOf('/'))) {
                    case 'image':
                        const fReader = new FileReader();
                        fReader.onload = $.proxy(async function (event) {
                            $item.find('[data-type="image"]').attr('src', event.target.result);
                        }, this.gadget);

                        // resize the image
                        if (dimension && !['image/svg'].some(keyword => file.type.includes(keyword))) {
                            file = await this.resizeImage(file, {
                                'type': file.type,
                                'dimension': dimension
                            });
                        }

                        $item.find('[data-type="size"]').html(Jaws.filters.apply(['formatNumber:filesize'], file.size));
                        fReader.readAsDataURL(file);
                        break;

                    default:
                        //do nothing
                }

                // check file size
                if (maxsize > 0 && file.size > maxsize) {
                    throw Jaws.t('error_upload_exceeded_size', [file.name]);
                }

                // add processed file to new files list
                dataTransfer.items.add(file);

            } catch(error) {
                // remove item element
                $item.remove();

                // enable file browser
                $filesInterface.find('[data-initialize="fileuploader"]').prop('disabled', false);

                this.gadget.message.show(
                    {
                        'text': error,
                        'type': 'alert-danger'
                    }
                );
            }
        }

        // update files count
        $filesInterface.find('[data-role="count"]').html(this.t('files_count', [$filesInterface.find('.files-interface-item').length]));

        // show select files preview/details
        //$filesInterface.find('.accordion-collapse').collapse('show');

        // set value of the file input to new files list
        inputFileElement.files = dataTransfer.files;
    },

    /**
     * get reference files interface
     */
    getReferenceFiles: function(interface, callback)
    {
        let files = [];
        this.gadget.ajax.call(
            'files',
            interface,
            function(response, status) {
                if (response['type'] == 'alert-success') {
                    files = response['data'];
                }
                callback(files);
            }
        )
    },

    /**
     * Display reference files interface
     */
    displayReferenceFiles: function($tpl, interface, options = [])
    {
        this.gadget.ajax.call(
            'displayReferenceFiles',
            interface,
            function(response, status) {
                if (response['type'] == 'alert-success') {
                    var regex = new RegExp(
                        '\{\{(' + Object.keys(response['data'].files[0]).join('|') + ')\}\}',
                        'g'
                    );

                    $.each(response['data'].files, function (index, file) {
                        let tplStr = response['data'].template.replace(regex, (m, $1) => file[$1] || m);
                        $tpl.append(tplStr.replace(/{{lbl_file}}/g, options.labels.title));
                    });
                }
            }
        )
    },

    /**
     * Get upload reference files interface
     */
    loadReferenceFiles: function($tpl, interface, options = {})
    {
        // merge input options with default options
        options = Object.assign(
            {
                'labels': {
                    'title': '',
                    'browse': '',
                    'remove': '',
                    'description': '',
                },
                'modalsize': '',
                'filetype': 0,
                'maxsize': 33554432,
                'extensions': '',
                'dimension': '',
                'maxcount': 0,
                'preview': true
            },
            options
        );

        let widthClasses = {
            '': 'col-12',
            'modal-sm': 'col-12',
            'modal-sm': 'col-12',
            'modal-lg': 'col-12 col-md-6',
            'modal-xl': 'col-12 col-md-6',
        };
        $tpl.find('[data-grid-role="col"]').toggleClass(widthClasses[options.modalsize], true);

        if (!interface.hasOwnProperty('input_reference')) {
            interface['input_reference'] = interface['reference'];
        }

        let inputIndexName = interface['action'].toLowerCase() + '_' +
            interface['input_reference'] + '_' + interface['type'] + '[]';

        let $fileInput = $tpl.find('template').contents().filter('input.files-interface-input');
        $fileInput.attr('name', 'new_files_' + inputIndexName);
        $fileInput.attr('data-maxsize', options['maxsize']);
        // preview
        $fileInput.attr('data-preview', options['preview']);
        $fileInput.attr('data-extensions', options['extensions']);
        $fileInput.attr('data-dimension', options['dimension']);
        // accept
        let accept = '*';
        options['extensions'] = String(options['extensions']).split(',').map(function(ext) {return ext.replace(/^\.+/g, '');});
        if (options['extensions'].length) {
            accept = '.' + options['extensions'].join(',.');
        } else {
            switch (options['filetype']) {
                case 2:
                    accept = 'text/*';
                    break;
                case 3:
                    accept = 'image/*';
                    break;
                case 4:
                    accept = 'audio/*';
                    break;
                case 5:
                    accept = 'video/*';
                    break;
                case 6:
                    accept = 'font/*';
                    break;
                case 7:
                    accept = 'archive/*';
                    break;
            }
        }
        $fileInput.attr('accept', accept);

        // set remove label
        $tpl.find('template').contents().find('[data-label="remove"]').attr('title', options['labels']['remove']);

        // empty items
        $tpl.find('.files-interface-item').remove();
        $tpl.find('.files-interface input[type="file"]').remove();
        let $container = $tpl.find('.files-interface-items');

        // initiate accordion
        let uid = interface['action'].toLowerCase() + '-' +
            interface['input_reference'] + '-' + interface['type'];

        // initiate accordion
        $tpl.find('[data-accordion="id"]').attr('id', `files-interface-accordion-${uid}`);
        $tpl.find('[data-accordion="item-id"]').attr('data-bs-target', `#files-interface-accordion-body-${uid}`);
        $tpl.find('[data-accordion="item-body"]').attr('data-bs-parent', `#files-interface-accordion-${uid}`);
        $tpl.find('[data-accordion="item-body"]').attr('id', `files-interface-accordion-body-${uid}`);
        $tpl.find('.accordion-collapse').collapse('hide');

        // set label
        if (options['labels']['title']) {
            $tpl.find("[data-label='title']").html(options['labels']['title']);
        }
        // set description
        if (options['labels']['description']) {
            let $tooltip = $tpl.find("[data-label='description']")
                .removeClass('d-none')
                .find("[data-bs-toggle='tooltip']")
                .attr('title', options['labels']['description']);
            // re-initiate tool tip 
            bootstrap.Tooltip.getOrCreateInstance($tooltip.get(0)).dispose();
            new bootstrap.Tooltip($tooltip.get(0), {});
        }
        // set browse label
        if (options['labels']['browse']) {
            $tpl.find("[data-label='browse']").attr('title', options['labels']['browse']);
        }
        // preview?
        options['preview'] = Boolean(options['preview'] || $fileInput.data('preview'));
        // min-count
        $tpl.find("[data-mincount]").data('mincount', options['mincount']);
        // max-count
        $tpl.find("[data-maxcount]").data('maxcount', options['maxcount']);

        if (interface['reference'] == 0) {
            this.initFileUploader($tpl.find('[data-initialize=fileuploader]').first());
            return;
        }

        this.gadget.ajax.call(
            'loadReferenceFiles',
            {
                'interface': interface,
                'options': options
            },
            function(response, status) {
                if (response['type'] == 'alert-success') {
                    $.each(
                        response['data'],
                        function (index, file) {
                            $container.append(
                                $tpl.find('.files-interface').find('template').contents().filter('.files-interface-item').prop('outerHTML')
                            );
                            let $item = $container.children('.files-interface-item').last();
                            $item.find('input').attr('name', 'old_files_' + inputIndexName).val(file.id);
                            $item.find("[data-type='name']").html(
                                '<a class="text-nowrap" href="'+file.fileurl +
                                '" target="_blank">' + file.title +
                                '</a>'
                            );
                            $item.find("[data-type='size']").html(Jaws.filters.apply(['formatNumber:filesize'], file.filesize));
                            // show preview
                            // FIXME! check file is an image
                            if (options['preview']) {
                                $item.find('[data-type="image"]').attr('src', file.fileurl);
                            }
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

    },

    /**
     * Preparing FormData object of old/new files 
     */
    prepareFormDataFiles: function($tpl, formData)
    {
        let filesCount = 0;
        let $fileuploader = $tpl.find('[data-initialize=fileuploader]').first();
        let mincount = Number($fileuploader.data('mincount'));
        let maxcount = Number($fileuploader.data('maxcount'));

        // is upload interface
        formData.append('files_upload_interface', 1);
        // old files
        $tpl.find('.files-interface input[type="hidden"][data-role="file"]').each(function (key, el) {
            if (el.value !== undefined && el.value !== null && el.value !== '') {
                filesCount++;
                formData.append(el.name, el.value);
            }
        });

        // new files
        $tpl.find('.files-interface input[type="file"]').each(function (key, elFile) {
            $.each(elFile.files, function(key, file) {
                filesCount++;
                formData.append(elFile.name, file);
            });
        });

        if ((filesCount < mincount) || (maxcount && filesCount > maxcount)) {
            // inform caller there is an issue
            filesCount = -1;

            // reset/empty FormData
            let entry;
            while (entry = formData.entries().next().value) {
                formData.delete(entry[0]);
            }

            this.gadget.message.show({
                'text': Jaws.t('error_upload_min_count'),
                'type': 'alert-danger'
            });
        }

        return filesCount;
    },

    /**
     * initialize file uploader
     */
    initFileUploader: function($fileuploader)
    {
        let $filesInterface = $fileuploader.closest('.files-interface');
        let maxcount = Number($fileuploader.data('maxcount'));
        let filesCount = $filesInterface.find('.files-interface-item').length;
        // update files count
        $filesInterface.find('[data-role="count"]').html(this.t('files_count', [filesCount]));
        if (maxcount > 0 && filesCount >= maxcount) {
            // disable file browser
            $filesInterface.find('[data-initialize="fileuploader"]').prop('disabled', true);
        } else {
            // enable file browser
            $filesInterface.find('[data-initialize="fileuploader"]').prop('disabled', false);
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
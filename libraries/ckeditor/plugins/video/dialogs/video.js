/**
 * @file
 * Written by Henri MEDOT <henri.medot[AT]absyx[DOT]fr>
 * http://www.absyx.fr
 */

(function(undefined) {
  'use strict';

  var trim = CKEDITOR.tools.trim;

  var unbind = function(video$) {
    video$.onloadedmetadata = video$.onerror = null;
  };

  var cache = {}, getMetadata = function(dialog, readyCallback) {
    var video$ = dialog.video.$;
    unbind(video$);
    var url = trim(dialog.getValueOf('info', 'src'));
    if (!url.length) {
      return false;
    }
    if (cache[url] !== undefined) {
      return cache[url];
    }
    video$.onloadedmetadata = video$.onerror = function() {
      unbind(video$);
      var w = video$.videoWidth;
      var h = video$.videoHeight;
      cache[url] = w && h ? {width: w, height: h} : false;
      readyCallback();
    };
    video$.src = url;
  };

  var clearPreview = function(dialog) {
    var video = dialog.video.setStyle('display', 'none');
    var video$ = video.$;
    unbind(video$);
    video$.src = '';
  };

  var updatePreview = function(dialog) {
    var metadata = getMetadata(dialog, function() {
      updatePreview(dialog);
    });
    var video = dialog.video;
    if (metadata) {
      dialog.commitContent(video);
      //var ratio = (100 * metadata.height / metadata.width).toFixed(5) + '%';
      video.setStyle('display', 'block').setStyle('width', '100%').setStyle('height', '100%');
    }
    else {
      video.setStyle('display', 'none');
    }
  };

  CKEDITOR.dialog.add('video', function(editor) {
    return {
      title: editor.lang.video.title,
      minWidth: 400,
      minHeight: 300,
      contents: [{
        id: 'info',
        label: editor.lang.common.generalTab,
        elements: [ {
            type: 'vbox',
            padding: 0,
            children: [ {
                type: 'hbox',
                widths: [ '280px', '110px' ],
                align: 'right',
                className: 'cke_dialog_video_url',
                children: [ {
                    id: 'src',
                    type: 'text',
                    label: editor.lang.common.url,
                    required: true,
                    onChange: function() {
                        updatePreview(this.getDialog());
                    },
                    validate: CKEDITOR.dialog.validate.notEmpty(editor.lang.video.emptySrc),
                    setup: function(element) {
                        this.setValue(element && element.getAttribute('src') || '');
                    },
                    commit: function(element) {
                        element.setAttribute('src', trim(this.getValue()));
                    }
                }, {
                    id: 'browse',
                    type: 'button',
                    filebrowser: 'info:src',
                    hidden: true,
                    style: 'display:inline-block;margin-top:14px;',
                    label: editor.lang.common.browseServer
                }]
            } ]
        }, {
            id: 'controls',
            type: 'checkbox',
            label: editor.lang.video.controls,
            'default': true,
            onChange: function() {
            var dialog = this.getDialog();
            if (!this.getValue()) {
                dialog.getContentElement('info', 'muted_looping_autoplay').setValue(true, true);
            }
            updatePreview(dialog);
            },
            setup: function(element) {
            this.setValue(!!(element && element.hasAttribute('controls')));
            },
            commit: function(element) {
            if (this.getValue()) {
                element.setAttribute('controls', 'controls');
            }
            else {
                element.removeAttribute('controls');
            }
            }
        }, {
            id: 'muted_looping_autoplay',
            type: 'checkbox',
            label: editor.lang.video.mutedLoopingAutoplay,
            onChange: function() {
                var dialog = this.getDialog();
                if (!this.getValue()) {
                    dialog.getContentElement('info', 'controls').setValue(true, true);
                }
                updatePreview(dialog);
            },
            setup: function(element) {
                this.setValue(!!(element && element.hasAttribute('autoplay')));
            },
            commit: function(element) {
                if (this.getValue()) {
                    element.setAttributes({autoplay: 'autoplay', loop: 'loop', muted: 'muted'});
                }
                else {
                    element.removeAttributes(['autoplay', 'loop', 'muted']);
                }
            }
        }, {
            type: 'vbox',
            padding: 0,
            children: [ {
                type: 'hbox',
                widths: [ '1%', '80px'],
                align: 'right',
                children: [ {
                    type: 'text',
                    id: 'txtWidth',
                    style: 'width:95px',
                    label: editor.lang.common.width,
                    setup: function(element) {
                        let size = '100%';
                        if (element && element.getStyle('width')) {
                            size = element.getStyle('width')
                        }
                        this.setValue(size);
                    },
                    commit: function( element ) {
                        element.setStyle('width', this.getValue());
                    }
                }, {
                    type: 'text',
                    id: 'txtHeight',
                    style: 'width:95px',
                    label: editor.lang.common.height,
                    setup: function(element) {
                        let size = '100%';
                        if (element && element.getStyle('height')) {
                            size = element.getStyle('height')
                        }
                        this.setValue(size);
                    },
                    commit: function( element ) {
                        element.setStyle('height', this.getValue());
                    }
                }]
            } ]
        }, {
            id: 'preview',
            type: 'html',
            html: '<label>' + editor.lang.video.preview + '</label><div class="cke_dialog_ui_labeled_content" style="background-color:#f8f8f8;border:1px solid #d1d1d1;height:22rem;"><video preload="metadata" style="width:100%;height:100%;"></video></div>'
        }]
      }],
      onLoad: function() {
        this.video = this.getContentElement('info', 'preview').getElement().getNext().getFirst();
        // Ensure browser does not ignore the muted attribute.
        this.video.$.addEventListener('loadedmetadata', function(e) {
          var video$ = e.target;
          video$.muted = video$.hasAttribute('muted');
        }, false);
      },
      onShow: function() {
        var element = this.getSelectedElement();
        if (element && element.data('cke-real-element-type') == 'video') {
          var realElement = editor.restoreRealElement(element);
          this.setupContent(realElement);
          updatePreview(this);
        }
      },
      onOk: function() {
        var dialog = this;
        var metadata = getMetadata(dialog, function() {
          dialog.definition.onOk.apply(dialog);
        });
        if (metadata) {
          var realElement = CKEDITOR.dom.element.createFromHtml('<cke:video></cke:video>', editor.document);
          realElement.setAttributes({
            preload: 'metadata',
            width: metadata.width,
            height: metadata.height
          });
          dialog.commitContent(realElement);
          var element = editor.createFakeElement(realElement, 'cke-video', 'video', false);
          element.$.src = CKEDITOR.tools.createImageData(metadata);
          editor.insertElement(element);
          dialog.hide();
          return;
        }
        if (metadata === false) {
          alert(editor.lang.video.invalidSrc);
        }
        return false;
      },
      onHide: function() {
        clearPreview(this);
      }
    };
  });

})();

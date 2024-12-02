/**
 * @file
 * Written by Henri MEDOT <henri.medot[AT]absyx[DOT]fr>
 * http://www.absyx.fr
 */

(function(undefined) {
  'use strict';

  var trim = CKEDITOR.tools.trim;

  var unbind = function(audio$) {
    audio$.onloadedmetadata = audio$.onerror = null;
  };

  var cache = {}, getMetadata = function(dialog, readyCallback) {
    var audio$ = dialog.audio.$;
    unbind(audio$);
    var url = trim(dialog.getValueOf('info', 'src'));
    if (!url.length) {
      return false;
    }
    if (cache[url] !== undefined) {
      return cache[url];
    }
    audio$.onloadedmetadata = audio$.onerror = function() {
      unbind(audio$);
      cache[url] = true;
      readyCallback();
    };
    audio$.src = url;
  };

  var clearPreview = function(dialog) {
    var audio = dialog.audio.setStyle('display', 'none');
    var audio$ = audio.$;
    unbind(audio$);
    audio$.src = '';
  };

  var updatePreview = function(dialog) {
    var metadata = getMetadata(dialog, function() {
      updatePreview(dialog);
    });
    var audio = dialog.audio;
    if (metadata) {
      dialog.commitContent(audio);
      audio.setStyle('display', 'block').setStyle('width', '100%').setStyle('height', '100%');
    }
    else {
      audio.setStyle('display', 'none');
    }
  };

  CKEDITOR.dialog.add('audio', function(editor) {
    return {
      title: editor.lang.audio.title,
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
                className: 'cke_dialog_audio_url',
                children: [ {
                    id: 'src',
                    type: 'text',
                    label: editor.lang.common.url,
                    required: true,
                    onChange: function() {
                        updatePreview(this.getDialog());
                    },
                    validate: CKEDITOR.dialog.validate.notEmpty(editor.lang.audio.emptySrc),
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
            label: editor.lang.audio.controls,
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
            label: editor.lang.audio.mutedLoopingAutoplay,
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
            type: 'text',
            id: 'txtWidth',
            style: 'width:95px',
            label: editor.lang.common.width,
            setup: function(element) {
                let size = '';
                if (element && element.getStyle('width')) {
                    size = element.getStyle('width')
                }
                this.setValue(size);
            },
            commit: function( element ) {
                element.setStyle('width', this.getValue());
            }
        },{
            id: 'preview',
            type: 'html',
            html: '<label>' + editor.lang.audio.preview + '</label><div class="cke_dialog_ui_labeled_content" style="background-color:#f8f8f8;border:1px solid #d1d1d1;height:5rem;"><audio preload="metadata" style="width:100%;height:100%;"></audio></div>'
        }]
      }],
      onLoad: function() {
        this.audio = this.getContentElement('info', 'preview').getElement().getNext().getFirst();
        // Ensure browser does not ignore the muted attribute.
        this.audio.$.addEventListener('loadedmetadata', function(e) {
          var audio$ = e.target;
          audio$.muted = audio$.hasAttribute('muted');
        }, false);
      },
      onShow: function() {
        var element = this.getSelectedElement();
        if (element && element.data('cke-real-element-type') == 'audio') {
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
          var realElement = CKEDITOR.dom.element.createFromHtml('<cke:audio></cke:audio>', editor.document);
          realElement.setAttributes({
            preload: 'metadata',
            width: metadata.width,
            height: metadata.height
          });
          dialog.commitContent(realElement);
          var element = editor.createFakeElement(realElement, 'cke-audio', 'audio', false);
          element.$.src = CKEDITOR.tools.createImageData(metadata);
          editor.insertElement(element);
          dialog.hide();
          return;
        }
        if (metadata === false) {
          alert(editor.lang.audio.invalidSrc);
        }
        return false;
      },
      onHide: function() {
        clearPreview(this);
      }
    };
  });

})();

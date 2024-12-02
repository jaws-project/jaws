/**
 * @file
 * Written by Henri MEDOT <henri.medot[AT]absyx[DOT]fr>
 * http://www.absyx.fr
 */

CKEDITOR.tools.createImageData = function(dimensions) {
  return 'data:image/svg+xml;charset=utf-8,' + encodeURIComponent('<svg xmlns="http://www.w3.org/2000/svg" width="' + dimensions.width + '" height="' + dimensions.height + '"></svg>');
};

CKEDITOR.plugins.add('audio', {
  requires: 'dialog,fakeobjects',
  lang: 'en,fr',
  icons: 'audio',
  hidpi: true,
  onLoad: function() {
    var url = CKEDITOR.getUrl(this.path + 'images/placeholder.png');
    CKEDITOR.addCss('img.cke-audio{background:#f8f8f8 url(' + url + ') center center no-repeat;outline:1px solid #ccc;outline-offset:-1px;min-width:192px;min-height:108px;max-width:100%;width:auto!important;height:auto!important;}');
  },
  init: function(editor) {
    editor.addCommand('audio', new CKEDITOR.dialogCommand('audio', {
      allowedContent: 'audio[autoplay,controls,height,loop,muted,preload,!src,width]'
    }));
    editor.ui.addButton('Audio', {
      label: editor.lang.audio.button,
      command: 'audio'
    });
    CKEDITOR.dialog.add('audio', this.path + 'dialogs/audio.js');
    editor.on('doubleclick', function(e) {
      var element = e.data.element;
      if (element && element.is('img') && !element.isReadOnly() && element.data('cke-real-element-type') == 'audio') {
        e.data.dialog = 'audio';
      }
    });
    if (editor.addMenuItems) {
      editor.addMenuGroup('audio', 11);
      editor.addMenuItems({
        audio: {
          label: editor.lang.audio.title,
          command: 'audio',
          group: 'audio'
        }
      });
    }
    if (editor.contextMenu) {
      editor.contextMenu.addListener(function(element) {
        if (element && element.is('img') && !element.isReadOnly() && element.data('cke-real-element-type') == 'audio') {
          return {audio: CKEDITOR.TRISTATE_OFF};
        }
      });
    }
    editor.filter.addElementCallback(function(element) {
      if (element.name == 'cke:audio') {
        return CKEDITOR.FILTER_SKIP_TREE;
      }
    });
    editor.lang.fakeobjects.audio = editor.lang.audio.button;
  },
  afterInit: function(editor) {
    editor.on('toHtml', function(e) {
      var html = e.data.dataValue;
      html = html.replace(/(<\/?)audio\b/gi, '$1cke:audio');
      e.data.dataValue = html;
    }, null, null, 1);
    var dataProcessor = editor.dataProcessor;
    var dataFilter = dataProcessor && dataProcessor.dataFilter;
    if (dataFilter) {
      dataFilter.addRules({
        elements: {
          'cke:audio': function(element) {
            var attributes = CKEDITOR.tools.extend({}, element.attributes);
            element = editor.createFakeParserElement(element, 'cke-audio', 'audio', false);
            element.attributes.src = CKEDITOR.tools.createImageData(attributes);
            return element;
          }
        }
      });
    }
  }
});

<?php
/**
 * Settings Installer
 *
 * @category    GadgetModel
 * @package     Settings
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2012-2014 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Settings_Installer extends Jaws_Gadget_Installer
{
    /**
     * Gadget Registry keys
     *
     * @var     array
     * @access  private
     */
    var $_RegKeys = array(
        array('admin_script', ''),
        array('http_auth', 'false'),
        array('realm', 'Jaws Control Panel'),
        array('key', ''),
        array('theme', 'jaws'),
        array('theme_variables', ''),
        array('date_format', 'd MN Y', true),
        array('calendar', 'Gregorian', true),
        array('timezone', 'UTC', true),
        array('gzip_compression', 'false'),
        array('use_gravatar', 'no'),
        array('gravatar_rating', 'G'),
        array('editor', 'TextArea', true),
        array('editor_tinymce_frontend_toolbar', ''),
        array('editor_tinymce_backend_toolbar', ''),
        array('editor_ckeditor_frontend_toolbar', ''),
        array('editor_ckeditor_backend_toolbar', ''),
        array('browsers_flag', 'opera,firefox,ie7up,ie,safari,nav,konq,gecko,text'),
        array('show_viewsite', 'true'),
        array('robots', ''),
        array('connection_timeout', '5'),           // per second
        array('global_website', 'true'),            // global website?
        array('img_driver', 'GD'),                  // image driver
        array('site_status', 'enabled'),
        array('site_name', ''),
        array('site_slogan', ''),
        array('site_comment', ''),
        array('site_keywords', ''),
        array('site_description', ''),
        array('site_custom_meta', ''),
        array('site_author', ''),
        array('site_license', ''),
        array('site_favicon', 'images/jaws.png'),
        array('site_title_separator', '-'),
        array('main_gadget', '', true),
        array('site_copyright', ''),
        array('site_language', 'en', true),
        array('admin_language', 'en', true),
        array('site_email', ''),
        array('cookie_domain', ''),
        array('cookie_path', '/'),
        array('cookie_version', '0.4'),
        array('cookie_session', 'false'),
        array('cookie_secure', 'false'),
        array('cookie_httponly', 'false'),
        array('ftp_enabled', 'false'),
        array('ftp_host', '127.0.0.1'),
        array('ftp_port', '21'),
        array('ftp_mode', 'passive'),
        array('ftp_user', ''),
        array('ftp_pass', ''),
        array('ftp_root', ''),
        array('proxy_enabled', 'false'),
        array('proxy_type', 'http'),
        array('proxy_host', ''),
        array('proxy_port', '80'),
        array('proxy_auth', 'false'),
        array('proxy_user', ''),
        array('proxy_pass', ''),
        array('mailer', 'phpmail'),
        array('gate_email', ''),
        array('gate_title', ''),
        array('smtp_vrfy', 'false'),
        array('sendmail_path', '/usr/sbin/sendmail'),
        array('sendmail_args', ''),
        array('smtp_host', '127.0.0.1'),
        array('smtp_port', '25'),
        array('smtp_auth', 'false'),
        array('pipelining', 'false'),
        array('smtp_user', ''),
        array('smtp_pass', ''),
    );

    /**
     * Gadget ACLs
     *
     * @var     array
     * @access  private
     */
    var $_ACLKeys = array(
        'BasicSettings',
        'AdvancedSettings',
        'MetaSettings',
        'MailSettings',
        'FTPSettings',
        'ProxySettings',
    );

    /**
     * Installs the gadget
     *
     * @access  public
     * @return  mixed   True on successful installation, Jaws_Error otherwise
     */
    function Install()
    {
        $robots = array(
            'Yahoo! Slurp', 'Baiduspider', 'Googlebot', 'msnbot', 'Gigabot', 'ia_archiver',
            'yacybot', 'http://www.WISEnutbot.com', 'psbot', 'msnbot-media', 'Ask Jeeves',
        );

        $uniqueKey =  sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                              mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
                              mt_rand( 0, 0x0fff ) | 0x4000,
                              mt_rand( 0, 0x3fff ) | 0x8000,
                              mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ) );
        $uniqueKey = md5($uniqueKey);

        // Registry keys
        $this->gadget->registry->update('key', $uniqueKey);
        $this->gadget->registry->update('robots', implode(',', $robots));
        // tinyMCE frontend toolbar
        $this->gadget->registry->update(
            'editor_tinymce_frontend_toolbar',
            ',code,undo,redo,|,ltr,rtl,|,bold,italic,underline,strikethrough,|'.
            ',blockquote,outdent,indent,|,alignleft,aligncenter,alignright,alignjustify,|,bullist,numlist,|'.
            ',link,unlink,image,|,forecolor,backcolor,|,formatselect,fontselect,fontsizeselect,'
        );
        // tinyMCE backend toolbar
        $this->gadget->registry->update(
            'editor_tinymce_backend_toolbar',
            ',undo,redo,|,ltr,rtl,|,styleselect,|,bold,italic,|,alignleft,aligncenter,alignright'.
            ',alignjustify,|,bullist,numlist,outdent,indent,|,link,unlink,image,|'.
            ',styleprops,attribs,|,fontselect,fontsizeselect,|,forecolor,backcolor,'
        );
        // CKEditor frontend toolbar
        $this->gadget->registry->update(
            'editor_ckeditor_frontend_toolbar',
            ',Source,-,Undo,Redo,|,BidiLtr,BidiRtl,|,Bold,Italic,Underline,Strike,|'.
            ',Blockquote,-,Outdent,Indent,|,JustifyLeft,JustifyCenter,JustifyRight,JustifyBlock,|'.
            ',NumberedList,BulletedList,|,Link,Unlink,Anchor,Image,|,TextColor,BGColor,|,Format,Font,FontSize,'
        );
        // CKEditor backend toolbar
        $this->gadget->registry->update(
            'editor_ckeditor_backend_toolbar',
            ',Source,-,NewPage,DocProps,Preview,Print,-,Templates,|'.
            ',CutCopy,Paste,PasteText,PasteFromWord,-,Undo,Redo,|,Find,Replace,-,SelectAll,|'.
            ',Form,Checkbox,Radio,TextField,Textarea,Select,Button,ImageButton,HiddenField,|'.
            ',Bold,Italic,Underline,Strike,Subscript,Superscript,-,RemoveFormat,|'.
            ',NumberedList,BulletedList,-,Outdent,Indent,-,Blockquote,CreateDiv,-,JustifyLeft'.
            ',JustifyCenter,JustifyRight,JustifyBlock,-,BidiLtr,BidiRtl,|'.
            ',Link,Unlink,Anchor,|,Image,Flash,Table,HorizontalRule,SpecialChar,PageBreak,|'.
            ',TextColor,BGColor,|,Styles,Format,Font,FontSize,|,Maximize,ShowBlocks,'
        );

        return true;
    }

    /**
     * Upgrades the gadget
     *
     * @access  public
     * @param   string  $old    Current version (in registry)
     * @param   string  $new    New version (in the $gadgetInfo file)
     * @return  mixed   True on success, Jaws_Error otherwise
     */
    function Upgrade($old, $new)
    {
        if (version_compare($old, '1.0.0', '<')) {
            $this->gadget->registry->delete('calendar_language');
            $this->gadget->registry->delete('cookie_precedence');
            $this->gadget->registry->update('theme', null, true);
            $this->gadget->registry->update('date_format', null, true);
            $this->gadget->registry->rename('calendar_type', 'calendar', true);
            $this->gadget->registry->update('timezone', null, true);
            $this->gadget->registry->update('editor', null, true);
            $this->gadget->registry->update('main_gadget', null, true);
            $this->gadget->registry->update('site_language', null, true);
            $this->gadget->registry->update('admin_language', null, true);
            // tinyMCE backend toolbar
            $this->gadget->registry->insert(
                'editor_tinymce_backend_toolbar',
                ',undo,redo,|,ltr,rtl,|,styleselect,|,bold,italic,|,alignleft,aligncenter,alignright'.
                ',alignjustify,|,bullist,numlist,outdent,indent,|,link,unlink,image,|'.
                ',styleprops,attribs,|,fontselect,fontsizeselect,|,forecolor,backcolor,'
            );
            // CKEditor backend toolbar
            $this->gadget->registry->insert(
                'editor_ckeditor_backend_toolbar',
                ',Source,-,NewPage,DocProps,Preview,Print,-,Templates,|'.
                ',CutCopy,Paste,PasteText,PasteFromWord,-,Undo,Redo,|,Find,Replace,-,SelectAll,|,'.
                ',Form,Checkbox,Radio,TextField,Textarea,Select,Button,ImageButton,HiddenField,|,'.
                ',Bold,Italic,Underline,Strike,Subscript,Superscript,-,RemoveFormat,|,'.
                ',NumberedList,BulletedList,-,Outdent,Indent,-,Blockquote,CreateDiv,-,JustifyLeft'.
                ',JustifyCenter,JustifyRight,JustifyBlock,-,BidiLtr,BidiRtl,|,'.
                ',Link,Unlink,Anchor,|,Image,Flash,Table,HorizontalRule,SpecialChar,PageBreak,|,'.
                ',TextColor,BGColor,|,Styles,Format,Font,FontSize,|,Maximize,ShowBlocks,|,'
            );
            $this->gadget->registry->rename('editor_tinymce_toolbar', 'editor_tinymce_frontend_toolbar');
            $this->gadget->registry->rename('editor_ckeditor_toolbar', 'editor_ckeditor_frontend_toolbar');
            // tinyMCE frontend toolbar
            $this->gadget->registry->update(
                'editor_tinymce_frontend_toolbar',
                ',newdocument,undo,redo,|,ltr,rtl,|,bold,italic,underline,strikethrough,|'.
                ',alignleft,aligncenter,alignright,alignjustify,|,bullist,numlist,|'.
                ',outdent,indent,blockquote,|,link,unlink,image,|,forecolor,backcolor,'
            );
            // CKEditor frontend toolbar
            $this->gadget->registry->update(
                'editor_ckeditor_frontend_toolbar',
                ',NewPage,Undo,Redo,|,BidiLtr,BidiRtl,|,Bold,Italic,Underline,Strike,|'.
                ',NumberedList,BulletedList,-,Outdent,Indent,-,Blockquote,-,JustifyLeft'.
                ',JustifyCenter,JustifyRight,JustifyBlock,|,Link,Unlink,Image,|,TextColor,BGColor'
            );
        }

        if (version_compare($old, '1.1.0', '<')) {
            // tinyMCE frontend toolbar
            $this->gadget->registry->update(
                'editor_tinymce_frontend_toolbar',
                ',code,undo,redo,|,ltr,rtl,|,bold,italic,underline,strikethrough,|'.
                ',blockquote,outdent,indent,|,alignleft,aligncenter,alignright,alignjustify,|,bullist,numlist,|'.
                ',link,unlink,image,|,forecolor,backcolor,|,formatselect,fontselect,fontsizeselect,'
            );
            // CKEditor frontend toolbar
            $this->gadget->registry->update(
                'editor_ckeditor_frontend_toolbar',
                ',Source,-,Undo,Redo,|,BidiLtr,BidiRtl,|,Bold,Italic,Underline,Strike,|'.
                ',Blockquote,-,Outdent,Indent,|,JustifyLeft,JustifyCenter,JustifyRight,JustifyBlock,|'.
                ',NumberedList,BulletedList,|,Link,Unlink,Anchor,Image,|,TextColor,BGColor,|,Format,Font,FontSize,'
            );
        }

        if (version_compare($old, '1.2.0', '<')) {
            $this->gadget->registry->update('theme', null, false);
            $this->gadget->registry->insert('theme_variables', '');
        }

        return true;
    }

}
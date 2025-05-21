<?php
/**
 * Settings Installer
 *
 * @category    GadgetModel
 * @package     Settings
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2012-2024 Jaws Development Group
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
        array('instance', ''),
        array('admin_script', ''),
        array('http_auth', 'false'),
        array('realm', 'Jaws Control Panel'),
        array('key', ''),
        array('theme', array('name' => 'jaws', 'locality' => 0)),
        array('theme_variables', '', true),
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
        array('editor_quill_frontend_toolbar', ''),
        array('editor_quill_backend_toolbar', ''),
        array('editor_summernote_frontend_toolbar', ''),
        array('editor_summernote_backend_toolbar', ''),
        array('browsers_flag', 'opera,firefox,ie7up,ie,safari,nav,konq,gecko,text'),
        array('show_viewsite', 'true'),
        array('robots', ''),
        array('connection_timeout', '5'),           // per second
        array('global_website', 'true'),            // global website?
        array('img_driver', 'GD'),                  // image driver
        array('fm_driver', 'File'),                 // Filesystem management driver
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
        array('site_mobile', ''),
        array('cookie_domain', ''),
        array('cookie_path', '/'),
        array('cookie_version', '0.4'),
        array('cookie_session', 'false'),
        array('cookie_secure', 'false'),
        array('cookie_httponly', 'true'),
        array('cookie_samesite', 'Lax'),
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
        array('master', ''),
        array('holder', ''),
        array('parent', ''),
        array('health_status', '1'),
        array('cache_driver', ''),
        array('service_worker_enabled', false),
        array('pwa_enabled', false),
        array('pwa_version', '1.0.0'),
        array('pwa_fullname', 'Jaws Application'),
        array('pwa_shortname', 'jaws'),
        array('pwa_description', 'Jaws Application Description'),
        array('buildnumber', ''),
    );

    /**
     * Gadget ACLs
     *
     * @var     array
     * @access  private
     */
    var $_ACLKeys = array(
        'BasicSettings',
        'ManageSiteStatus',
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
        $this->gadget->registry->update('buildnumber', date('YmdGi'));
        $this->gadget->registry->update('instance', (string)time());

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
            ',alignjustify,|,bullist,numlist,outdent,indent,|,link,unlink,image,media|'.
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

        // Quill frontend toolbar
        $this->gadget->registry->update(
            'editor_quill_frontend_toolbar',
            ''
        );

        // Quill backend toolbar
        $this->gadget->registry->update(
            'editor_quill_backend_toolbar',
            ''
        );

        // Summernote frontend toolbar
        $this->gadget->registry->update(
            'editor_summernote_frontend_toolbar',
            ''
        );

        // Summernote backend toolbar
        $this->gadget->registry->update(
            'editor_summernote_backend_toolbar',
            ''
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
                ',alignjustify,|,bullist,numlist,outdent,indent,|,link,unlink,image,media,|'.
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
                ',link,unlink,image,media,|,forecolor,backcolor,|,formatselect,fontselect,fontsizeselect,'
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
            $tblReg = Jaws_ORM::getInstance()->table('registry');
            $result = $tblReg->select('id', 'key_value')
                ->where('component', 'Settings')
                ->and()
                ->where('key_name', 'theme')
                ->fetchAll();
            if (Jaws_Error::IsError($result)) {
                return $result;
            }

            foreach ($result as $rec) {
                $result = $tblReg->update(
                    array(
                        'key_value' => json_encode(
                            serialize(array('name' => json_decode($rec['key_value']), 'locality' => 0))
                        )
                    )
                )->where('id', (int)$rec['id'])
                ->exec();
                if (Jaws_Error::IsError($result)) {
                    return $result;
                }
            }

            // registry keys
            $this->gadget->registry->insert('theme_variables', '', true);
            $this->gadget->registry->insert('master', '');
            $this->gadget->registry->insert('holder', '');
            $this->gadget->registry->insert('parent', '');
            // ACL keys
            $this->gadget->acl->insert('ManageSiteStatus');
        }

        if (version_compare($old, '1.3.0', '<')) {
            $this->gadget->registry->update('theme', null, false);
            $result = Jaws_ORM::getInstance()
                ->table('registry')
                ->delete()
                ->where('component', 'Settings')
                ->and()
                ->where('key_name', 'theme')
                ->and()
                ->where('user', 0, '<>')
                ->exec();
            if (Jaws_Error::IsError($result)) {
                return $result;
            }
        }

        if (version_compare($old, '1.5.0', '<')) {
            // registry keys 
            $this->gadget->registry->insert('site_mobile', '');
        }

        if (version_compare($old, '1.6.0', '<')) {
            // registry keys 
            $tblReg = Jaws_ORM::getInstance()->table('registry');
            $result = $tblReg->select('id', 'key_value')
                ->where('component', 'Settings')
                ->and()
                ->where('key_name', 'theme')
                ->fetchAll();
            if (Jaws_Error::IsError($result)) {
                return $result;
            }

            foreach ($result as $rec) {
                $result = $tblReg->update(
                    array('key_value' => json_encode(unserialize(json_decode($rec['key_value']))))
                )->where('id', (int)$rec['id'])
                ->exec();
                if (Jaws_Error::IsError($result)) {
                    return $result;
                }
            }
        }

        if (version_compare($old, '1.7.0', '<')) {
            // registry keys 
            $this->gadget->registry->insert('health_status', '1');
        }

        if (version_compare($old, '1.8.0', '<')) {
            // registry keys 
            $this->gadget->registry->insert('cache_driver', '');
        }

        if (version_compare($old, '1.9.0', '<')) {
            // registry keys 
            $this->gadget->registry->insert('pwa_enabled', false);
            $this->gadget->registry->insert('pwa_version', '1.0.0');
            $this->gadget->registry->insert('pwa_fullname', 'Jaws Application');
            $this->gadget->registry->insert('pwa_shortname', 'jaws');
            $this->gadget->registry->insert('pwa_description', 'Jaws Application Description');
        }

        if (version_compare($old, '2.0.0', '<')) {
            // nothing to do
        }

        if (version_compare($old, '2.1.0', '<')) {
            // nothing to do
        }

        if (version_compare($old, '2.2.0', '<')) {
            // registry keys 
            $this->gadget->registry->insert('buildnumber', date('YmdGi'));
        }

        if (version_compare($old, '2.3.0', '<')) {
            // registry keys 
            $this->gadget->registry->insert('instance', (string)time());
        }

        if (version_compare($old, '2.4.0', '<')) {
            // registry keys 
            $this->gadget->registry->update('pwa_version', '2.0.0');
            $this->gadget->registry->update('buildnumber', date('YmdGi'));
        }

        if (version_compare($old, '2.5.0', '<')) {
            // registry keys 
            $this->gadget->registry->insert('service_worker_enabled', false);
        }

        if (version_compare($old, '2.6.0', '<')) {
            // registry keys 
            $this->gadget->registry->update('pwa_version', '3.0.0');
        }

        if (version_compare($old, '2.7.0', '<')) {
            // do nothing
        }

        if (version_compare($old, '2.8.0', '<')) {
            // registry keys 
            $this->gadget->registry->insert('cookie_samesite', 'Lax');
        }

        if (version_compare($old, '2.9.0', '<')) {
            // registry keys 
            $this->gadget->registry->insert('fm_driver', 'File');
        }

        return true;
    }

}
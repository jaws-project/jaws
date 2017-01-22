<?php
/**
 * AbuseReporter Gadget Admin
 *
 * @category   GadgetAdmin
 * @package    AbuseReporter
 */
class AbuseReporter_Actions_Admin_Reports extends AbuseReporter_Actions_Admin_Default
{
    /**
     * Builds Reports UI
     *
     * @access  public
     * @return  string  XHTML UI
     */
    function Reports()
    {
        $this->gadget->CheckPermission('ManageReports');
        $this->AjaxMe('script.js');
        $tpl = $this->gadget->template->loadAdmin('Reports.html');
        $tpl->SetBlock('Reports');

        //Menu bar
        $tpl->SetVariable('menubar', $this->MenuBar('Reports'));

        //Recipient filter
        $recipientCombo =& Piwi::CreateWidget('Combo', 'recipient_filter');
        $recipientCombo->SetID('recipient_filter');
        $recipientCombo->AddEvent(ON_CHANGE, "getReports('reports_datagrid', 0, true)");
        $recipientCombo->AddOption('', -1);
        $recipientCombo->AddOption($this->gadget->registry->fetch('site_author', 'Settings'), 0);
        $model = $this->gadget->model->load('Recipients');
        $recipients = $model->GetRecipients();
        if (!Jaws_Error::IsError($result)) {
            foreach ($recipients as $recipient) {
                $recipientCombo->AddOption($recipient['name'], $recipient['id']);
            }
        }
        $recipientCombo->SetDefault(-1);
        $tpl->SetVariable('lbl_recipient_filter', _t('CONTACT_RECIPIENT'));
        $tpl->SetVariable('recipient_filter', $recipientCombo->Get());
        $tpl->SetVariable('lbl_recipient_filter', _t('CONTACT_RECIPIENT'));

        //DataGrid
        $tpl->SetVariable('grid', $this->ReportsDataGrid());

        //ReportUI
        $tpl->SetVariable('report_ui', $this->ReportUI());

        $btnCancel =& Piwi::CreateWidget('Button', 'btn_cancel', _t('GLOBAL_CANCEL'), STOCK_CANCEL);
        $btnCancel->AddEvent(ON_CLICK, 'stopAction();');
        $btnCancel->SetStyle('display:none;');
        $tpl->SetVariable('btn_cancel', $btnCancel->Get());

        $btnSave =& Piwi::CreateWidget('Button', 'btn_save', _t('GLOBAL_SAVE'), STOCK_SAVE);
        $btnSave->SetEnabled($this->gadget->GetPermission('ManageReports'));
        $btnSave->AddEvent(ON_CLICK, 'updateReport(false);');
        $btnSave->SetStyle('display:none;');
        $tpl->SetVariable('btn_save', $btnSave->Get());

        $btnSaveSend =& Piwi::CreateWidget('Button', 'btn_save_send', _t('CONTACT_REPLAY_SAVE_SEND'), STOCK_SAVE);
        $btnSaveSend->SetEnabled($this->gadget->GetPermission('ManageReports'));
        $btnSaveSend->AddEvent(ON_CLICK, 'updateReport(true);');
        $btnSaveSend->SetStyle('display:none;');
        $tpl->SetVariable('btn_save_send', $btnSaveSend->Get());

        $this->gadget->layout->setVariable('incompleteReportFields', _t('CONTACT_INCOMPLETE_FIELDS'));
        $this->gadget->layout->setVariable('confirmReportDelete',    _t('CONTACT_CONTACTS_CONFIRM_DELETE'));
        $this->gadget->layout->setVariable('legend_title',            _t('CONTACT_CONTACTS_MESSAGE_DETAILS'));
        $this->gadget->layout->setVariable('messageDetail_title',     _t('CONTACT_CONTACTS_MESSAGE_DETAILS'));
        $this->gadget->layout->setVariable('reportReply_title',      _t('CONTACT_CONTACTS_MESSAGE_REPLY'));
        $this->gadget->layout->setVariable('dataURL',                 $GLOBALS['app']->getDataURL() . 'report/');

        $tpl->ParseBlock('Reports');
        return $tpl->Get();
    }

    /**
     * Builds reports datagrid
     *
     * @access  public
     * @return  string  XHTML datagrid
     */
    function ReportsDataGrid()
    {
        $model = $this->gadget->model->load();
        $total = $model->TotalOfData('reports');

        $grid =& Piwi::CreateWidget('DataGrid', array());
        $grid->SetID('reports_datagrid');
        $grid->TotalRows($total);
        $grid->pageBy(12);
        $column1 = Piwi::CreateWidget('Column', _t('GLOBAL_NAME'), null, false);
        $grid->AddColumn($column1);
        $column2 = Piwi::CreateWidget('Column', '', null, false);
        $grid->AddColumn($column2);
        $column2->SetStyle('width:16px;');
        $column3 = Piwi::CreateWidget('Column', _t('GLOBAL_DATE'), null, false);
        $column3->SetStyle('width:72px; white-space:nowrap;');
        $grid->AddColumn($column3);
        $column4 = Piwi::CreateWidget('Column', _t('GLOBAL_ACTIONS'), null, false);
        $column4->SetStyle('width:64px; white-space:nowrap;');
        $grid->AddColumn($column4);

        return $grid->Get();
    }

    /**
     * Prepares reports data for data grid
     *
     * @access  public
     * @param   int    $recipient   Recipient ID
     * @param   int    $offset      Offset of data array
     * @return  array  Data array
     */
    function GetReports($recipient = -1, $offset = null)
    {
        $model = $this->gadget->model->loadAdmin('Reports');
        $reports = $model->GetReports($recipient, 12, $offset);
        if (Jaws_Error::IsError($reports)) {
            return array();
        }

        $date = Jaws_Date::getInstance();
        $newData = array();
        foreach ($reports as $report) {
            $reportData = array();

            // Name
            $label =& Piwi::CreateWidget('Label', $report['name']);
            $label->setTitle($report['subject']);
            if (empty($report['reply'])) {
                $label->setStyle('font-weight:bold;');
            }
            $reportData['name'] = $label->get();

            // Attachment
            if (empty($report['attachment'])) {
                $reportData['attach'] = '';
            } else {
                $image =& Piwi::CreateWidget('Image', 'gadgets/Report/Resources/images/attachment.png');
                $image->setTitle($report['attachment']);
                $reportData['attach'] = $image->get();
            }

            // Date
            $label =& Piwi::CreateWidget('Label', $date->Format($report['createtime'],'Y-m-d'));
            $label->setTitle($date->Format($report['createtime'],'H:i:s'));
            $reportData['time'] = $label->get();

            // Actions
            $actions = '';
            if ($this->gadget->GetPermission('ManageReports')) {
                $link =& Piwi::CreateWidget('Link', _t('GLOBAL_EDIT'),
                                            "javascript:editReport(this, '".$report['id']."');",
                                            STOCK_EDIT);
                $actions.= $link->Get().'&nbsp;';

                $link =& Piwi::CreateWidget('Link', _t('CONTACT_CONTACTS_MESSAGE_REPLY'),
                                            "javascript:editReply(this, '" . $report['id'] . "');",
                                            'gadgets/Report/Resources/images/report_mini.png');
                $actions.= $link->Get().'&nbsp;';

                $link =& Piwi::CreateWidget('Link', _t('GLOBAL_DELETE'),
                                            "javascript:deleteReport(this, '".$report['id']."');",
                                            STOCK_DELETE);
                $actions.= $link->Get().'&nbsp;';
            }
            $reportData['actions'] = $actions;
            $newData[] = $reportData;
        }
        return $newData;
    }

    /**
     * Show a form to show/edit a given report
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function ReportUI()
    {
        $tpl = $this->gadget->template->loadAdmin('Reports.html');
        $tpl->SetBlock('ReportUI');

        //IP
        $tpl->SetVariable('lbl_ip', _t('GLOBAL_IP'));

        //name
        $nameEntry =& Piwi::CreateWidget('Entry', 'name', '');
        $tpl->SetVariable('lbl_name', _t('GLOBAL_NAME'));
        $tpl->SetVariable('name', $nameEntry->Get());

        //email
        $nameEntry =& Piwi::CreateWidget('Entry', 'email', '');
        $tpl->SetVariable('lbl_email', _t('GLOBAL_EMAIL'));
        $tpl->SetVariable('email', $nameEntry->Get());

        //company
        $nameEntry =& Piwi::CreateWidget('Entry', 'company', '');
        $tpl->SetVariable('lbl_company', _t('CONTACT_COMPANY'));
        $tpl->SetVariable('company', $nameEntry->Get());

        //url
        $nameEntry =& Piwi::CreateWidget('Entry', 'url', '');
        $tpl->SetVariable('lbl_url', _t('GLOBAL_URL'));
        $tpl->SetVariable('url', $nameEntry->Get());

        //tel
        $nameEntry =& Piwi::CreateWidget('Entry', 'tel', '');
        $tpl->SetVariable('lbl_tel', _t('CONTACT_TEL'));
        $tpl->SetVariable('tel', $nameEntry->Get());

        //fax
        $nameEntry =& Piwi::CreateWidget('Entry', 'fax', '');
        $tpl->SetVariable('lbl_fax', _t('CONTACT_FAX'));
        $tpl->SetVariable('fax', $nameEntry->Get());

        //mobile
        $nameEntry =& Piwi::CreateWidget('Entry', 'mobile', '');
        $tpl->SetVariable('lbl_mobile', _t('CONTACT_MOBILE'));
        $tpl->SetVariable('mobile', $nameEntry->Get());

        //address
        $nameEntry =& Piwi::CreateWidget('Entry', 'address', '');
        $tpl->SetVariable('lbl_address', _t('CONTACT_ADDRESS'));
        $tpl->SetVariable('address', $nameEntry->Get());

        //recipient
        $recipientCombo =& Piwi::CreateWidget('Combo', 'rid');
        $recipientCombo->SetID('rid');
        $recipientCombo->AddOption($this->gadget->registry->fetch('site_author', 'Settings'), 0);
        $model = $this->gadget->model->load('Recipients');
        $recipients = $model->GetRecipients();
        if (!Jaws_Error::IsError($result)) {
            foreach ($recipients as $recipient) {
                $recipientCombo->AddOption($recipient['name'], $recipient['id']);
            }
        }
        $tpl->SetVariable('lbl_recipient', _t('CONTACT_RECIPIENT'));
        $tpl->SetVariable('recipient', $recipientCombo->Get());

        //subject
        $subjectEntry =& Piwi::CreateWidget('Entry', 'subject', '');
        $tpl->SetVariable('lbl_subject', _t('CONTACT_SUBJECT'));
        $tpl->SetVariable('subject', $subjectEntry->Get());

        //message
        $messageText =& Piwi::CreateWidget('TextArea', 'message','');
        $messageText->SetRows(8);
        $tpl->SetVariable('lbl_message', _t('CONTACT_MESSAGE'));
        $tpl->SetVariable('message', $messageText->Get());

        $tpl->ParseBlock('ReportUI');
        return $tpl->Get();
    }

    /**
     * Show a form to edit/send report reply
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function ReplyUI()
    {
        $tpl = $this->gadget->template->loadAdmin('Reports.html');
        $tpl->SetBlock('ReplyUI');

        //name
        $nameEntry =& Piwi::CreateWidget('Entry', 'name', '');
        $nameEntry->SetReadOnly(true);
        $tpl->SetVariable('lbl_name', _t('GLOBAL_NAME'));
        $tpl->SetVariable('name', $nameEntry->Get());

        //email
        $nameEntry =& Piwi::CreateWidget('Entry', 'email', '');
        $nameEntry->SetReadOnly(true);
        $tpl->SetVariable('lbl_email', _t('GLOBAL_EMAIL'));
        $tpl->SetVariable('email', $nameEntry->Get());

        //subject
        $subjectEntry =& Piwi::CreateWidget('Entry', 'subject', '');
        $subjectEntry->SetReadOnly(true);
        $tpl->SetVariable('lbl_subject', _t('CONTACT_SUBJECT'));
        $tpl->SetVariable('subject', $subjectEntry->Get());

        //message
        $messageText =& Piwi::CreateWidget('TextArea', 'message','');
        $messageText->SetReadOnly(true);
        $messageText->SetRows(8);
        $tpl->SetVariable('lbl_message', _t('CONTACT_MESSAGE'));
        $tpl->SetVariable('message', $messageText->Get());

        //reply
        $replyText =& Piwi::CreateWidget('TextArea', 'reply','');
        $replyText->SetRows(10);
        $tpl->SetVariable('lbl_reply', _t('CONTACT_REPLY'));
        $tpl->SetVariable('reply', $replyText->Get());

        $tpl->ParseBlock('ReplyUI');
        return $tpl->Get();
    }

    /**
     * Send report reply
     *
     * @access  public
     * @param   int     $cid    Report ID
     * @return  mixed   True on Success or Jaws_Error on Failure
     */
    function SendReply($cid)
    {
        $model = $this->gadget->model->loadAdmin('Reports');
        $report = $model->GetReply($cid);
        if (Jaws_Error::IsError($report)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'),
                                                       RESPONSE_ERROR);
            return new Jaws_Error(_t('GLOBAL_ERROR_QUERY_FAILED'));
        }

        if (!isset($report['id'])) {
            $GLOBALS['app']->Session->PushLastResponse(_t('CONTACT_ERROR_CONTACT_DOES_NOT_EXISTS'),
                                                       RESPONSE_ERROR);
            return new Jaws_Error(_t('CONTACT_ERROR_CONTACT_DOES_NOT_EXISTS'));
        }

        $from_name  = '';
        $from_email = '';
        $to  = $report['email'];
        $rid = $report['recipient'];
        if ($rid != 0) {
            $rModel = $this->gadget->model->load('Recipients');
            $recipient = $rModel->GetRecipient($rid);
            if (Jaws_Error::IsError($recipient)) {
                $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'),
                                                           RESPONSE_ERROR);
                return new Jaws_Error(_t('GLOBAL_ERROR_QUERY_FAILED'));
            }
            if (!isset($recipient['id'])) {
                $GLOBALS['app']->Session->PushLastResponse(_t('CONTACT_ERROR_RECIPIENT_DOES_NOT_EXISTS'),
                                                           RESPONSE_ERROR);
                return new Jaws_Error(_t('CONTACT_ERROR_RECIPIENT_DOES_NOT_EXISTS'));
            }
            $from_name  = $recipient['name'];
            $from_email = $recipient['email'];
        }

        $format = $this->gadget->registry->fetch('email_format');
        if ($format == 'html') {
            $reply = $this->gadget->ParseText($report['reply']);
        } else {
            $reply = $report['reply'];
        }

        $jDate = Jaws_Date::getInstance();
        $site_url  = $GLOBALS['app']->getSiteURL('/');
        $site_name = $this->gadget->registry->fetch('site_name', 'Settings');
        $site_language = $this->gadget->registry->fetch('site_language', 'Settings');
        $profile_url = $GLOBALS['app']->getSiteURL('/'). $GLOBALS['app']->Map->GetURLFor(
            'Users',
            'Profile',
            array('user' => $GLOBALS['app']->Session->GetAttribute('username'))
        );
        Jaws_Translate::getInstance()->LoadTranslation('Global', JAWS_COMPONENT_OTHERS, $site_language);
        Jaws_Translate::getInstance()->LoadTranslation('Report', JAWS_COMPONENT_GADGET, $site_language);

        $tpl = $this->gadget->template->load('SendReplyTo.html',
            array(
                'loadFromTheme' => true,
                'loadRTLDirection' => _t_lang($site_language, 'GLOBAL_LANG_DIRECTION') == 'rtl',
            )
        );
        $tpl->SetBlock($format);

        $tpl->SetVariable('lbl_name',    _t_lang($site_language, 'GLOBAL_NAME'));
        $tpl->SetVariable('lbl_email',   _t_lang($site_language, 'GLOBAL_EMAIL'));
        $tpl->SetVariable('lbl_message', _t_lang($site_language, 'CONTACT_MESSAGE'));
        $tpl->SetVariable('lbl_reply',   _t_lang($site_language, 'CONTACT_REPLY'));
        $tpl->SetVariable('name',        $report['name']);
        $tpl->SetVariable('email',       $report['email']);
        $tpl->SetVariable('subject',     $report['subject']);
        $tpl->SetVariable('message',     $report['msg_txt']);
        $tpl->SetVariable('reply',       $reply);
        $tpl->SetVariable('createtime',  $jDate->Format($report['createtime']));
        $tpl->SetVariable('nickname',    $GLOBALS['app']->Session->GetAttribute('nickname'));
        $tpl->SetVariable('profile_url', $profile_url);
        $tpl->SetVariable('site-name',   $site_name);
        $tpl->SetVariable('site-url',    $site_url);
        $tpl->ParseBlock($format);
        $template = $tpl->Get();
        $subject = _t_lang($site_language, 'CONTACT_REPLY_TO', Jaws_XSS::defilter($report['subject']));

        $mail = Jaws_Mail::getInstance();
        $mail->SetFrom($from_email, $from_name);
        $mail->AddRecipient($to);
        $mail->AddRecipient('', 'cc');
        $mail->SetSubject($subject);
        $mail->SetBody($template, $format);
        $result = $mail->send();
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('CONTACT_ERROR_REPLY_NOT_SENT'), RESPONSE_ERROR);
            return false;
        }

        $model->UpdateReplySent($cid, true);
        $GLOBALS['app']->Session->PushLastResponse(_t('CONTACT_REPLY_SENT'), RESPONSE_NOTICE);
        return true;
    }
}
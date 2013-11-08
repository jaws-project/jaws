<?php
/**
 * Friends Admin Gadget
 *
 * @category   GadgetAdmin
 * @package    Friends
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Friends_Actions_Admin_Friends extends Jaws_Gadget_Action
{
    /**
     * Creates the datagrid
     *
     * @access  public
     * @return  string  XHTML template of datagrid
     */
    function DataGrid()
    {
        $model = $this->gadget->model->loadAdmin('Friends');
        $total = $model->TotalOfData('friend');
        $datagrid =& Piwi::CreateWidget('DataGrid', array());
        $datagrid->SetID('friends_datagrid');
        $datagrid->TotalRows($total);
        $datagrid->AddColumn(Piwi::CreateWidget('Column', _t('FRIENDS_FRIEND')));
        $datagrid->AddColumn(Piwi::CreateWidget('Column', _t('GLOBAL_URL')));
        $datagrid->AddColumn(Piwi::CreateWidget('Column', _t('GLOBAL_ACTIONS')));
        $datagrid->SetStyle('width: 100%;');
        return $datagrid->Get();
    }

    /**
     * Get a list of friends in an array, it will contain actions and all
     * the stuff so Ajax can use it
     *
     * @access  public
     * @param   int     $limit  Limit of data
     * @return  array   Data array
     */
    function GetFriends($limit = 0)
    {
        $model = $this->gadget->model->load('Friends');
        $friends = $model->GetFriendsList($limit);
        if (Jaws_Error::IsError($friends)) {
            return array();
        }

        $i = 0;
        $data = array();
        foreach ($friends as $friend) {
            $url = $friend['url'];
            $clean_url = Jaws_XSS::filter($url);
            if (strlen($url) > 30) {
                $url = '<a title="'.$clean_url.'" href="'.$clean_url.'">' . Jaws_XSS::filter(substr($url, 0, 30)) . '...</a>';
            } else {
                $url = '<a title="'.$clean_url.'" href="'.$clean_url.'">'.$clean_url.'</a>';
            }
            $friend['url'] = $url;
            $actions = '';
            if ($this->gadget->GetPermission('EditFriend')) {
                $link = Piwi::CreateWidget('Link', _t('GLOBAL_EDIT'),
                    "javascript: editFriend('".$friend['id']."');",
                    STOCK_EDIT);
                $actions.= $link->Get().'&nbsp;';
            }
            if ($this->gadget->GetPermission('DeleteFriend')) {
                $actions = (empty($actions)) ? $actions : $actions . '|&nbsp;';
                $link = Piwi::CreateWidget('Link', _t('GLOBAL_DELETE'),
                    "javascript: if (confirm('"._t("FRIENDS_CONFIRM_DELETE_FRIEND")."')) ".
                    "deleteFriend('".$friend['id']."');",
                    STOCK_DELETE);
                $actions.= $link->Get();
            }
            unset($friend['id']);
            $friend['actions'] = $actions;
            $data[] = $friend;
        }
        return $data;
    }

    /**
     * Creates and prints the administration template
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function Friends()
    {
        $this->AjaxMe('script.js');
        $tpl = $this->gadget->loadAdminTemplate('Friends.html');
        $tpl->SetBlock('friends');
        $tpl->SetVariable('grid', $this->Datagrid());

        ///Config properties
        if ($this->gadget->GetPermission('UpdateProperties')) {
            $config_form =& Piwi::CreateWidget('Form', BASE_SCRIPT, 'post');
            $config_form->Add(Piwi::CreateWidget('HiddenEntry', 'gadget', 'Friends'));
            $config_form->Add(Piwi::CreateWidget('HiddenEntry', 'action', 'UpdateProperties'));

            include_once JAWS_PATH . 'include/Jaws/Widgets/FieldSet.php';
            $fieldset_config = new Jaws_Widgets_FieldSet(_t('GLOBAL_PROPERTIES'));
            $fieldset_config->SetDirection('vertical');

            $limitcombo =& Piwi::CreateWidget('Combo', 'limit_random');
            $limitcombo->SetTitle(_t('FRIENDS_LIMIT_RANDOM'));
            for ($i = 1; $i <= 10; $i++) {
                $limitcombo->AddOption($i, $i);
            }

            $limit = $this->gadget->registry->fetch('limit');
            if (Jaws_Error::IsError($limit) || !$limit) {
                $limit = 10;
            }

            $limitcombo->SetDefault($limit);

            $fieldset_config->Add($limitcombo);

            $config_form->Add($fieldset_config);
            $submit_config =& Piwi::CreateWidget('Button', 'saveproperties',
                _t('GLOBAL_UPDATE', _t('GLOBAL_PROPERTIES')), STOCK_SAVE);
            $submit_config->SetStyle(_t('GLOBAL_LANG_DIRECTION')=='rtl'?'float: left;' : 'float: right;');
            $submit_config->AddEvent(ON_CLICK, 'javascript: updateProperties(this.form);');


            $config_form->Add($submit_config);
            $tpl->SetVariable('config_form', $config_form->Get());
        }

        if ($this->gadget->GetPermission('AddFriend')) {
            $friend = array();
            $friends_form =& Piwi::CreateWidget('Form', BASE_SCRIPT, 'post', '', 'friends_form');
            $friends_form->Add(Piwi::CreateWidget('HiddenEntry', 'gadget', 'Friends'));
            $friends_form->Add(Piwi::CreateWidget('HiddenEntry', 'action', 'AddFriend'));
            $friends_form->Add(Piwi::CreateWidget('HiddenEntry', 'id', ''));

            include_once JAWS_PATH . 'include/Jaws/Widgets/FieldSet.php';
            $fieldset_friebd = new Jaws_Widgets_FieldSet(_t('FRIENDS_FRIEND'));
            $fieldset_friebd->SetDirection('vertical');

            $action = jaws()->request->fetch('action', 'get');
            $action = !(is_null($action) ? $action : '');

            $friendentry =& Piwi::CreateWidget('Entry', 'friend',
                (isset($friend['friend']) ?
                    Jaws_XSS::filter($friend['friend']) : ''));
            $friendentry->SetTitle(_t('FRIENDS_FRIEND'));
            $friendentry->SetStyle('width: 250px;');
            $fieldset_friebd->Add($friendentry);

            $urlentry =& Piwi::CreateWidget('Entry', 'url',
                (isset($friend['url']) ?
                    Jaws_XSS::filter($friend['url']) : 'http://'));
            $urlentry->SetTitle(_t('GLOBAL_URL'));
            $urlentry->SetStyle('direction: ltr; width: 250px;');
            $fieldset_friebd->Add($urlentry);

            $buttonbox =& Piwi::CreateWidget('HBox');
            $buttonbox->SetStyle(_t('GLOBAL_LANG_DIRECTION')=='rtl'?'float: left;' : 'float: right;'); //hig style
            $submit =& Piwi::CreateWidget('Button', 'addnewfriend', _t('GLOBAL_SAVE'), STOCK_SAVE);
            $submit->AddEvent(ON_CLICK, 'javascript: submitForm(this.form);');

            $cancel =& Piwi::CreateWidget('Button', 'cancelform', _t('GLOBAL_CANCEL'), STOCK_CANCEL);
            $cancel->AddEvent(ON_CLICK, "cleanForm(this.form);");

            $buttonbox->Add($cancel);
            $buttonbox->Add($submit);

            $friends_form->Add($fieldset_friebd);
            $friends_form->Add($buttonbox);

            $tpl->SetVariable('friend_form', $friends_form->Get());
        }
        $tpl->ParseBlock('friends');

        return $tpl->Get();
    }
}
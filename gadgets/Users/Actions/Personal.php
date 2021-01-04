<?php
/**
 * Users Core Gadget
 *
 * @category    Gadget
 * @package     Users
 */
class Users_Actions_Personal extends Users_Actions_Default
{
    /**
     * Builds a simple form to update user personal (fname, lname, gender, ...)
     *
     * @access  public
     * @return  string  XHTML form
     */
    function Personal()
    {
        if (!$this->app->session->user->logged) {
            return Jaws_Header::Location(
                $this->gadget->urlMap(
                    'Login',
                    array('referrer'  => bin2hex(Jaws_Utils::getRequestURL(true)))
                )
            );
        }

        $this->gadget->CheckPermission('EditUserPersonal');
        $response = $this->gadget->session->pop('Personal');
        if (!isset($response['data'])) {
            $personal  = $this->app->users->GetUser($this->app->session->user->id, true, true);
        } else {
            $personal = $response['data'];
        }

        // Load the template
        $tpl = $this->gadget->template->load('Personal.html');
        $tpl->SetBlock('personal');
        $tpl->SetVariable('title', $this::t('PERSONAL_INFO'));
        $tpl->SetVariable('base_script', BASE_SCRIPT);
        $tpl->SetVariable('update', $this::t('USERS_ACCOUNT_UPDATE'));

        // Menu navigation
        $this->gadget->action->load('MenuNavigation')->navigation($tpl);

        if (empty($personal['avatar'])) {
            $user_current_avatar = $this->app->getSiteURL('/gadgets/Users/Resources/images/photo128px.png');
        } else {
            $user_current_avatar = $this->app->getDataURL() . "avatar/" . $personal['avatar'];
            $user_current_avatar .= !empty($personal['last_update']) ? "?" . $personal['last_update'] . "" : '';
        }
        $avatar =& Piwi::CreateWidget('Image', $user_current_avatar);
        $avatar->SetID('avatar');
        $tpl->SetVariable('avatar', $avatar->Get());

        $tpl->SetVariable('lbl_fname',  $this::t('USERS_FIRSTNAME'));
        $tpl->SetVariable('fname',      $personal['fname']);
        $tpl->SetVariable('lbl_lname',  $this::t('USERS_LASTNAME'));
        $tpl->SetVariable('lname',      $personal['lname']);
        $tpl->SetVariable('lbl_gender', $this::t('USERS_GENDER'));
        $tpl->SetVariable('gender_0',   $this::t('USERS_GENDER_0'));
        $tpl->SetVariable('gender_1',   $this::t('USERS_GENDER_1'));
        $tpl->SetVariable('gender_2',   $this::t('USERS_GENDER_2'));
        $tpl->SetVariable('selected_gender_'.(int)$personal['gender'], 'selected="selected"');
        $tpl->SetVariable('lbl_ssn',    $this::t('USERS_SSN'));
        $tpl->SetVariable('ssn',        $personal['ssn']);

        if (!empty($personal['dob'])) {
            $personal['dob'] = Jaws_Date::getInstance()->Format($personal['dob'], 'Y-m-d');
        }

        $tpl->SetVariable('lbl_dob', $this::t('USERS_BIRTHDAY'));
        $tpl->SetVariable('dob',     $personal['dob']);
        $tpl->SetVariable('dob_sample', $this::t('USERS_BIRTHDAY_SAMPLE'));

        // website
        $tpl->SetVariable('lbl_url', Jaws::t('URL'));
        $tpl->SetVariable('url',     empty($personal['url'])? 'http://' : $personal['url']);

        // upload/delete avatar
        $tpl->SetVariable('lbl_upload_avatar', $this::t('USERS_AVATAR_UPLOAD'));
        $tpl->SetVariable('lbl_delete_avatar', $this::t('USERS_AVATAR_DELETE'));

        // pgpkey
        $tpl->SetVariable('lbl_pgpkey', $this::t('USERS_PGPKEY'));
        $tpl->SetVariable('pgpkey',     $personal['pgpkey']);

        // signature
        $tpl->SetVariable('lbl_signature', $this::t('USERS_SIGNATURE'));
        $tpl->SetVariable('signature',     $personal['signature']);

        // about
        $tpl->SetVariable('lbl_about', $this::t('USERS_ABOUT'));
        $tpl->SetVariable('about',     $personal['about']);

        // experiences
        $tpl->SetVariable('lbl_experiences', $this::t('USERS_EXPERIENCES'));
        $tpl->SetVariable('experiences',     $personal['experiences']);

        // occupations
        $tpl->SetVariable('lbl_occupations', $this::t('USERS_OCCUPATIONS'));
        $tpl->SetVariable('occupations',     $personal['occupations']);

        // interests
        $tpl->SetVariable('lbl_interests', $this::t('USERS_INTERESTS'));
        $tpl->SetVariable('interests',     $personal['interests']);

        if (!empty($response)) {
            $tpl->SetVariable('response_type', $response['type']);
            $tpl->SetVariable('response_text', $response['text']);
        }

        $tpl->ParseBlock('personal');
        return $tpl->Get();
    }

    /**
     * Updates user personal
     *
     * @access  public
     * @return  void
     */
    function UpdatePersonal()
    {
        if (!$this->app->session->user->logged) {
            return Jaws_Header::Location(
                $this->gadget->urlMap(
                    'Login',
                    array('referrer'  => bin2hex(Jaws_Utils::getRequestURL(true)))
                )
            );
        }

        $this->gadget->CheckPermission('EditUserPersonal');
        $post = $this->gadget->request->fetch(
            array('fname', 'lname', 'gender', 'ssn', 'dob', 'url', 'pgpkey', 'signature',
                  'about', 'avatar', 'delete_avatar', 'experiences', 'occupations', 'interests'),
            'post'
        );

        if (!empty($post['dob'])) {
            $post['dob'] = Jaws_Date::getInstance()->ToBaseDate(explode('-', $post['dob']), 'Y-m-d');
        } else {
            $post['dob'] = null;
        }

        // validate url
        if (!preg_match('|^\S+://\S+\.\S+.+$|i', $post['url'])) {
            $post['url'] = '';
        }

        unset($post['avatar']);
        if (empty($post['delete_avatar'])) {
            $res = $this->app->fileManagement::uploadFiles(
                $_FILES,
                Jaws_Utils::upload_tmp_dir(),
                'gif,jpg,jpeg,png,svg'
            );
            if (Jaws_Error::IsError($res)) {
                $this->gadget->session->push(
                    $res->GetMessage(),
                    RESPONSE_ERROR,
                    'Personal',
                    $post
                );

                return Jaws_Header::Location($this->gadget->urlMap('Personal'));
            } elseif (!empty($res)) {
                $post['avatar'] = $res['avatar'][0]['host_filename'];
            }
        } else {
            $post['avatar'] = '';
        }

        $result = $this->gadget->model->load('User')->UpdatePersonal(
            $this->app->session->user->id,
            $post
        );
        if (Jaws_Error::IsError($result)) {
            $this->gadget->session->push(
                $result->GetMessage(),
                RESPONSE_ERROR,
                'Personal',
                $post
            );
        } else {
            $this->gadget->session->push(
                $this::t('USERS_PERSONALINFO_UPDATED'),
                RESPONSE_NOTICE,
                'Personal'
            );
        }

        return Jaws_Header::Location($this->gadget->urlMap('Personal'));
    }

}
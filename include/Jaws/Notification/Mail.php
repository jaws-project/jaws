<?php
/**
 * Mail notification class
 *
 * @category    Notification
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2014 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Notification_Mail
{
    /**
     * Store mail object instance
     * @var     array
     * @access  private
     */
    private $object;

    /**
     * Site attributes
     *
     * @access  private
     * @var     array
     */
    private $attributes = array();


    /**
     * constructor
     *
     * @access  public
     * @param   array   $options    Associated options array
     * @return  void
     */
    public function __construct($options)
    {
        $this->object = Jaws_Mail::getInstance('notification');
        // fetch all registry keys related to site attributes
        $this->attributes = $GLOBALS['app']->Registry->fetchAll('Settings', false);
        Jaws_Translate::getInstance()->LoadTranslation(
            'Global',
            JAWS_COMPONENT_OTHERS,
            $this->attributes['site_language']
        );
        $this->attributes['site_url']       = $GLOBALS['app']->GetSiteURL('/');
        $this->attributes['site_direction'] = _t_lang($this->attributes['site_language'], 'GLOBAL_LANG_DIRECTION');
    }


    /**
     * Sends notify to user
     *
     * @access  public
     * @param   array   $users      Users properties associated array
     * @param   string  $title      Notification title
     * @param   string  $summary    Notification summary
     * @param   string  $content    Notification content
     * @return  mixed   Jaws_Error on failure
     */
    function notify($users, $title, $summary, $content)
    {
        $this->object->reset();
        $this->object->SetFrom();
        foreach ($users as $user) {
            $this->object->AddRecipient($user['email']);
        }
        $this->object->SetSubject($title);

        $tpl = new Jaws_Template(true);
        $tpl->loadRTLDirection = $this->attributes['site_direction'] == 'rtl';
        $tpl->Load('Notification.html', 'include/Jaws/Resources');
        $tpl->SetBlock('notification');
        $tpl->SetVariable('site-url',       $this->attributes['site_url']);
        $tpl->SetVariable('site-direction', $this->attributes['site_direction']);
        $tpl->SetVariable('site-name',      $this->attributes['site_name']);
        $tpl->SetVariable('site-slogan',    $this->attributes['site_slogan']);
        $tpl->SetVariable('site-comment',   $this->attributes['site_comment']);
        $tpl->SetVariable('site-author',    $this->attributes['site_author']);
        $tpl->SetVariable('site-license',   $this->attributes['site_license']);
        $tpl->SetVariable('site-copyright', $this->attributes['site_copyright']);
        $tpl->SetVariable('content', $content);
        $tpl->ParseBlock('notification');
        $this->object->SetBody($tpl->Get());
        unset($tpl);

        return $this->object->send();
    }

}
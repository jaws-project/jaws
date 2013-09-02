<?php
/**
 * Preferences Gadget
 *
 * @category   Gadget
 * @package    Preferences
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Preferences_HTML extends Jaws_Gadget_HTML
{
    /**
     * Default Action
     *
     * @access      public
     * @return      string   HTML content of DefaultAction
     */
    function DefaultAction()
    {
        $layoutGadget = $GLOBALS['app']->LoadGadget('Preferences', 'LayoutHTML');
        return $layoutGadget->Display();
    }

    /**
     * Save the cookie, save the world
     *
     * @access      public
     */
    function Save()
    {
        $post = jaws()->request->get(
            array(
                'theme', 'editor', 'language', 'calendar_type', 'calendar_language',
                'date_format', 'timezone'
            ),
            'post'
        );

        $expire_age = 150*24*60; //don't expired for 150 days
        $model = $GLOBALS['app']->LoadGadget('Preferences', 'Model');
        $model->SavePreferences($post, $expire_age);

        Jaws_Header::Referrer();
    }

    /**
     * Set site language by cookie
     *
     * @access      public
     */
    function SetLanguage()
    {
        $language = jaws()->request->get('lang', 'get');

        if (!is_dir(JAWS_PATH . 'languages/' . $language) &&
            !is_dir(JAWS_DATA . 'languages/' . $language))
        {
            require_once JAWS_PATH . 'include/Jaws/HTTPError.php';
            return Jaws_HTTPError::Get(404);
        }

        $preferences = $GLOBALS['app']->Session->GetCookie('preferences');
        $preferences['language'] = $language;

        $expire_age = 150*24*60; //don't expired for 150 days
        $model = $GLOBALS['app']->LoadGadget('Preferences', 'Model');
        $model->SavePreferences($preferences, $expire_age);

        Jaws_Header::Referrer();
    }

}
<?php
/**
 * Search boxes actions
 *
 * @category    GadgetLayout
 * @package     Search
 * @author      Jonathan Hernandez <ion@suavizado.com>
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2004-2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Search_Actions_Search extends Search_HTML
{
    /**
     * Builds the search box
     *
     * @access  public
     * @param   bool    $gadgets_combo  Display gadgets combo (optional, default true)
     * @return  string  XHTML search box
     */
    function Box($gadgets_combo = true)
    {
        $request =& Jaws_Request::getInstance();
        $post = $request->get(array('all', 'exact', 'least', 'exclude', 'gadgets', 'date'), 'get');

        $tpl = new Jaws_Template('gadgets/Search/templates/');
        $tpl->Load('Search.html');
        if ($gadgets_combo) {
            $block = 'Box';
        } else {
            $block = 'SimpleBox';
        }
        $tpl->SetBlock("$block");
        $tpl->SetVariable('base_script', BASE_SCRIPT);
        $tpl->SetVariable('title', _t('SEARCH_NAME'));

        $model = $GLOBALS['app']->LoadGadget('Search', 'Model');
        $wordAll =& Piwi::CreateWidget('Entry', 'all', $model->implodeSearch($post));
        $wordAll->SetTitle(_t('SEARCH_WORD_FILTER_ALL'));
        $tpl->SetVariable('lbl_all', _t('SEARCH_WORD_FILTER_ALL'));
        $tpl->SetVariable('all', $wordAll->Get());

        // Create Select box.
        if ($gadgets_combo) {
            $gadgetList = $model->GetSearchableGadgets();
            $gSearchable = $this->GetRegistry('searchable_gadgets');
            $searchableGadgets = ($gSearchable=='*')? array_keys($gadgetList) : explode(', ', $gSearchable);

            $gchk =& Piwi::CreateWidget('Combo', 'gadgets');
            $gchk->addOption(_t('GLOBAL_ALL'), '');
            foreach ($searchableGadgets as $gadget) {
                $info = $GLOBALS['app']->LoadGadget($gadget, 'Info');
                if (Jaws_Error::IsError($info)) {
                    continue;
                }
                $gchk->AddOption($info->GetTitle(), $gadget);
            }
            $default = !is_null($post['gadgets']) ? $post['gadgets'] : '';
            $gchk->SetDefault($default);
            $tpl->SetVariable('lbl_search_in', _t('SEARCH_SEARCH_IN'));
            $tpl->SetVariable('gadgets_combo', $gchk->Get());
        }

        $btnSearch =& Piwi::CreateWidget('Button', '', _t('SEARCH_BUTTON'));
        $btnSearch->SetID('btn_search');
        $btnSearch->SetSubmit(true);
        $tpl->SetVariable('btn_search', $btnSearch->Get());
        $tpl->ParseBlock("$block");

        return $tpl->Get();
    }

    /**
     * Builds the simple search box
     *
     * @access  public
     * @return  string  XHTML search box
     */
    function SimpleBox()
    {
        return $this->Box(false);
    }

    /**
     * Builds the advanced search box
     *
     * @access  public
     * @return  string  XHTML search box
     */
    function AdvancedBox()
    {
        $request =& Jaws_Request::getInstance();
        $post = $request->get(array('all', 'exact', 'least', 'exclude', 'gadgets', 'date'), 'get');

        $xss = $GLOBALS['app']->loadClass('XSS', 'Jaws_XSS');
        $post['all'] = $xss->defilter($post['all']);

        $tpl = new Jaws_Template('gadgets/Search/templates/');
        $tpl->Load('Search.html');
        $tpl->SetBlock('AdvancedBox');

        $tpl->SetVariable('base_script', BASE_SCRIPT);
        $tpl->SetVariable('title', _t('SEARCH_NAME'));
        $tpl->SetVariable('lbl_word_filter', _t('SEARCH_WORD_FILTER'));
        $tpl->SetVariable('lbl_all', _t('SEARCH_WORD_FILTER_ALL'));
        $tpl->SetVariable('lbl_exact', _t('SEARCH_WORD_FILTER_EXACT'));
        $tpl->SetVariable('lbl_least', _t('SEARCH_WORD_FILTER_LEAST'));
        $tpl->SetVariable('lbl_exclude', _t('SEARCH_WORD_FILTER_EXCLUDE'));
        $tpl->SetVariable('lbl_data_filter', _t('SEARCH_DATA_FILTER'));
        $tpl->SetVariable('lbl_search_in', _t('SEARCH_SEARCH_IN'));

        $model = $GLOBALS['app']->LoadGadget('Search', 'Model');
        $options = $model->parseSearch($post, $searchable);

        $wordAll =& Piwi::CreateWidget('Entry', 'all', implode(' ', $options['all']));
        $wordExact =& Piwi::CreateWidget('Entry', 'exact', implode(' ', $options['exact']));
        $wordLeast =& Piwi::CreateWidget('Entry', 'least', implode(' ', $options['least']));
        $wordExclude =& Piwi::CreateWidget('Entry', 'exclude', implode(' ', $options['exclude']));
        $tpl->SetVariable('all', $wordAll->Get());
        $tpl->SetVariable('exclude', $wordExclude->Get());
        $tpl->SetVariable('least', $wordLeast->Get());
        $tpl->SetVariable('exact', $wordExact->Get());

        //Gadgets filter combo
        $gadgetList = $model->GetSearchableGadgets();
        $gSearchable = $this->GetRegistry('searchable_gadgets');
        $searchableGadgets = ($gSearchable=='*')? array_keys($gadgetList) : explode(', ', $gSearchable);

        $gchk =& Piwi::CreateWidget('Combo', 'gadgets');
        $gchk->addOption(_t('GLOBAL_ALL'), '');
        foreach ($searchableGadgets as $gadget) {
            $info = $GLOBALS['app']->LoadGadget($gadget, 'Info');
            if (Jaws_Error::IsError($info)) {
                continue;
            }
            $gchk->AddOption($info->GetTitle(), $gadget);
        }
        $default = !is_null($post['gadgets']) ? $post['gadgets'] : '';
        $gchk->SetDefault($default);

        $tpl->SetVariable('gadgets_combo', $gchk->Get());

        //Search button
        $btnSearch =& Piwi::CreateWidget('Button', '', _t('SEARCH_BUTTON'));
        $btnSearch->SetID('btn_search');
        $btnSearch->SetSubmit(true);
        $tpl->SetVariable('btn_search', $btnSearch->Get());

        $tpl->ParseBlock('AdvancedBox');
        return $tpl->Get();
    }

}
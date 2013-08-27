<?php
/**
 * Filebrowser Admin Gadget
 *
 * @category   GadgetAdmin
 * @package    FileBrowser
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class FileBrowser_AdminHTML extends Jaws_Gadget_HTML
{
    /**
     * Builds the basic datagrid view
     *
     * @access  public
     * @param   string  $path
     * @return  string  XHTML template of datagrid
     */
    function DataGrid($path = '')
    {
        $model = $GLOBALS['app']->LoadGadget('FileBrowser', 'Model', 'Directory');
        $total = $model->GetDirContentsCount($path);

        $grid =& Piwi::CreateWidget('DataGrid', array());
        $grid->TotalRows($total);
        $grid->pageBy(15);
        $grid->SetID('fb_datagrid');
        $column = Piwi::CreateWidget('Column', '');
        $column->SetStyle('width: 1px;');
        $grid->AddColumn($column);
        $grid->AddColumn(Piwi::CreateWidget('Column', _t('GLOBAL_TITLE')));
        $grid->AddColumn(Piwi::CreateWidget('Column', _t('GLOBAL_NAME')));
        $grid->AddColumn(Piwi::CreateWidget('Column', _t('FILEBROWSER_SIZE')));
        $grid->AddColumn(Piwi::CreateWidget('Column', _t('FILEBROWSER_HITS')));
        $grid->AddColumn(Piwi::CreateWidget('Column', _t('GLOBAL_ACTIONS')));
        $grid->SetStyle('width: 100%;');

        return $grid->Get();
    }


    /**
     * Creates and returns some data
     *
     * @access  public
     * @param   string  $path   
     * @return  string  location link string   
     */
    function GetLocation($path)
    {
        $model = $GLOBALS['app']->LoadGadget('FileBrowser', 'Model', 'Directory');

        $dir_array = $model->GetCurrentRootDir($path);
        $path_link = '';
        $location_link = '';
        foreach ($dir_array as $d) {
            $path_link .= $d . (($d != '/')? '/' : '');
            $link =& Piwi::CreateWidget('Link', $d, "javascript: cwd('{$path_link}');");
            $location_link .= $link->Get() . '&nbsp;';
        }

        return $location_link;
    }

    /**
     * Prints the admin section
     *
     * @access  public
     * @return  string  XHTML template content of administration
     */
    function Admin()
    {
    }

}
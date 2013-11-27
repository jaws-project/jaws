<?php
/**
 * FileBrowser - Search gadget hook
 *
 * @category   GadgetHook
 * @package    FileBrowser
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2007-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class FileBrowser_Hooks_Search extends Jaws_Gadget_Hook
{
    /**
     * Returns an array with the results of a search
     *
     * @access  public
     * @param   string  $match  Match word
     * @return  array   An array of entries that matches a certain pattern
     */
    function Execute($match)
    {
        if (!$this->gadget->GetPermission('OutputAccess')) {
            return array();
        }

        if ($GLOBALS['app']->Registry->fetch('frontend_avail', 'FileBrowser') != 'true') {
            return array();
        }

        $match['all'] = array_map('preg_quote', $match['all']);
        $pattern = '';
        if (!empty($match['all'])) {
            $pattern = '(' . implode(').*(', $match['all']) . ')';
        }

        $match['exact'] = array_map('preg_quote', $match['exact']);
        if (!empty($match['exact'])) {
            if (empty($pattern)) {
                $pattern = '(' . implode(' ', $match['exact']) . ')';
            } else {
                $pattern .= '.*(' . implode(' ', $match['exact']) . ')';
            }
        }

        $match['least'] = array_map('preg_quote', $match['least']);
        if (!empty($match['least'])) {
            if (empty($pattern)) {
                $pattern = '(' . implode(')|(', $match['least']) . ')';
            } else {
                $pattern .= '.*((' . implode(')|(', $match['least']) . '))';
            }
        }
        //FIXME: exclude pattern

        require_once PEAR_PATH. 'File/Find.php';
        $path  = JAWS_DATA . 'files';
        $files = &File_Find::search('$'.$pattern.'$i', $path, 'perl', false, 'both');

        //Load model
        $model = $this->gadget->model->load('Files');
        $entries = array();
        if (is_array($files)) {
            $date = Jaws_Date::getInstance();
            foreach ($files as $f) {
                $entry['title'] = str_replace(JAWS_DATA. 'files', '', $f);
                $entry['title'] = substr($entry['title'], 1);
                if (empty($entry['title'])) {
                    $entry['title'] = '/';
                }

                if (is_dir($f)) {
                    $entry['url'] = $this->gadget->urlMap('Display', array('path' => $entry['title']));
                    $icon = 'gadgets/FileBrowser/Resources/images/folder.png';
                } else {
                    $entry['url'] = str_replace(JAWS_PATH, '', $f);
                    if (DIRECTORY_SEPARATOR!='/') {
                        $entry['url'] = str_replace('\\', '/', $entry['url']);
                    }
                    //Get the extension
                    $file_extension = strtolower(strrev(substr(strrev($f), 0, strpos(strrev($f), '.'))));
                    //Get the icon
                    $iconName = $model->getExtImage($file_extension);
                    $icon = JAWS_PATH . 'gadgets/FileBrowser/Resources/images/'.$iconName;
                    if (!is_file($icon)) {
                        $icon = 'gadgets/FileBrowser/Resources/images/unknown.png';
                    } else {
                        $icon = 'gadgets/FileBrowser/Resources/images/'.$iconName;
                    }
                }
                $entry['image'] = $icon;
                $entry['snippet'] = '';
                $entry['parse_text'] = false;
                $entry['strip_tags'] = false;
                $stamp = date('Y-m-d H:i:s', filemtime($f));
                $entry['date'] = $date->ToISO($stamp);
                $stamp = str_replace(array('-', ':', ' '), '', $stamp);
                if (isset($entries[$stamp])) {
                    $stamp += 1;
                }
                $entries[$stamp] = $entry;
            }
        }

        return $entries;
    }
}

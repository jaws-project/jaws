<?php
/**
 * Jaws NiceFormat plugin
 *
 * @category   Plugin
 * @package    NiceFormat
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2004-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class NiceFormat_Plugin extends Jaws_Plugin
{
    var $friendly = true;
    var $version = '0.4';

    /**
     * Overrides, Gets the WebControl of this plugin
     *
     * @access  public
     * @param   string  $textarea   The textarea
     * @return  string  XHTML WebControl
     */
    function GetWebControl($textarea)
    {
        $buttonbox =& Piwi::CreateWidget('Division');

        $bold =& Piwi::CreateWidget('Button', 'bold', '', STOCK_TEXT_BOLD);
        $bold->AddEvent(ON_CLICK, "javascript: insertTags('$textarea', '**','**','".
                        _t('PLUGINS_NICEFORMAT_TEXT_BOLD')."');");
        $bold->SetTitle(_t('PLUGINS_NICEFORMAT_TEXT_BOLD').' ALT+B');
        $bold->SetAccessKey('B');

        $italic =& Piwi::CreateWidget('Button', 'italic', '', STOCK_TEXT_ITALIC);
        $italic->AddEvent(ON_CLICK, "javascript: insertTags('$textarea', '\'\'','\'\'','".
                          _t('PLUGINS_NICEFORMAT_TEXT_ITALIC')."');");
        $italic->SetTitle(_t('PLUGINS_NICEFORMAT_TEXT_ITALIC').' ALT+I');
        $italic->SetAccessKey('I');

        $hrule =& Piwi::CreateWidget('Button', 'hrule', '',
                        $GLOBALS['app']->getSiteURL('/plugins/NiceFormat/images/stock-hrule.png', true));
        $hrule->AddEvent(ON_CLICK, "javascript: insertTags('$textarea', '----\\n','','');");
        $hrule->SetTitle(_t('PLUGINS_NICEFORMAT_HRULE').' ALT+H');
        $hrule->SetAccessKey('H');

        $heading1 =& Piwi::CreateWidget('Button', 'heading1', '',
                        $GLOBALS['app']->getSiteURL('/plugins/NiceFormat/images/stock-heading1.png', true));
        $heading1->AddEvent(ON_CLICK, "javascript: insertTags('$textarea', '=======','=======','".
                            _t('PLUGINS_NICEFORMAT_LEVEL_1')."');");
        $heading1->SetTitle(_t('PLUGINS_NICEFORMAT_LEVEL_1').' ALT+1');
        $heading1->SetAccessKey('1');

        $heading2 =& Piwi::CreateWidget('Button', 'heading2', '',
                        $GLOBALS['app']->getSiteURL('/plugins/NiceFormat/images/stock-heading2.png', true));
        $heading2->AddEvent(ON_CLICK, "javascript: insertTags('$textarea', '======','======','".
                            _t('PLUGINS_NICEFORMAT_LEVEL_2')."');");
        $heading2->SetTitle(_t('PLUGINS_NICEFORMAT_LEVEL_2').' ALT+2');
        $heading2->SetAccessKey('2');

        $heading3 =& Piwi::CreateWidget('Button', 'heading3', '',
                        $GLOBALS['app']->getSiteURL('/plugins/NiceFormat/images/stock-heading3.png', true));
        $heading3->AddEvent(ON_CLICK, "javascript: insertTags('$textarea', '=====','=====','".
                            _t('PLUGINS_NICEFORMAT_LEVEL_3')."');");
        $heading3->SetTitle(_t('PLUGINS_NICEFORMAT_LEVEL_3').' ALT+3');
        $heading3->SetAccessKey('3');

        $heading4 =& Piwi::CreateWidget('Button', 'heading4', '',
                        $GLOBALS['app']->getSiteURL('/plugins/NiceFormat/images/stock-heading4.png', true));
        $heading4->AddEvent(ON_CLICK, "javascript: insertTags('$textarea', '====','====','".
                            _t('PLUGINS_NICEFORMAT_LEVEL_4')."');");
        $heading4->SetTitle(_t('PLUGINS_NICEFORMAT_LEVEL_4').' ALT+4');
        $heading4->SetAccessKey('4');

        $heading5 =& Piwi::CreateWidget('Button', 'heading5', '',
                        $GLOBALS['app']->getSiteURL('/plugins/NiceFormat/images/stock-heading5.png', true));
        $heading5->AddEvent(ON_CLICK, "javascript: insertTags('$textarea', '===','===','".
                            _t('PLUGINS_NICEFORMAT_LEVEL_5')."');");
        $heading5->SetTitle(_t('PLUGINS_NICEFORMAT_LEVEL_5').' ALT+5');
        $heading5->SetAccessKey('5');

        $listenum =& Piwi::CreateWidget('Button', 'listenum', '',
                        $GLOBALS['app']->getSiteURL('/plugins/NiceFormat/images/stock-listnum.png', true));
        $listenum->AddEvent(ON_CLICK, "javascript: insertTags('$textarea', '  - ','\\n','".
                            _t('PLUGINS_NICEFORMAT_LIST_NUMERIC')."');");
        $listenum->SetTitle(_t('PLUGINS_NICEFORMAT_LIST_NUMERIC'));

        $listbullet =& Piwi::CreateWidget('Button', 'listbullet', '',
                        $GLOBALS['app']->getSiteURL('/plugins/NiceFormat/images/stock-listbullet.png', true));
        $listbullet->AddEvent(ON_CLICK, "javascript: insertTags('$textarea', '  * ','\\n','".
                                  _t('PLUGINS_NICEFORMAT_LIST_BULLET')."');");
        $listbullet->SetTitle(_t('PLUGINS_NICEFORMAT_LIST_BULLET'));

        $buttonbox->PackStart($bold);
        $buttonbox->PackStart($italic);
        $buttonbox->PackStart($heading1);
        $buttonbox->PackStart($heading2);
        $buttonbox->PackStart($heading3);
        $buttonbox->PackStart($heading4);
        $buttonbox->PackStart($heading5);
        $buttonbox->PackStart($listenum);
        $buttonbox->PackStart($listbullet);
        $buttonbox->PackStart($hrule);

        return $buttonbox;
    }

    /**
     * Overrides, Parses the text
     *
     * @access  public
     * @param   string  $html   HTML to be parsed
     * @return  string  Parsed content
     */
    function ParseText($html)
    {
        $html = preg_replace ('/__(.+?)__/s','<em>\1</em>', $html);  //emphasize
        $html = preg_replace ('/\'\'(.+?)\'\'/s','<em>\1</em>', $html);  //emphasize
        $html = preg_replace ('/\*\*(.+?)\*\*/s','<strong>\1</strong>',$html);  //bold
        $html = preg_replace ('/^(\s)*----+(\s*)$/m',"<hr noshade=\"noshade\" size=\"1\" />", $html); //hr

        //Funny chars ;-D This is a feature in NiceFormat, not a Text_Wiki or Dokuwiki feature
        $html = preg_replace('/\(c\)/i','&copy;',$html);      //  copyrigtht
        $html = preg_replace('/\(r\)/i','&reg;',$html);      //  registered
        $html = preg_replace('/\(tm\)/i','&trade;',$html);      //  trademark

        //Same here
        $html = preg_replace('#&lt;sub&gt;(.*?)&lt;/sub&gt;#is','<sub>\1</sub>',$html);
        $html = preg_replace('#&lt;sup&gt;(.*?)&lt;/sup&gt;#is','<sup>\1</sup>',$html);

        /**
         * Headers
         */
        $html = preg_replace ('/=======(.+?)=======/s','<h1>\1</h1>', $html);  //h1
        $html = preg_replace ('/======(.+?)======/s','<h2>\1</h2>', $html);  //h2
        $html = preg_replace ('/=====(.+?)=====/s','<h3>\1</h3>', $html);  //h3
        $html = preg_replace ('/====(.+?)====/s','<h4>\1</h4>', $html);  //h4
        $html = preg_replace ('/===(.+?)===/s','<h5>\1</h5>', $html);  //h5

        //Lists
        $html = preg_replace_callback(
            '/\n([\s\t]+[\*\-]\s*[^\n]+\n)+/smu',
            array($this, 'BuildList'),
            $html
        );

        return $html;
    }

    /**
     * Builds a list from textilized code
     *
     * @access  private
     * @param   string  $block  The Code to be parsed
     * @return  string  XHTML code
     */
    function BuildList($block)
    {
        //remove newline at first and end of block
        $block = Jaws_UTF8::substr($block[0], 1, -1);

        //walk line by line
        $ret = "\n";
        $lines = array_filter(preg_split("/\n/u", $block));

        //build an item array
        $cnt=0;
        $items = array();
        foreach ($lines as $line) {
            //get intendion level
            $lvl = floor(strspn($line, ' ')/2);
            $lvl+= strspn($line, "\t");
            //remove indents
            $line = preg_replace('/^[\s|\t]+/smu', '', $line);
            if (empty($line)) {
                continue;
            }
            //get type of list
            $type = $line[0] == '-'? 'ol' : 'ul';
            // remove bullet and following spaces
            $line = preg_replace('/^[\*|\-]\s*/smu','',$line);
            //add item to the list
            $items[$cnt]['level'] = $lvl;
            $items[$cnt]['type']  = $type;
            $items[$cnt]['text']  = $line;
            //increase counter
            $cnt++;
        }

        $level = -1;
        $opens = array();
        foreach ($items as $item) {
            if ($item['level'] > $level ) {
                //open new list
                $ret .= "\n<".$item['type'].">\n";
                array_push($opens,$item['type']);
            } else if ($item['level'] < $level ) {
                //close last item
                $ret .= "</li>\n";
                for ($i=0; $i<($level - $item['level']); $i++) {
                    //close higher lists
                    $ret .= '</'.array_pop($opens).">\n</li>\n";
                }
            } else if ($item['type'] != $opens[count($opens)-1]){
                //close last list and open new
                $ret .= '</'.array_pop($opens).">\n</li>\n";
                $ret .= "\n<".$item['type'].">\n";
                array_push($opens,$item['type']);
            } else {
                //close last item
                $ret .= "</li>\n";
            }

            //remember current level and type
            $level = $item['level'];

            //print item
            $ret .= '<li class="level'.$item['level'].'">';
            $ret .= '<span class="li">'.$item['text'].'</span>';
        }

        //close remaining items and lists
        while ($open = array_pop($opens)) {
            $ret .= "</li>\n";
            $ret .= '</'.$open.">\n";
        }

        return $ret;
    }

}
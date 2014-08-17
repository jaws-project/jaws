<?php
/**
 * Blog Admin HTML file
 *
 * @category   GadgetAdmin
 * @package    Blog
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2014 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Blog_Actions_Admin_Summary extends Blog_Actions_Admin_Default
{
    /**
     * Displays blog summary with some statistics
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function Summary()
    {
    
        // get table definition
        MDB2::loadFile('Schema');
        $valid_default_values = array(
            'text'      => '',
            'boolean'   => true,
            'integer'   => 0,
            'decimal'   => 0.0,
            'float'     => 0.0,
            'timestamp' => '1970-01-01 00:00:00',
            'time'      => '00:00:00',
            'date'      => '1970-01-01',
            'clob'      => '',
            'blob'      => '',
        );
        require_once PEAR_PATH. 'MDB2/Schema/Parser.php';

        $model = Jaws_Gadget::getInstance('Components')->model->load('Gadgets');
        $gadgets = $model->GetGadgetsList(null, true, true, null, true);
        foreach($gadgets as $gadget) {
            $input_file = JAWS_PATH. "gadgets/{$gadget['name']}/Resources/schema/schema.xml";
            if (!file_exists($input_file)) {
                continue;
            }
            $parser = new MDB2_Schema_Parser(array(), true, false, $valid_default_values, true, 64);
            $result = $parser->setInputString(file_get_contents($input_file));
            if (MDB2::isError($result)) {
                return $result;
            }

            $result = $parser->parse();
            if (MDB2::isError($result)) {
                return $result;
            }
            if (MDB2::isError($parser->error)) {
                return $parser->error;
            }

            $tables  = array_keys($parser->database_definition['tables']);

            // fetch/insert data
            foreach ($tables as $table) {
                $sql = "SELECT setval('{$table}_id_seq', (SELECT MAX(id) FROM {$table}))";
                $result = Jaws_DB::getInstance()->query($sql);
                if (Jaws_Error::IsError($result)) {
                   //-------------
                }
            }

        }

    }

}
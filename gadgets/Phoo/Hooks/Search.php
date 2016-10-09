<?php
/**
 * Phoo - Search gadget hook
 *
 * @category   GadgetHook
 * @package    Phoo
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2007-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Phoo_Hooks_Search extends Jaws_Gadget_Hook
{
    /**
     * Gets the gadget's search fields
     *
     * @access  public
     * @return  array   search fields array
     */
    function GetOptions() {
        return array(
            'phoo_album' => array('name', 'description'),
            'phoo_image' => array('pi.title', 'pi.description'),
        );
    }

    /**
     * Returns an array with the results of a search
     *
     * @access  public
     * @param   string  $table  Table name
     * @param   object  $objORM Jaws_ORM instance object
     * @return  array   An array of entries that matches a certain pattern
     */
    function Execute($table, &$objORM)
    {
        $entries = array();
        $date = Jaws_Date::getInstance();

        if ($table == 'phoo_album') {
            $objORM->table('phoo_album');
            $objORM->select('id', 'name', 'description', 'createtime');
            $result = $objORM->where('published', true)
                ->and()
                ->loadWhere('search.terms')
                ->orderBy('createtime desc')
                ->fetchAll();
            if (Jaws_Error::IsError($result)) {
                return false;
            }

            foreach ($result as $r) {
                $entry = array();
                $entry['title']   = $r['name'];
                $entry['url']     = $this->gadget->urlMap('ViewAlbum', array('id' => $r['id']));
                $entry['image']   = 'gadgets/Phoo/Resources/images/logo.png';
                $entry['snippet'] = $r['description'];
                $entry['date']    = $date->ToISO($r['createtime']);
                $entries[] = $entry;
            }

        } else {
            $objORM->table('phoo_image', 'pi');
            $objORM->select(
                'pi.id', 'pi.filename', 'pi.title', 'pi.description', 'pi.createtime', 'pia.phoo_album_id'
            );
            $objORM->join('phoo_image_album as pia', 'pia.phoo_image_id', 'pi.id', 'left');
            $objORM->join('phoo_album as pa', 'pa.id', 'pia.phoo_album_id', 'left');
            $result = $objORM->where('pi.published', true)
                ->and()
                ->where('pa.published', true)
                ->and()
                ->loadWhere('search.terms')
                ->orderBy('pi.createtime desc')
                ->fetchAll();
            if (Jaws_Error::IsError($result)) {
                return false;
            }

            foreach ($result as $r) {
                $entry = array();
                $entry['title'] = $r['title'];
                $entry['url']   = $this->gadget->urlMap(
                    'ViewImage',
                    array('albumid' => $r['phoo_album_id'], 'id' => $r['id'])
                );
                $path = substr($r['filename'], 0, strrpos($r['filename'], '/'));
                $file = basename($r['filename']);

                $entry['image']   = $GLOBALS['app']->getDataURL("phoo/$path/thumb/$file");
                $entry['snippet'] = $r['description'];
                $entry['date']    = $date->ToISO($r['createtime']);
                $entries[]  = $entry;
            }
        }

        return $entries;
    }

}
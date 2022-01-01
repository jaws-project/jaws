<?php
/**
 * Jaws XML Response driver
 *
 * @category    Response
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2021-2022 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Response_XML
{
    /**
     * Returns json-encoded data
     *
     * @access  public
     * @param   string  $data   Data string
     * @return  string  Returns encoded data
     */
    static function get($data)
    {
        header('Content-Type: text/xml; charset=utf-8');
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');

        $xmlObj = new DOMDocument('1.0', 'utf-8');
        $xmlObj->xmlStandalone = true;
        $xmlObj->formatOutput  = true;

        try {
            self::toXML($xmlObj, $data);
            $xmlObj->normalizeDocument();
            $result = $xmlObj->saveXML();
            return  $result;
        } catch( Exception $err )  {
            return  $err;
        }

    }

    /**
     * convert mixed data(array/string) to XML DOM
     *
     * @access  private
     * @param   object  $xmlObj DOMDocument object
     * @param   mixed   $data   Array/String data
     * @param   object  $elObj  DOMElement object
     * @return  void
     */
    private static function toXML(&$xmlObj, $data, $elObj = null)
    {
        if (is_null($elObj)) {
            $elObj = $xmlObj;
        }

        if (is_array($data)) {
            foreach ($data as $index => $mixedElement) {
                if (is_int($index)) {
                    if ($index == 0) {
                        $node = $elObj;
                    } else {
                        $node = $xmlObj->createElement($elObj->tagName);
                        $elObj->parentNode->appendChild($node);
                    }
                } elseif (substr($index, 0, 1) !== '_') {
                    $node = $xmlObj->createElement($index);
                    $elObj->appendChild($node);
                } else {
                    $node = $xmlObj->createAttribute(substr($index, 1));
                    $elObj->appendChild($node);
                }

                self::toXML($xmlObj, $mixedElement, $node);
            }
        } else {
            $elObj->appendChild($xmlObj->createTextNode($data));
        }
    }

}
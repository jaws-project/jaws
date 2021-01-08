<?php
/**
 * Get Alexa rank
 *
 * @category    Scripts
 * @package     Launcher
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2014-2020 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class AlexaRank
{
    /**
     * Saves file
     *
     * @access  public
     * @param   string  $cache_file
     * @param   string  $data
     * @return  mixed   True on Success, PEAR Error on Failure
     */
    function saveFile($cache_file, $data)
    {
        if (!Jaws_FileManagement_File::file_put_contents($cache_file, serialize($data))) {
            return PEAR::raiseError("Fail to save stream with file_put_contents('$cache_file',...).");
        }

        return true;
    }

    /**
     * Loads File
     *
     * @access  public
     * @param   string  $cache_file
     * @return  mixed   data array or PEAR Error on Failure
     */
    function loadFile($cache_file)
    {
        if (false === $data = Jaws_FileManagement_File::file_get_contents($cache_file)) {
            return PEAR::raiseError("Fail to open '$cache_file', not found"); 
        }

        return unserialize($data);
    }

    /**
     * Retrieves URL content
     *
     * @access  public
     * @param   string  $url    url address
     * @return  string  page string data
     */
    function getURL($url)
    {
        require_once PEAR_PATH. 'HTTP/Request.php';
        $httpRequest = new HTTP_Request($url);
        $httpRequest->setMethod(HTTP_REQUEST_METHOD_GET);
        $resRequest  = $httpRequest->sendRequest();
        if (!PEAR::isError($resRequest) && $httpRequest->getResponseCode() == 200) {
            $data = $httpRequest->getResponseBody();
        } else {
            $data = @file_get_contents($url);
        }

        return $data;
    }

    /**
     * Gets Alexa Rank
     *
     * @access  public
     * @param   string  $url
     * @return  array   ranks array
     */
    function getRank($url)
    {
        $ranks = array();
        $rdata = $this->getURL("http://data.alexa.com/data?cli=10&url=". urlencode($url));
        if (!empty($rdata)) {
            $xmlData = simplexml_load_string($rdata, 'SimpleXMLElement', LIBXML_NOERROR);
            if ($xmlData && isset($xmlData->SD[0])) {
                $nodeAttributes = $xmlData->SD[0]->POPULARITY->attributes();
                $ranks['Popularity'] = (string)$nodeAttributes['TEXT'];
                $nodeAttributes = $xmlData->SD[0]->RANK->attributes();
                $ranks['Delta'] = (string)$nodeAttributes['DELTA'];
                $nodeAttributes = $xmlData->SD[0]->COUNTRY->attributes();
                $ranks[(string)$nodeAttributes['NAME']] = (string)$nodeAttributes['RANK'];
            }
        }

        return $ranks;
    }
}

/**
 * Generates Alexa Rank Html display Fragment
 * 
 * @access  public
 * @return  string   html fragment
 */
function AlexaSiteRank()
{
    $cache_dir = ROOT_DATA_PATH . 'launcher' . DIRECTORY_SEPARATOR;
    if (!Jaws_FileManagement_File::mkdir($cache_dir)) {
        return new Jaws_Error(Jaws::t('ERROR_FAILED_CREATING_DIR', $cache_dir),  __FUNCTION__);
    }

    $url = Jaws::getInstance()->getSiteURL('/');
    $file = $cache_dir . 'alexarank_' . md5($url);
    $timedif = time() - (int)Jaws_FileManagement_File::filemtime($file);

    $objAlexaRank = new AlexaRank();
    if ($timedif < 43200) { // a half day
        //cache file is fresh
        $ranks = $objAlexaRank->loadFile($file);
    } else {
        $ranks = $objAlexaRank->getRank($url);
        $objAlexaRank->saveFile($file, $ranks);
    }
    unset($objAlexaRank);

    $result = '';
    foreach ($ranks as $key => $rank) {
        $result.= "<div><label>$key:</label> <span>$rank</span></div>";
    }

    return "<div class=\"gadget launcher alexarank\">$result</div>";
}
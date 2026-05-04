<?php
/**
 * Class to handle the WebSocket
 *
 * @category    WebSocket
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2008-2024 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_WebSocket
{
    /**
     * Network address
     *
     * @access  protected
     * @var     string
     */
    protected $address;

    /**
     * Network port
     *
     * @access  protected
     * @var     int
     */
    protected $port = 2048;

    /**
     * Server socket
     *
     * @access  protected
     * @var     resource
     */
    protected $socket;

    /**
     * socket send timeout
     *
     * @access  protected
     * @var     int
     */
    protected $send_timeout = 5;

    /**
     * socket receive timeout
     *
     * @access  protected
     * @var     int
     */
    protected $receive_timeout = 15;


    /**
     * Constructor
     *
     * @access  protected
     * @param   string  $address    Network address
     * @param   int     $port       Network port
     * @return  void
     */
    protected function __construct($address, $port)
    {
        $this->address = $address;
        $this->port = $port;
    }

    /**
     * Creates the Jaws_WebSocket instance if it doesn't exist else it returns the already created one
     *
     * @access  public
     * @param   string  $address    Network address
     * @param   int     $port       Network port
     * @param   string  $instance   Instance name
     * @return  object  returns the instance of Jaws_WebSocket or Jaws_Error on failure
     */
    static function getInstance($address = '', $port = 0, $instance = 'default')
    {
        if (!extension_loaded('sockets')) {
            return Jaws_Error::raiseError('sockets extension is not available');
        }

        static $objWebSocket = array();
        if (!isset($objWebSocket[$instance])) {
            $calssname = get_called_class();
            $objWebSocket[$instance] = new $calssname($address, $port);
        }

        return $objWebSocket[$instance];
    }


    /**
     * Set send/receive timeouts
     *
     * @access  public
     * @param   int     $send_timeout       send timeout
     * @param   int     $receive_timeout    receive timeout
     * @return  void
     */
    public function setTimeouts($send_timeout = null, $receive_timeout = null)
    {
        $this->send_timeout = is_null($send_timeout)? $this->send_timeout : $send_timeout;
        $this->receive_timeout = is_null($receive_timeout)? $this->receive_timeout : $receive_timeout;
    }

    /**
     * Encodes WebSocket sending data
     *
     * @access  protected
     * @param   string  $data   Sending data
     * @return  string  Encoded data
     */
    protected function encode($data)
    {
        $firstByte = 0x80 | (0x1 & 0x0f);
        $length = strlen($data);
        if($length <= 125) {
            $header = pack('CC', $firstByte, $length);
        } elseif ($length > 125 && $length < 65536) {
            $header = pack('CCS', $firstByte, 126, $length);
        } elseif ($length >= 65536) {
            $header = pack('CCN', $firstByte, 127, $length);
        }

        return $header . $data;
    }


    /**
     * Decodes WebSocket received data
     *
     * @access  protected
     * @param   string  $data   Received data
     * @return  string  Decoded data
     */
    protected function decode($data)
    {
        $result = '';
        if (empty($data) || strlen($data) < 2) {
            return $result;
        }

        $masked  = (bool)(ord($data[1]) >> 7);
        $length  = ord($data[1]) & 127;

        if ($length == 126) {
            if (strlen($data) < 4) {
                return $result;
            }
            $length  = unpack('n', substr($data, 2, 2))[1];
            $dataPos = 4;
        } elseif ($length == 127) {
            if (strlen($data) < 10) {
                return $result;
            }
            // unpack as two 32-bit unsigned ints (big-endian) and combine
            $parts   = unpack('N2', substr($data, 2, 8));
            $length  = ($parts[1] << 32) | $parts[2];
            $dataPos = 10;
        } else {
            $dataPos = 2;
        }

        if ($masked) {
            if (strlen($data) < $dataPos + 4) {
                return $result;
            }
            $mask   = substr($data, $dataPos, 4);
            $data   = substr($data, $dataPos + 4, $length);
            $dlen   = strlen($data);
            for ($i = 0; $i < $dlen; ++$i) {
                $result .= $data[$i] ^ $mask[$i % 4];
            }
        } else {
            $result = substr($data, $dataPos, $length);
        }

        return $result;
    }


    /**
     * Close the socket
     *
     * @access  public
     * @param   resource    $socket socket resource
     * @param   string      $errstr optional error message
     * @return  mixed       True on success or Jaws_Error for socket last error
     */
    public function close($socket = null, $errstr = '')
    {
        if (!empty($socket)) {
            $errno = socket_last_error($socket);
            socket_close($socket);
        } else {
            $errno = socket_last_error();
        }

        if (!empty($errno) || !empty($errstr)) {
            return Jaws_Error::raiseError(empty($errno)? $errstr : socket_strerror($errno));
        }

        return true;
    }

}
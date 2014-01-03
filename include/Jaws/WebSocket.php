<?php
/**
 * Class to handle the WebSocket - server side
 *
 * @category   WebSocket
 * @package    Core
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2013-2014 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_WebSocket
{
    /**
     * Network address
     *
     * @access  private
     * @var     string
     */
    private $address;

    /**
     * Network port
     *
     * @access  private
     * @var     int
     */
    private $port = 2048;

    /**
     * Server socket
     *
     * @access  private
     * @var     resource
     */
    private $srvSock;

    /**
     * All connected client sockets
     *
     * @access  private
     * @var     array
     */
    private $liveSocks;


    /**
     * Constructor
     *
     * @access  private
     * @param   string  $address    Network address
     * @param   int     $port       Network port
     * @return  void
     */
    private function __construct($address, $port)
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
     * @return  object returns the instance
     */
    static function getInstance($address = '', $port = 0)
    {
        static $objWebSocket;
        if (!isset($objWebSocket)) {
            $objWebSocket = new Jaws_WebSocket($address, $port);
        }

        return $objWebSocket;
    }


    /**
     * Listen network port over given address
     *
     * @access  private
     * @return  mixed   True or Jaws_Error
     */
    private function listen()
    {
        if ($this->srvSock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) {
            if (socket_set_option($this->srvSock, SOL_SOCKET, SO_REUSEADDR, 1)) {
                if (socket_bind($this->srvSock, $this->address, $this->port)) {
                    if (socket_listen($this->srvSock)) {
                        return true;
                    }
                }
            }
        }

        socket_close($this->srvSock);
        return Jaws_Error::raiseError(socket_strerror(socket_last_error()));
    }


    /**
     * Runs WebSocket server
     *
     * @access  public
     * @param   mixed   $callback Callback function loaded when data received
     * @return  void
     */
    public function open($callback)
    {
        $result = $this->listen();
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        // no time limit
        set_time_limit(0);
        $readySocks = array();
        $this->liveSocks[intval($this->srvSock)] = $this->srvSock;
        while (!connection_aborted()) {
            $readSocks = array_values($this->liveSocks);
            if (!@socket_select($readSocks, $writeSocks, $exceptSocks, 0)) {
                // do nothing
            }

            // looping all readable sockets
            foreach($readSocks as $sock) {
                if($sock == $this->srvSock){
                    // accepting new connection
                    if($client = socket_accept($this->srvSock)) {
                        $this->liveSocks[intval($client)] = $client;
                    }
                    continue;
                } else {
                    $keySock = intval($sock);
                    // trying receive data
                    if(!socket_recv($sock, $buffer, 4096, 0)) {
                        unset($readySocks[$keySock], $this->liveSocks[$keySock]);
                        socket_close($sock);
                        continue;
                    }

                    // no data
                    if (is_null($buffer)) {
                        continue;
                    }

                    if (!isset($readySocks[$keySock])) {
                        // trying verify client WebSocket header
                        if (false !== $result = $this->verify($buffer)) {
                            $readySocks[intval($sock)] = true;
                            socket_write($sock, $result);
                        } else {
                            unset($readySocks[$keySock], $this->liveSocks[$keySock]);
                            socket_close($sock);
                        }

                        continue;
                    }

                    if (substr($buffer, 0, 18) == "it-deflate-frame\r\n") {
                        $buffer = substr($buffer, strpos($buffer, "\r\n\r\n")+4);
                    }

                    if (empty($buffer)) {
                        continue;
                    }

                    $buffer = $this->decode($buffer);
                    if (is_array($callback)) {
                        $callback[0]->$callback[1]($keySock, $buffer);
                    } else {
                        $callback($keySock, $buffer);
                    }
                }
            }
        }

        socket_close($this->srvSock);
    }


    /**
     * Sends data to the given client
     *
     * @access  public
     * @param   int     $keySock    Socket identifier
     * @param   string  $buffer     Buffer data
     * @return  mixed   True or Jaws_Error
     */
    public function send($keySock, &$buffer)
    {
        if (!socket_write(@$this->liveSocks[$keySock], $this->encode($buffer))) {
            return Jaws_Error::raiseError(socket_strerror(socket_last_error()));
        }

        return true;
    }


    /**
     * Sends data to all clients
     *
     * @access  public
     * @param   string  $buffer Buffer data
     * @return  boll    True
     */
    public function sendAll(&$buffer)
    {
        $encoded_data = $this->encode($buffer);
        foreach($this->liveSocks as $sock) {
            if($sock == $this->srvSock){
                continue;
            }
            if (!socket_write($sock, $encoded_data)) {
                // do nothing
            }
        }

        return true;
    }


    /**
     * Decodes WebSocket received data
     *
     * @access  private
     * @param   string  $data   Received data
     * @return  string  Decoded data
     */
    private function decode($data)
    {
        $length = ord($data[1]) & 127;
        $posMasks = ($length == 126) ? 4 : (($length == 127)? 10 : 2);
        $masks = substr($data, $posMasks, 4);
        $data = substr($data, $posMasks + 4);

        $result = '';
        for ($i = 0; $i < strlen($data); ++$i) {
            $result.= $data[$i] ^ $masks[$i%4];
        }

        return $result;
    }


    /**
     * Encodes WebSocket sending data
     *
     * @access  private
     * @param   string  $data   Sending data
     * @return  string  Encoded data
     */
    private function encode($data)
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
     * Verifies WebSocket received data
     *
     * @access  private
     * @param   string  $buffer Buffer data
     * @return  mixed   Response string if verified otherwise False
     */
    private function verify(&$buffer) {
        // Version
        $version = 0;
        if (preg_match("/Sec-WebSocket-Version: (.*)\r\n/i", $buffer, $match)) {
            $version = $match[1];
        }

        if($version == 13) {
            // Resource
            if (preg_match("/GET (.*) HTTP/i", $buffer, $match)) {
                $resource = $match[1];
            }
            // Host
            if (preg_match("/Host: (.*)\r\n/i"  , $buffer, $match)) {
                $host = $match[1];
            }
            // Origin
            if (preg_match("/Origin: (.*)\r\n/i", $buffer, $match)) {
                $origin = $match[1];
            }
            // Sec-WebSocket-Key
            if (preg_match("/Sec-WebSocket-Key: (.*)\r\n/i", $buffer, $match)) {
                $key = $match[1];
            }
            // Data
            if (preg_match("/\r\n(.*?)\$/", $buffer, $match)) {
                $data = $match[1];
            }

            // Accept key
            $acceptKey = $key.'258EAFA5-E914-47DA-95CA-C5AB0DC85B11';
            $acceptKey = base64_encode(sha1($acceptKey, true));

            return "HTTP/1.1 101 Switching Protocols\r\n".
                "Upgrade: websocket\r\n".
                "Connection: Upgrade\r\n".
                "Sec-WebSocket-Accept: $acceptKey".
                "\r\n\r\n";
        }

        return false;
    }

}
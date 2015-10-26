<?php
/**
 * Class to handle the WebSocket server
 *
 * @category    WebSocket
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2013-2015 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_WebSocket_Server extends Jaws_WebSocket
{
    /**
     * All connected client sockets
     *
     * @access  private
     * @var     array
     */
    private $liveSocks;


    /**
     * Listen network port over given address
     *
     * @access  public
     * @param   mixed   $callback Callback function loaded when data received
     * @return  mixed   True on success or Jaws_Error on failure
     */
    public function open($callback)
    {
        if (!$this->socket = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) {
            return Jaws_Error::raiseError(socket_strerror(socket_last_error()));
        }

        // try to reuse local address if used before
        socket_set_option($this->socket, SOL_SOCKET, SO_REUSEADDR, 1);
        // trying to bind socket
        if (!socket_bind($this->socket, $this->address, $this->port)) {
            return $this->close($this->socket);
        }
        // trying to listen on given address:port
        if (!socket_listen($this->socket)) {
            return $this->close($this->socket);
        }

        // no time limit
        set_time_limit(0);
        $readySocks = array();
        $this->liveSocks[intval($this->socket)] = $this->socket;
        while (!connection_aborted()) {
            $readSocks = array_values($this->liveSocks);
            if (!@socket_select($readSocks, $writeSocks, $exceptSocks, 0)) {
                // do nothing
            }

            // looping all readable sockets
            foreach($readSocks as $sock) {
                if ($sock == $this->socket){
                    // accepting new connection
                    if($client = socket_accept($this->socket)) {
                        $this->liveSocks[intval($client)] = $client;
                    }
                    continue;
                } else {
                    $keySock = intval($sock);
                    // trying receive data
                    if (!socket_recv($sock, $buffer, 4096, 0)) {
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
            usleep(500);
        }

        $this->close($this->socket);
        return true;
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
            if($sock == $this->socket){
                continue;
            }
            if (!socket_write($sock, $encoded_data)) {
                // do nothing
            }
        }

        return true;
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
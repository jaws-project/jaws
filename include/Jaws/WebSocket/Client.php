<?php
/**
 * Class to handle the WebSocket client
 *
 * @category    WebSocket
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2014 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_WebSocket_Client extends Jaws_WebSocket
{
    /**
     * Listen network port over given address
     *
     * @access  public
     * @param   mixed   $callback Callback function loaded when data received
     * @return  mixed   True on success or Jaws_Error on failure
     */
    public function open($path, $origin = false, $callback = null)
    {
        if (!$this->socket = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) {
            return $this->close();
        }

        // set send/receive timeouts
        socket_set_option(
            $this->socket, SOL_SOCKET, SO_RCVTIMEO,
            array('sec' => $this->receive_timeout, 'usec' => 0)
        );
        socket_set_option(
            $this->socket, SOL_SOCKET, SO_SNDTIMEO,
            array('sec' => $this->send_timeout,  'usec' => 0)
        );
        // trying connect to WebSocket server
        if (false === socket_connect($this->socket, $this->address, $this->port)) {
            return $this->close();
        }

        $randomKey = base64_encode(Jaws_Utils::RandomText(16, true, true, true));                
        $header = "GET $path HTTP/1.1\r\n";
        $header.= "Host: {$this->address}:{$this->port}\r\n";
        $header.= "Upgrade: websocket\r\n";
        $header.= "Connection: Upgrade\r\n";
        $header.= "Sec-WebSocket-Key: $randomKey\r\n";
        if (!empty($origin)) {
            $header.= "Sec-WebSocket-Origin: {$origin}\r\n";
        }
        $header.= "Sec-WebSocket-Version: 13\r\n";            

        if (false === socket_write($this->socket, $header)) {
            return $this->close();
        }

        if (false === socket_recv($this->socket, $response, 1024, 0)) {
            return $this->close();
        }

        $expectedKey = $randomKey.'258EAFA5-E914-47DA-95CA-C5AB0DC85B11';
        $expectedKey = base64_encode(sha1($expectedKey, true));

        if (preg_match('#Sec-WebSocket-Accept: (.*)\r\n\r\n$#imU', $response, $matches)) {
            $acceptKey = trim($matches[1]);
            if ($acceptKey === $expectedKey) {
                return true;
            }
        }

        return $this->close(255);
    }


    /**
     * receive data from server
     *
     * @access  public
     * @param   int     $length     receive length
     * @param   int     $flags      socket receive flags
     * @return  mixed   received data or Jaws_Error on failure
     */
    public function recv($length, $flags = 0)
    {
        if (false === socket_recv($this->socket, $buffer, $length, $flags)) {
            return $this->close();
        }

        return $this->decode($buffer);
    }


    /**
     * Sends data to the server
     *
     * @access  public
     * @param   string  $buffer     Buffer data
     * @return  mixed   True or Jaws_Error
     */
    public function send(&$buffer)
    {
        if (false === socket_write($this->socket, $this->encode($buffer))) {
            return $this->close();
        }

        return true;
    }

}
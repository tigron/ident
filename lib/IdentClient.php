<?php
/**
 * RFC1413 IDENT client class
 *
 * This is more or less a port of the PEAR Net_Ident class, by Ondrej Jombik and
 * Gavin Brown. See http://pear.php.net/package/Net_Ident
 *
 * @author Gerry Demaret <gerry@tigron.be>
 */

namespace Tigron\Ident;

class IdentClient {

	/**
	 * @var int $timeout
	 * @access private
	 */
	private $timeout = 10;

	/**
	 * @var int $ident_port
	 * @access private
	 */
	private $ident_port = 113;

	/**
	 * @var int $local_port
	 * @access private
	 */
	private $local_port = null;

	/**
	 * @var int $remote_port
	 * @access private
	 */
	private $remote_port = null;

	/**
	 * @var int $remote_address
	 * @access private
	 */
	private $remote_address = null;

	/**
	 * @var int $result
	 * @access private
	 */
	private $result = null;

	/**
	 * Constructor
	 *
	 * @param string $remote_address
	 * @param int $remote_port
	 * @param int $local_port
	 * @param int $ident_port
	 * @param int $timeout
	 * @access public
	 */
	public function __construct($remote_address = null, $remote_port = null, $local_port = null, $ident_port = null, $timeout = null) {
		$this->setRemoteAddress($remote_address);
		$this->setRemotePort($remote_port);
		$this->setLocalPort($local_port);
		$this->setIdentPort($ident_port);
		$this->setTimeout($timeout);
	}

	/**
	 * Sets the remote host address (IP or hostname)
	 *
	 * Defaults to the value of $_SERVER['REMOTE_ADDR']
	 *
	 * @param string $remote_address
	 * @access public
	 */
	public function setRemoteAddress($remote_address) {
		$remote_address === null && $remote_address = $_SERVER['REMOTE_ADDR'];
		$this->remote_address = $remote_address;
	}

	/**
	 * Sets the remote port
	 *
	 * Defaults to the value of $_SERVER['REMOTE_PORT']
	 *
	 * @param int $remote_port
	 * @access public
	 */
	public function setRemotePort($remote_port) {
		$remote_port === null && $remote_port = $_SERVER['REMOTE_PORT'];
		$this->remote_port = intval($remote_port);
	}

	/**
	 * Sets the local port
	 *
	 * Defaults to the value of $_SERVER['SERVER_PORT']
	 *
	 * @param int $local_port
	 * @access public
	 */
	public function setLocalPort($local_port) {
		$local_port === null && $local_port = $_SERVER['SERVER_PORT'];
		$this->local_port = intval($local_port);
	}

	/**
	 * Sets the ident port
	 *
	 * Defaults to 113
	 *
	 * @param int $ident_port
	 * @access public
	 */
	public function setIdentPort($ident_port) {
		$ident_port === null && $ident_port = 113;
		$this->ident_port = intval($ident_port);
	}

	/**
	 * Sets the socket timeout
	 *
	 * Defaults to 10
	 *
	 * @param int $timeout
	 * @access public
	 */
	public function setTimeout($timeout) {
		$timeout === null && $timeout = 10;
		$this->timeout = $timeout;
	}


	/**
	 * Returns ident username
	 *
	 * @return mixed PEAR_Error on connection error
	 * @access public
	 */
	public function getUser() {
		if ($this->result === null && $this->query() === false) {
			return false;
		}

		return $this->result['username'];
	}

	/**
	 * Returns ident operating system type
	 *
	 * @return mixed PEAR_Error on connection error / false boolean on ident protocol error / operating system type string on success
	 * @access public
	 */
	public function getOsType() {
		if ($this->result === null && $this->query() === false) {
			return false;
		}

		return $this->result['os_type'];
	}

	/**
	 * Performs network socket ident query
	 *
	 * @return boolean true if no error occured, false otherwise
	 * @access public
	 */
	private function query() {
		$this->result = null;

		$socket = @fsockopen($this->remote_address, $this->ident_port, $errno, $errstr,	$this->timeout);

		if ($socket === false) {
			return;
		}

		$line = $this->remote_port . ',' . $this->local_port . "\r\n";
		@fwrite($socket, $line);
		$line = @fgets($socket, 1000); // 1000 octets according to RFC 1413
		fclose($socket);

		$response = $this->parseIdentResponse($line);
		if ($response !== false) {
			$this->result = $response;
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Parses response from an ident server and sets internal data structures
	 * with ident username and ident operating system type
	 *
	 * @param string $string ident server response
	 * @return mixed parsed result if no ident protocol error had occured, false otherwise
	 * @access private
	 */
	private function parseIdentResponse($string) {
		$result = [];
		$array = explode(':', $string, 4);

		if (count($array) > 1 && ! strcasecmp(trim($array[1]), 'USERID')) {
			isset($array[2]) && $result['os_type'] = trim($array[2]);
			isset($array[3]) && $result['username'] = trim($array[3]);
			return $result;
		}

		return false;
	}
}

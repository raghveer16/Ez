<?php

namespace EzAd\Util;

class FTP
{
	const CRLF = "\r\n";

	private $socket;
	private $pasvSocket;
	
	private $host;
	private $user;
	private $pass;
	private $port;

	public $timeout = 30;
	public $error;
	public $debug = false;

	private $mode;

	public function __construct($host, $user, $pass, $port = 21)
	{
		$this->host = $host;
		$this->user = $user;
		$this->pass = $pass;
		$this->port = $port;
	}

	public function connect()
	{
		$socket = fsockopen($this->host, $this->port, $errno, $errstr, $this->timeout);
		if ( !$socket ) {
			return false;
		}
		$this->socket = $socket;

		$resp = $this->read();
		if ( !$resp || $resp[0] != 220 ) {
			return false;
		}

		if ( !$this->write('USER ' . $this->user, 331) ) {
			return false;
		}

		if ( !$this->write('PASS ' . $this->pass, 230) ) {
			return false;
		}

		return true;
	}

	public function write($cmd, $expect)
	{
		if ( $this->debug ) {
			echo "> $cmd\n";
		}

		$status = fputs($this->socket, $cmd . self::CRLF);
		if ( $status === false ) {
			if ( $this->debug ) {
				echo "status === false\n";
			}
			return false;
		}

		$resp = $this->read();
		if ( $resp === false ) {
			if ( $this->debug ) {
				echo "resp === false\n";
			}
			return false;
		}

        $expect = (array) $expect;
		if ( !in_array($resp[0], $expect) ) {
			$this->error = $resp[1];
			return false;
		}

		return $resp;
	}

	public function read()
	{
		$msg = '';
		$loop = true;
		$regex = "/^([0-9]{3})(-(.*[".self::CRLF."]{1,2})+\\1)? [^".self::CRLF."]+[".self::CRLF."]{1,2}$/";

		do {
			$tmp = fgets($this->socket, 512);
			if ( $this->debug ) {
				echo "< $tmp\n";
			}
			if ( $tmp === false ) {
				$loop = false;
			} else {
				$msg .= $tmp;
				if ( preg_match($regex, $msg, $m) ) {
					$loop = false;
				}
			}
		} while ( $loop );

		if ( isset($m) ) {
			return array((int) $m[1], $msg);
		}
		return false;
	}

	public function readPasvData()
	{
		if ( $this->pasvSocket === null ) {
			return '';
		}

		$out = '';
		while ( !feof($this->pasvSocket) ) {
			$block = fread($this->pasvSocket, 4096);
			$out .= $block;
		}
		return $out;
	}

	public function writePasvData($str)
	{
		if ( $this->pasvSocket === null ) {
			return '';
		}

		do {
			if ( ($ret = fwrite($this->pasvSocket, $str)) === false ) {
				return false;
			}
			$str = substr($str, $ret);
		} while ( !empty($str) );
		return true;
	}

	public function nlist()
	{
		$this->enterPasvMode();

		$this->write('NLST', 150);
		$data = $this->readPasvData();
		$lines = preg_split('/\r?\n/', $data, -1, PREG_SPLIT_NO_EMPTY);
		$this->read();

		$this->exitPasvMode();

		return $lines;
	}

	public function putString($remoteFile, $data)
	{
		$ok = $this->enterPasvMode();
        if ( !$ok ) {
            return false;
        }

		$ok = $this->write('STOR ' . $remoteFile, [150, 125]);
        if ( !$ok ) {
            return false;
        }

		$ok = $this->writePasvData($data);
        if ( !$ok ) {
            return false;
        }

		$this->exitPasvMode();
		$resp = $this->read();

        if ( !$resp || $resp[0] != 226 ) {
            return false;
        }

		return true;
	}

	private function setMode($mode)
	{
		if ( $mode == FTP_BINARY ) {
			$mode = 'I';
		} else {
			$mode = 'A';
		}

		if ( $mode != $this->mode ) {
			$this->mode = $mode;
			$this->write('TYPE ' . $mode, 200);
		}
	}

	private function enterPasvMode()
	{
		$this->setMode(FTP_BINARY);

		$resp = $this->write('PASV', 227);
		if ( $resp ) {
			$p1 = strpos($resp[1], '(');
			$p2 = strpos($resp[1], ')', $p1);
			$data = substr($resp[1], $p1 + 1, $p2 - $p1 - 1);
			$parts = explode(',', $data);
			
			if ( $parts[0] == 192 || $parts[0] == 10 ) {
				$ip = $this->host;
			} else {
				$ip = implode('.', array_slice($parts, 0, 4));
			}
			$port = $parts[4] * 256 + $parts[5];
			$socket = fsockopen($ip, $port, $errno, $errstr, $this->timeout);
			if ( $socket ) {
				$this->pasvSocket = $socket;
				return true;
			}
		}

		return false;
	}

	private function exitPasvMode()
	{
		fclose($this->pasvSocket);
	}
}


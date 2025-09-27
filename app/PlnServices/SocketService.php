<?php

namespace App\PlnServices;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Response;

class SocketService
{
    public function __construct()
    {

    }

    public function runSocketbaru($jenis_layanan, $message){
    	$pln_config = Config::get('app.pln');
		$ipaddress = $pln_config['ip_address'];
		$port = "";

		switch ($jenis_layanan) {
			case 'POSTPAID':
				$port = $pln_config['postpaid_port'];
				break;
			case 'PREPAID':
				$port = $pln_config['prepaid_port'];
				break;
			case 'NONTAGLIS':
				$port = $pln_config['nontaglis_port'];
				break;
		}

		$hasil['layanan'] = $jenis_layanan;
		$hasil['ip_address'] = $ipaddress;
		$hasil['port'] = $port;

		$hasil = array();

		$response = "";

		$fp = fsockopen($ipaddress, $port, $errno, $errstr, 30);

		if (!$fp) {
		    //echo "$errstr ($errno)<br />\n";
		    $hasil['socket_connect'] = false;
		    $hasil['socket_connect_message'] = "$errstr ($errno)";
		} else {

			$hasil['socket_connect'] = true;
		    $hasil['socket_connect_message'] = "socket connect berhasil";

		    fwrite($fp, $message);
		    while (!feof($fp)) {
		        $response .= fgets($fp, 128);
		    }

		    fclose($fp);

		    $hasil['response'] = $response;
		}

		dd($hasil);

		return $hasil;
    }

    public function runSocket($jenis_layanan, $message){

		$pln_config = Config::get('app.pln');
		$ipaddress = $pln_config['ip_address'];
		$port = "";

		switch ($jenis_layanan) {
			case 'POSTPAID':
				$port = $pln_config['postpaid_port'];
				break;
			case 'PREPAID':
				$port = $pln_config['prepaid_port'];
				break;
			case 'NONTAGLIS':
				$port = $pln_config['nontaglis_port'];
				break;
		}

		$hasil['layanan'] = $jenis_layanan;
		$hasil['ip_address'] = $ipaddress;
		$hasil['port'] = $port;

		$hasil = array();

		$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

		if ($socket === false) {
			$hasil['socket_create'] = false;
		    $hasil['socket_create_message'] = "socket_create() failed: reason: " . socket_strerror(socket_last_error());
		} else {
			$hasil['socket_create'] = true;
		    $hasil['socket_create_message'] = "socket create berhasil";
		}

		//socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, array('sec' => 5, 'usec' => 0));
		//socket_set_option($socket, SOL_SOCKET, SO_SNDTIMEO, array('sec' => 5, 'usec' => 0));

		$result = socket_connect($socket, $ipaddress, $port);
		//socket_set_timeout($result, 30);

		if ($result === false) {
			$hasil['socket_connect'] = false;
			$hasil['socket_connect_message'] = "socket_connect() failed.\nReason: ($result) " . socket_strerror(socket_last_error($socket));
		} else {
		    $hasil['socket_connect'] = true;
		    $hasil['socket_connect_message'] = "socket connect berhasil";
		}

		$response = "";
		//$message = "2100403000418081000005995010000001197602017040710511760210745100170745120170000000000000048019ST145S3           2212000023331";
		//$message = "2100403000418081000005995010000001197602017030110511760210745100170745102160000000000000048019ST145S3541120114555";

		socket_write($socket, $message, strlen($message));

		$response = socket_read($socket, 2048);
		$response = substr($response, 0, strlen($response)-1);

		//dd($response);

		$hasil['response'] = $response;

		socket_close($socket);

		return $hasil;
	}

    public function runSocketold($jenis_layanan, $message){

		$pln_config = Config::get('app.pln');
		$ipaddress = $pln_config['ip_address'];
		$port = "";
		$timeout = 110;

		switch ($jenis_layanan) {
			case 'POSTPAID':
				$port = $pln_config['postpaid_port'];
				break;
			case 'PREPAID':
				$port = $pln_config['prepaid_port'];
				break;
			case 'NONTAGLIS':
				$port = $pln_config['nontaglis_port'];
				break;
		}

		$hasil['layanan'] = $jenis_layanan;
		$hasil['ip_address'] = $ipaddress;
		$hasil['port'] = $port;

		$hasil = array();

		$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		socket_set_nonblock($socket);

		$time = time();

		$response = "";

		if ($socket === false) {
			$hasil['socket_create'] = false;
		    $hasil['socket_create_message'] = "socket_create() failed: reason: " . socket_strerror(socket_last_error());
		} else {
			$hasil['socket_create'] = true;
		    $hasil['socket_create_message'] = "socket create berhasil";
		}

		// loop until a connection is gained or timeout reached
		while (!@socket_connect($socket, $ipaddress, $port)) {
		    $err = socket_last_error($socket);

		    // success!
		    if($err === 56) {
		        $hasil['socket_connect'] = true;
		    	$hasil['socket_connect_message'] = "socket connect berhasil";

		    	dd("connect");

				socket_write($socket, $message, strlen($message));

				$response = socket_read($socket, 2048);
				$response = substr($response, 0, strlen($response)-1);

				//dd($response);

				$hasil['response'] = $response;

		        break;
		    }

		    // if timeout reaches then call exit();
		    if ((time() - $time) >= $timeout) {
		    	$hasil['socket_connect'] = false;
		    	$hasil['socket_connect_message'] = "socket timeout.";
		        socket_close($socket);
		        exit();
		    }

		    // sleep for a bit
		    usleep(250000);
		}

		// re-block the socket if needed
		socket_set_block($socket);

		dd($hasil);

		return $hasil;
	}

}
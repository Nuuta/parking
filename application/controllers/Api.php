<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
*  API call
*/

Class Api extends CI_Controller
{
	private $_request;
	private $_contentType  = "application/json";
	private $_code         = '200';
	private $_postData     = [
		'immatriculation_plate',
	];

	function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->_request = $this->_getRequestMethod();
	}

	public function index()
	{
		$this->load->view('api/index');
	}

	public function root($method, $id, $key, $data = NULL)
	{
		$this->_auth($id, $key);
		$this->{'_' . $method}($data);
	}

	private function _auth($id, $key)
	{
		$query = "SELECT * FROM user WHERE id=" . $id . ' AND key_encryption="' . $key . '"';
		$obj   = $this->db->query($query);
		$user  = $obj->row();
		if (! $user) {
			$this->_code = 401;
			$this->_response(['message' => 'Error: User not authentify!']);
			exit;
		}
	}

	private function _car($id = NULL)
	{
		$response = [];
		if ($this->_request == 'POST') {
			$response = $this->_post();
		} else if ($this->_request == 'GET') {
			$response['cars'] = $this->_get('car', $id);
			$count = count($response['cars']);
			if (! $id) {
				$response['count'] = $count;
			} else if ($count == 0) {
				unset($response['cars']);
				$response['message'] = 'This ID does not exist!';
				$this->_code = '500';
			}

		} else if ($this->_request == 'DELETE') {
			$response = $this->_delete($id);
		}
		$this->_response($response);
	}

	private function _spot($id = NULL)
	{
		$response = [];
		if ($this->_request == 'GET') {
			if (! $id) {
				$query = 'SELECT * FROM spot WHERE is_used = 0';
				$obj   = $this->db->query($query);
				$count = 0;
				foreach ($obj->result() as $row) {
					$count++;
				}

				$response['nbSpotsFree'] = $count;
			}
			$response['spots'] = $this->_get('spot', $id);
			$this->_response($response);
		}
	}

	private function _get($table = 'car', $id = NULL)
	{
		$result = [];

		$query = 'SELECT * FROM ' . $table;
		$query .= $id ? (' WHERE id=' . $id) : '';
		$res = $this->db->query($query);

		foreach ($res->result() as $row) {
			$result[] = $row;
		}
		return $result;
	}

	private function _post()
	{
		$data = $this->input->post();
		$response = [];

		foreach ($data as $key => $value) {
			if (! in_array($key, $this->_postData)) {
				$this->_code = 500;
				$response['message'] = 'Wrong data!';
				return $response;
			}
		}

		$query    = 'SELECT * FROM spot WHERE is_used IS FALSE';
		$obj      = $this->db->query($query);
		$count    = $obj->conn_id->field_count;

		if ($count < 1) {
			$this->_code = 500;
			$response['message'] = 'No more parking spot!';
		} else {
			$spot = $obj->row();

			$query = 'UPDATE spot SET is_used = 1 WHERE id = ' . $spot->id;
			$res = $this->db->simple_query($query);

			if ($res) {
				$query = 'INSERT INTO car (immatriculation_plate, spot_id)';
				$query .= ' VALUES("' . trim($data['immatriculation_plate']) . '", ' . $spot->id . ')';
				$res = $this->db->simple_query($query);
				$this->code = 201;
				$response['message'] = 'The car has been place on spot ' . $spot->id;
			} else {
				$this->_code = 500;
				$response['message'] = 'Error on reserving the spot!';
			}

		}

		return $response;
	}

	private function _delete($id)
	{
		$query = 'SELECT spot_id FROM car WHERE id = ' . $id;
		$obj = $this->db->query($query);
		$row = $obj->row();
		if ($row) {
			$query = 'UPDATE spot SET is_used = FALSE WHERE id = ' . $row->spot_id;
			$res   = $this->db->simple_query($query);
			$query = 'DELETE FROM car WHERE id = ' . $id;
			$res   = $this->db->simple_query($query);

			if ($res) {
				$response['message'] = 'Car has been delete and the spot has been released!';
			}			
		} else {
			$response['message'] = 'This car does not exist!';
			$this->_code = 500;
		}
		return $response;
	}

	private function _response($data)
	{
		$this->_setHeaders();
		echo json_encode([
			'response' => $this->_code,
			'rows' => $data
		]);
	}

	private function _getRequestMethod()
	{
		return $_SERVER['REQUEST_METHOD'];
	}

	private function _setHeaders()
	{
		header("HTTP/1.1 " . $this->_code . " " . $this->_getStatusMessage());
		header("Content-Type:" . $this->_contentType);
	}

	private function _getStatusMessage(){
		$status = array(
					100 => 'Continue',  
					101 => 'Switching Protocols',  
					200 => 'OK',
					201 => 'Created',  
					202 => 'Accepted',  
					203 => 'Non-Authoritative Information',  
					204 => 'No Content',  
					205 => 'Reset Content',  
					206 => 'Partial Content',  
					300 => 'Multiple Choices',  
					301 => 'Moved Permanently',  
					302 => 'Found',  
					303 => 'See Other',  
					304 => 'Not Modified',  
					305 => 'Use Proxy',  
					306 => '(Unused)',  
					307 => 'Temporary Redirect',  
					400 => 'Bad Request',  
					401 => 'Unauthorized',  
					402 => 'Payment Required',  
					403 => 'Forbidden',  
					404 => 'Not Found',  
					405 => 'Method Not Allowed',  
					406 => 'Not Acceptable',  
					407 => 'Proxy Authentication Required',  
					408 => 'Request Timeout',  
					409 => 'Conflict',  
					410 => 'Gone',  
					411 => 'Length Required',  
					412 => 'Precondition Failed',  
					413 => 'Request Entity Too Large',  
					414 => 'Request-URI Too Long',  
					415 => 'Unsupported Media Type',  
					416 => 'Requested Range Not Satisfiable',  
					417 => 'Expectation Failed',  
					500 => 'Internal Server Error',  
					501 => 'Not Implemented',  
					502 => 'Bad Gateway',  
					503 => 'Service Unavailable',  
					504 => 'Gateway Timeout',  
					505 => 'HTTP Version Not Supported');
		return ($status[$this->_code]) ? $status[$this->_code] : $status[500];
	}

}
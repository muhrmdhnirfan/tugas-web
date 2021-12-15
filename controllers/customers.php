<?php
defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';
require "vendor\autoload.php";

use Restserver\Libraries\REST_Controller;
use \Firebase\JWT\JWT;

class Customers extends REST_Controller
{

    function __construct($config = 'rest')
    {
        parent::__construct($config);
    }

    //Menampilkan data
    public function index_get()
    {
        $authHeader = $this->input->get_request_header('Authorization');
        $arr = explode(" ", $authHeader);
        $jwt = isset($arr[1]) ? $arr[1] : "";
        $secretkey = base64_encode("difficult");
        if ($jwt) {
            try {
                $decode = JWT::decode($jwt, $secretkey, array('HS256'));
                $id = $this->get('id');

                if ($id == '') {
                    $data = $this->db->get('customers')->result();
                } else {
                    $this->db->where("id", $id);
                    $data = $this->db->get('customers')->result();
                }
                $result = [
                    "took" => $_SERVER["REQUEST_TIME_FLOAT"],
                    "code" => 200,
                    "message" => "Response successfully",
                    "data" => $data
                ];
                //header('Access-Control-Allow-Origin: *'); 
                //header('Access-Control-Allow-Methods: GET');
                $this->response($result, 200);
            } catch (Exception $e) {
                $result = [
                    "took" => $_SERVER["REQUEST_TIME_FLOAT"],
                    "code" => 401,
                    "message" => "Access denied",
                    "data" => null
                ];
                $this->response($result, 401);
            }
        } else {
            $result = [
                "took" => $_SERVER["REQUEST_TIME_FLOAT"],
                "code" => 402,
                "message" => "Access denied",
                "data" => null
            ];
            $this->response($result, 402);
        }
    }

    //Menambah data
    public function index_post()
    {
        $data = array(
            'id' => $this->post('id'),
            'name' => $this->post('name'),
            'email' => $this->post('email'),
            'phone' => $this->post('phone'),
            'address' => $this->post('address'),

        );
        $this->db->where("id", $this->post('id'));
        $this->db->where("name", $this->post('name'));
        $check = $this->db->get('customers')->num_rows();
        if ($check == 0) :
            $insert = $this->db->insert('customers', $data);
            if ($insert) {
                $result = [
                    "took" => $_SERVER["REQUEST_TIME_FLOAT"],
                    "code" => 201,
                    "message" => "Data has successfully added",
                    "data" => $data
                ];
                $this->response($result, 201);
            } else {
                $result = [
                    "took" => $_SERVER["REQUEST_TIME_FLOAT"],
                    "code" => 502,
                    "message" => "Failed adding data",
                    "data" => null
                ];
                $this->response($result, 502);
            }
        else :
            $result = [
                "took" => $_SERVER["REQUEST_TIME_FLOAT"],
                "code" => 304,
                "message" => "Data already added",
                "data" => $data
            ];
            $this->response($result, 304);
        endif;
    }

    //Merubah data
    public function index_put()
    {
        $id = $this->put('id');
        $data = array(

            'name' => $this->put('name'),
            'email' => $this->put('email'),
            'phone' => $this->put('phone'),
            'address' => $this->put('address'),

        );
        $this->db->where('id', $id);
        $update = $this->db->update('customers', $data);
        if ($update) {
            $result = [
                "took" => $_SERVER["REQUEST_TIME_FLOAT"],
                "code" => 200,
                "message" => "Data Updated",
                "data" => $data

            ];
            $this->response($result, 200);
        } else {
            $this->response(array('status' => 'fail', 502));
        }
    }

    //Menghapus data
    public function index_delete()
    {
        $id = $this->delete('id');
        $this->db->where('id', $id);
        $delete = $this->db->delete('customers');
        if ($delete) {
            $this->response(array('status' => 'data has been deleted'), 201);
        } else {
            $this->response(array('status' => 'fail', 502));
        }
    }
}

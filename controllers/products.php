<?php
defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class Products extends REST_Controller
{

    function __construct($config = 'rest')
    {
        parent::__construct($config);
        $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
    }

    //Menampilkan data
    public function index_get()
    {

        $id = $this->get('productCode');
        $products = [];
        if ($id == '') {
            $data = $this->db->get('products')->result();
            foreach ($data as $row => $key) :
                $products[] = [
                    "productCode" => $key->productCode,
                    "productName" => $key->productName,
                    "_links" => [(object)[
                        "href" => "orders/($key->productCode)",
                        "rel" => "orders",
                        "type" => "GET"
                    ]],
                    "price" => $key->price,
                    "quantityInStock" => $key->quantityInStock,
                    "productBest" => $key->productBest,
                    "productExpired" => $key->productExpired,
                ];
            endforeach;
        }
        $etag = hash('sha256', $data[0]->LastUpdate);
        $this->cache->save($etag, $products, 300);
        $this->output->set_header('ETag:' . $etag);
        $this->output->set_header('Cache-Control: must-revalidate');
        if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && $_SERVER['HTTP_IF_NONE_MATCH'] == $etag) {
            $this->output->set_header('HTTP/1.1 304 Not Modified');
        } else {
            $result = [
                "took" => $_SERVER["REQUEST_TIME_FLOAT"],
                "code" => 200,
                "message" => "Response successfully",
                "data" => $products
            ];
            $this->response($result, 200);
        }
    }
}

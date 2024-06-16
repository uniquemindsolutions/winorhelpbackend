<?php

require APPPATH.'libraries/REST_Controller.php';

class Wallettoperation extends REST_Controller{

  public function __construct(){

    parent::__construct();

    // Set headers for CORS
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Headers: *');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE');


    //load database
    $this->load->database();
    $this->load->model('User_model');
    $this->load->helper('form');
    $this->load->library(array("form_validation", "email"));
    $this->load->helper("security");
    $this->load->helper('url');
    $this->load->library('session');
  }

  public function wallet_withdraw_post() {

   
    // if ($this->form_validation->run() === FALSE) {
    //   $this->response([
    //       'status' => FALSE,
    //       'message' => validation_errors()
    //   ], REST_Controller::HTTP_BAD_REQUEST);
    // } else {

    
      $data = [
        'user_id' => $this->post('user_id'),
        'trans_type' => $this->post('trans_type'),
        'amount' => $this->post('amount')
      ];

      if ($this->User_model->debitinserdata($data)) {
        $this->response([
            'status' => TRUE,
            'message' => 'Withdraw request successfully completed.'
        ], REST_Controller::HTTP_OK);
      } else {
        $this->response([
            'status' => FALSE,
            'message' => 'Error in withdraw'
        ], REST_Controller::HTTP_CONFLICT);
      }
    //}
  }

  public function users_getwallet_get() {

    // $page = $this->get('page');
    // $limit = $this->get('limit');
    // $userid = $this->get('userid');

    $page = $_GET['page'];
    $limit = $_GET['limit'];
    $userid = $_GET['user_id'];

    if (!$page) {
        $page = 1;
    }
    if (!$limit) {
        $limit = 10;
    }

    $offset = ($page - 1) * $limit;

    $users = $this->User_model->get_user_walethist($limit, $offset,$userid);
    // echo $this->db->last_query();exit;
    //$total_users = $this->User_model->get_total_users();

    if ($users) {
        $this->response([
            'status' => TRUE,
            'message' => 'Users retrieved successfully.',
            'data' => $users,
            'total_users' => $users,
            'page' => $page,
            'limit' => $limit
        ], REST_Controller::HTTP_OK);
    } else {
        $this->response([
            'status' => FALSE,
            'message' => 'No users found.'
        ], REST_Controller::HTTP_NOT_FOUND);
    }
}

 

}

 ?>

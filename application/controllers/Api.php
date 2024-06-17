<?php

require APPPATH.'libraries/REST_Controller.php';

class Auth extends REST_Controller{

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
  }

  /*
    INSERT: POST REQUEST TYPE
    UPDATE: PUT REQUEST TYPE
    DELETE: DELETE REQUEST TYPE
    LIST: Get REQUEST TYPE
  */

  public function test_get(){
    $this->response(array(
      "status" => TRUE,
      "message" => "All fields are needed"
    ) , REST_Controller::HTTP_OK);
  }

 
  public function create_room_post() {
    // $this->form_validation->set_rules('date', 'Date', 'required');
    // $this->form_validation->set_rules('entryFee', 'Entry Fee', 'required|numeric');
    // $this->form_validation->set_rules('totalParticipants', 'Total Participants', 'required|numeric');
    // $this->form_validation->set_rules('winningAmount', 'Winning Amount', 'required|numeric');
    // $this->form_validation->set_rules('viewDetails', 'View Details', 'required|valid_url');

    if ($this->form_validation->run() === FALSE) {
      $this->response([
          'status' => FALSE,
          'message' => validation_errors()
      ], REST_Controller::HTTP_BAD_REQUEST);
    } else {

      // Fetch the last room ID
      $last_room = $this->User_model->get_last_room_id();

      // Generate new room ID
      if ($last_room) {
          $last_id = intval(substr($last_room->room_id, 2));
          $new_id = 'RM' . str_pad($last_id + 1, 6, '0', STR_PAD_LEFT);
      } else {
          $new_id = 'RM000001';
      }

      $data = [
        'roomId' => $new_id,
        'entryFee' => $this->post('entryFee'),
        'totalParticipants' => $this->post('totalParticipants'),
        'winningAmount' => $this->post('winningAmount'),
        'startDate' => $this->post('startDate'),
        'endDate' => $this->post('endDate'),
        'startTime' => $this->post('startTime'),
        'endTime' => $this->post('endTime'),
        'createdAt' => date(),
      ];

      if ($this->User_model->create_room($data)) {
        $this->response([
            'status' => TRUE,
            'message' => 'Room created successfully.'
        ], REST_Controller::HTTP_OK);
      } else {
        $this->response([
            'status' => FALSE,
            'message' => 'Room ID already exists.'
        ], REST_Controller::HTTP_CONFLICT);
      }
    }
  }


  // API method to fetch users with pagination
  public function users_get() {
      $page = $this->get('page');
      $limit = $this->get('limit');

      if (!$page) {
          $page = 1;
      }
      if (!$limit) {
          $limit = 10;
      }

      $offset = ($page - 1) * $limit;

      $users = $this->User_model->get_users($limit, $offset);
      $total_users = $this->User_model->get_total_users();

      if ($users) {
          $this->response([
              'status' => TRUE,
              'message' => 'Users retrieved successfully.',
              'data' => $users,
              'total_users' => $total_users,
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

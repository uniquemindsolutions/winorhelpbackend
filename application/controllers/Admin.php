<?php
  require APPPATH.'libraries/REST_Controller.php';

class Admin extends REST_Controller{

  public function __construct(){
    parent::__construct();

    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json');
    header('Access-Control-Allow-Headers: *');
    header('Access-Control-Allow-Credentials', 'true');
    header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE, OPTIONS");
    // header("Access-Control-Allow-Headers: *");

    //load database
    $this->load->database();
    $this->load->model('User_model');
    $this->load->helper('form');
    $this->load->library(array("form_validation", "email"));
    $this->load->helper("security");
    $this->load->helper('url');
  }

    public function index_options() {
        return $this->response(NULL, REST_Controller::HTTP_OK);
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
     $last_room = $this->User_model->get_last_room_id();

      // Generate new room ID
      if ($last_room) {
          $last_id = intval(substr($last_room->roomId, 2));
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
        'endTime' => $this->post('endTime')
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

  // API method to fetch users with pagination
  public function roomList_get() {
    $page = $this->get('page');
    $limit = $this->get('limit');

    if (!$page) {
        $page = 1;
    }
    if (!$limit) {
        $limit = 10;
    }

    $offset = ($page - 1) * $limit;

    $rooms = $this->User_model->get_rooms($limit, $offset);
    $total_rooms = $this->User_model->get_total_rooms();

    if ($rooms) {
        $this->response([
            'status' => TRUE,
            'message' => 'Rooms retrieved successfully.',
            'data' => $rooms,
            'totalRooms' => $total_rooms,
            'page' => $page,
            'limit' => $limit
        ], REST_Controller::HTTP_OK);
    } else {
        $this->response([
            'status' => FALSE,
            'message' => 'No rooms found.'
        ], REST_Controller::HTTP_NOT_FOUND);
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



    public function updateStatus_post($id) {
        $status = $this ->post('isActive');
        if($status >=0){
            if ($this->User_model->update_room_status($id, $status)) {
                echo json_encode(array('status' => TRUE,'message' => 'Room status updated successfully'));
            } else {
                echo json_encode(array('status' => FALSE,'error' => 'Failed to update room status'));
            }
        }else{
            echo json_encode(array('status' => FALSE,'error' => 'The isActive field is required'));
        }
        
    }

    public function delete_put($id) {
        if (empty($id)) {
            $response = ['status' => FALSE, 'message' => 'Room ID is required'];
            $this->output->set_content_type('application/json')->set_status_header(400)->set_output(json_encode($response));
            return;
        }

        $deleted = $this->User_model->delete_room($id);

        if ($deleted) {
            $response = ['status' => TRUE, 'message' => 'Room deleted successfully'];
            $this->output->set_content_type('application/json')->set_status_header(200)->set_output(json_encode($response));
        } else {
            $response = ['status' => FALSE, 'message' => 'Failed to delete room or room not found'];
            $this->output->set_content_type('application/json')->set_status_header(404)->set_output(json_encode($response));
        }
    }


    public function userRoomList_get() {
        $startDate = $this->get('startDate');
        $startTime = $this->get('startTime');
        $endDate = $this->get('endDate');
        $endTime = $this->get('endTime');

        if (empty($startDate) || empty($startTime) || empty($endDate) || empty($endTime)) {
            $this->response([
                'status' => FALSE,
                'message' => 'Missing required parameters'
            ], REST_Controller::HTTP_BAD_REQUEST);
        } else {
            $rooms = $this->User_model->get_rooms_today($startDate, $startTime, $endDate, $endTime);
            if (!empty($rooms)) {
                $this->response($rooms, REST_Controller::HTTP_OK);
            } else {
                $this->response([
                    'status' => FALSE,
                    'message' => 'No rooms found'
                ], REST_Controller::HTTP_NOT_FOUND);
            }
        }
    }



}

 ?>
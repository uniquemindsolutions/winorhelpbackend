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

        $lotteryDate = $this->post('lotteryDate');
        $lotteryDate = date('Y-m-d', strtotime($lotteryDate));
        $time=$this->post('lotteryTime');
        $date = $lotteryDate . ' ' . $time;
        $lotteryDateTime = date("Y-m-d H:i:s", strtotime($date));

        $data = [
            'roomId' => $new_id,
            'entryFee' => $this->post('entryFee'),
            'startDate' => $this->post('startDate'),
            'endDate' => $this->post('endDate'),
            'startTime' => $this->post('startTime'),
            'endTime' => $this->post('endTime'),
            'winningAmount' => $this->post('winningAmount'),
            'winingPercentageInfo' => json_encode($this->post('winingPercentageInfo')),
            'latter_datetime' => $lotteryDateTime 
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

    public function updateterms_post() {
 
    
        $data = [
          'content' => $this->post('content'),
        ];
  
        if ($this->User_model->update_terms($data)) {
          $this->response([
              'status' => TRUE,
              'message' => 'Updated the Terms and conditions.'
          ], REST_Controller::HTTP_OK);
        } else {
          $this->response([
              'status' => FALSE,
              'message' => 'Not Updated the Terms and conditions.'
          ], REST_Controller::HTTP_CONFLICT);
        }
      
    }


    public function getTerms_get() {
       
    
    
        $terms = $this->User_model->get_terms();
    
        if ($terms) {
            $this->response([
                'status' => TRUE,
                'message' => 'Rooms retrieved successfully.',
                'data' => $terms,
            ], REST_Controller::HTTP_OK);
        } else {
            $this->response([
                'status' => FALSE,
                'message' => 'No rooms found.'
            ], REST_Controller::HTTP_NOT_FOUND);
        }
    }

    public function updateprivacy_post() {
 
    
        $data = [
          'content' => $this->post('content'),
        ];
  
        if ($this->User_model->update_privacy($data)) {
          $this->response([
              'status' => TRUE,
              'message' => 'Updated the Terms and conditions.'
          ], REST_Controller::HTTP_OK);
        } else {
          $this->response([
              'status' => FALSE,
              'message' => 'Not Updated the Terms and conditions.'
          ], REST_Controller::HTTP_CONFLICT);
        }
      
    }


    public function getprivacy_get() {
       
    
    
        $terms = $this->User_model->get_privacy();
    
        if ($terms) {
            $this->response([
                'status' => TRUE,
                'message' => 'Rooms retrieved successfully.',
                'data' => $terms,
            ], REST_Controller::HTTP_OK);
        } else {
            $this->response([
                'status' => FALSE,
                'message' => 'No rooms found.'
            ], REST_Controller::HTTP_NOT_FOUND);
        }
    }

    public function getroomUserlist_get() {
        $terms = $this->User_model->get_roomUserList();
    
        if ($terms) {
            $this->response([
                'status' => TRUE,
                'message' => 'Rooms Users List retrieved successfully.',
                'data' => $terms,
            ], REST_Controller::HTTP_OK);
        } else {
            $this->response([
                'status' => FALSE,
                'message' => 'No rooms found.'
            ], REST_Controller::HTTP_NOT_FOUND);
        }
    }


    public function getRoomDetails_post() {
        $roomId = $this->post('roomId');
        $rooms = $this->User_model->getRoomDetails($roomId);
 
        if (!empty($rooms)) {
            $this->response($rooms, REST_Controller::HTTP_OK);
        } else {
            $this->response([
                'status' => FALSE,
                'message' => 'No rooms found'
            ], REST_Controller::HTTP_NOT_FOUND);
        }
    }



   

    public function roomAllocation_post() {
        $amount = $this->security->xss_clean($this->post('amount'));
        $user_id = $this->security->xss_clean($this->post('userId'));
        $wallet = $this->User_model->get_wallet_amount($user_id);
        if ($wallet >= $amount) {

            $data = [
                'user_id' => $user_id,
                'startDate'=>$this->post('startDate'),
                'endDate'=>$this->post('endDate'),
                'startTime'=>$this->post('startTime'),
                'endTime'=>$this->post('endTime'),
                'room_id' => $this->post('roomId')
            ];

            if ($this->User_model->roomuserListInsert($data)) {
                $new_wallet = $wallet - $amount;
                $this->db->where('id', $user_id);
                $this->db->update('users', array('wallet_amount' => $new_wallet));

                $data = array(
                    'user_id' =>$user_id,
                    'trans_type' => "debit",
                    'amount' => $amount
                );

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
                $this->response([
                    'status' => TRUE,
                    'message' => 'Added to Room.'
                ], REST_Controller::HTTP_OK);
            } else {
                $this->response([
                    'status' => FALSE,
                    'message' => 'Error in withdraw'
                ], REST_Controller::HTTP_CONFLICT);
            }
        }else{
            $this->response([ 'status' => FALSE, 'message' => 'Wallet amount not enough'], REST_Controller::HTTP_BAD_REQUEST);
        }
        
      }


    public function debitRequest_post() {
        $amount = $this->security->xss_clean($this->post('amount'));
        $user_id = $this->security->xss_clean($this->post('userId'));
        $wallet = $this->User_model->get_wallet_amount($user_id);
        if ($wallet >= $amount) {
            $new_wallet = $wallet - $amount;
            $this->db->where('id', $user_id);
            $this->db->update('users', array('wallet_amount' => $new_wallet));

            $data = array(
                'user_id' =>$user_id,
                'trans_type' => "debit",
                'amount' => $amount
            );

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
        }else{
            $this->response([ 'status' => FALSE, 'message' => 'Wallet amount not enough'], REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    public function creditRequest_post() {
        $amount = $this->security->xss_clean($this->post('amount'));
        $user_id = $this->security->xss_clean($this->post('userId'));
        $wallet = $this->User_model->get_wallet_amount($user_id);
        $new_wallet = $wallet + $amount;
        $this->db->where('id', $user_id);
        $this->db->update('users', array('wallet_amount' => $new_wallet));

        $data = array(
            'user_id' =>$user_id,
            'trans_type' => "credit",
            'amount' => $amount
        );

        if ($this->User_model->debitinserdata($data)) {
            $this->response([
                'status' => TRUE,
                'newWallet'=>$new_wallet,
                'message' => 'Withdraw request successfully completed.'
            ], REST_Controller::HTTP_OK);
        } else {
            $this->response([
                'status' => FALSE,
                'message' => 'Error in withdraw'
            ], REST_Controller::HTTP_CONFLICT);
        }
    }


    public function winnerSave_post() {
        
        $room_id=$this->post('room_id');
        // print_r($this->post('winners'));
        $winners = $this->post('winners');

        if (!empty($winners) && is_array($winners)) {
             $data = array(
                'manuval_winners' => json_encode($winners),
            );
            if ($this->User_model->updateRoomWinnner($room_id, $data)) {
                $this->response([
                    'status' => TRUE,
                    'message' => 'Winner updated successfully'
                ], REST_Controller::HTTP_OK);
            } else {
                $this->response([
                    'status' => FALSE,
                    'message' => 'Failed to delete winners'
                ], REST_Controller::HTTP_CONFLICT);
            }
        }else{
            $this->response([
                'status' => FALSE,
                'message' => 'Invalid data format'
            ], REST_Controller::HTTP_CONFLICT);
        }
    }

    public function getRoomUsersList_post() {
        if($this->post('roomId')){
            $winners = $this->User_model->get_winners_by_room_id($this->post('roomId'));
            $users = $this->User_model->get_users_by_room_id($this->post('roomId'));
            $roomsInfo = $this->User_model->getRoomsByRoomNo($this->post('roomId'));
            if (!empty($winners) || !empty($users)) {
                $this->response([
                    'status' => TRUE,
                    "finalWinners"=>$winners,
                    "users"=>$users,
                    "roomsInfo"=>$roomsInfo,
                    'message' => 'Invalid data format'
                ], REST_Controller::HTTP_OK);

            } else {
                $this->response([
                    'status' => FALSE,
                    'message' => 'No winners found for this room ID'
                ], REST_Controller::HTTP_CONFLICT);
            }
        }
    }
    

}
?>
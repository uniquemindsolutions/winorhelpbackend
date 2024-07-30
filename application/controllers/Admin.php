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
    $this->load->library('Jwt_lib');
    // $this->load->model('Room_model');
  
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
        $manuval_winners=$this->post('manuval_winners');
        if($manuval_winners!=''){
            $manuval_winners=$this->post('manuval_winners');
        }else{
            $manuval_winners='';
        }

        $data = [
            'roomId' => $new_id,
            'entryFee' => $this->post('entryFee'),
            'startDate' => $this->post('startDate'),
            'endDate' => $this->post('endDate'),
            'startTime' => $this->post('startTime'),
            'endTime' => $this->post('endTime'),
           // 'winningAmount' => $this->post('winningAmount'),
            'winingPercentageInfo' => json_encode($this->post('winingPercentageInfo')),
            'latter_datetime' => $lotteryDateTime,
            'css' => $this->post('bgcolor'),
            'manuval_winners' => $manuval_winners,
            'isActive_users' => 1,
        ];
        // print_r($this->post());
        // print_r($data);die;
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

    
    // $tokenvalid=$this->validate_token();

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

public function adminroomList_get() {
    $page = $this->get('page');
    $limit = $this->get('limit');

    if (!$page) {
        $page = 1;
    }
    if (!$limit) {
        $limit = 10;
    }

    $offset = ($page - 1) * $limit;

    $rooms = $this->User_model->adminget_rooms($limit, $offset);
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


        $addedpercheck_data = [
            'user_id' => $this->post('userId'),
            'room_id' => $this->post('roomId'),
          ];
          $addedpercheckcond = $this->User_model->addedpercheck($addedpercheck_data);

        if ($wallet >= $amount  && count($addedpercheckcond)<=0) {

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
                $this->db->where('uniq_id', $user_id);
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

        // echo $this->db->last_query();


        
      }


    public function debitRequest_post() {
        $amount = $this->security->xss_clean($this->post('amount'));
        $user_id = $this->security->xss_clean($this->post('userId'));
        $wallet = $this->User_model->get_wallet_amount($user_id);
        if ($wallet >= $amount) {
            $new_wallet = $wallet - $amount;
            $this->db->where('uniq_id', $user_id);
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
       
        if($wallet==''){
            $new_wallet = $amount;
        }else{
            $new_wallet = $wallet + $amount;
        }
       
        $this->db->where('uniq_id', $user_id);
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
           // $winners = $this->User_model->get_winners_by_room_id($this->post('roomId'));
            $users = $this->User_model->get_users_by_room_id($this->post('roomId'));
            //echo $this->db->last_query();
            $roomsInfo = $this->User_model->getRoomsByRoomNo($this->post('roomId'));
            if (!empty($winners) || !empty($users)) {
                $this->response([
                    'status' => TRUE,
                    "finalWinners"=>'',
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
    public function roomWinnerList_get() {
        $page = $this->get('page');
        $limit = $this->get('limit');
    
        if (!$page) {
            $page = 1;
        }
        if (!$limit) {
            $limit = 10;
        }
    
        $offset = ($page - 1) * $limit;
    
        $rooms = $this->User_model->get_winnerlist_rooms($limit, $offset);
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

    public function roomwinerperUpdate_post() {
        $json_input = file_get_contents('php://input');
        $data = json_decode($json_input, true);
         //print_r($data[0]['room_id']);die;

        foreach($data as $result){

            $data_post = array(
                'winning_amount_per' =>$result['winning_amount_per'],
                'winning_amount' => $result['winning_amount'],
                'deduct_amount_per' => $result['deduct_amount_per'],
                'deduct_amount' => $result['deduct_amount'],
                'tot_amount_send' => $result['tot_amount_send']
            );
  
            $this->db->where('room_id', $result['room_id']);
            $this->db->where('user_id', $result['user_id']);
            $this->db->update('winner_list', $data_post);

        }

        $this->response([
            'status' => TRUE,
            'newWallet'=>$data,
            'message' => 'Withdraw request successfully completed.'
        ], REST_Controller::HTTP_OK);

       


       
    }

    public function masterdata_get() {
       
        $masterdata = $this->User_model->get_masterdata();
    
        if ($masterdata) {
            $this->response([
                'status' => TRUE,
                'message' => 'Rooms retrieved successfully.',
                'data' => $masterdata,
            ], REST_Controller::HTTP_OK);
        } else {
            $this->response([
                'status' => FALSE,
                'message' => 'No rooms found.'
            ], REST_Controller::HTTP_NOT_FOUND);
        }
    }

    public function masterdataupdate_post() {
        $ref_per = $this->security->xss_clean($this->post('ref_per'));

            $data_post = array(
                'ref_per' =>$ref_per
            );
            $masterdataupdate=$this->db->update('masterdata', $data_post);

            if ( $masterdataupdate) {
                $this->response([
                    'status' => TRUE,
                    'message' => 'MasterdataUpdated.',
                ], REST_Controller::HTTP_OK);
            } else {
                $this->response([
                    'status' => FALSE,
                    'message' => 'No rooms found.'
                ], REST_Controller::HTTP_NOT_FOUND);
            }
    
       
    }

    public function getUserMasterDetails_post() {

        $user_id = $this->security->xss_clean($this->post("user_id"));
  
        $user = $this->User_model->getuserdetails($user_id);
        
  
        if ($user) {
            $this->response([
                'status' => TRUE,
                'data' => $user,
                'message' => 'Success'
            ], REST_Controller::HTTP_OK);
        } else {
            $this->response([
                'status' => FALSE,
                'message' => 'Invalid username or password'
            ], REST_Controller::HTTP_UNAUTHORIZED);
        }
    }

    public function submitWinners_post() {


            $user_id = $this->security->xss_clean($this->post("user_id"));
  
        // $user = $this->User_model->getuserdetails($user_id);
        
        $data_post = array(
            'room_id' =>$this->security->xss_clean($this->post("room_id")),
            'user_id' =>$this->security->xss_clean($this->post("user_id")),
            'winner_orderid' =>$this->security->xss_clean($this->post("winner_orderid")),
            'tot_amount_send' =>$this->security->xss_clean($this->post("tot_amount_send")),
            'username' =>$this->security->xss_clean($this->post("username"))
        );
      
        $result_winner = $this->User_model->checkduplicateWinner($data_post);

        $rounddata = $this->User_model->getwinnersdata($this->post("room_id"));

        $result="";
        //echo count($rounddata)."hii";
        if(count($result_winner)>0){

        }else{
            if(count($rounddata)<$this->post("totround")){
                $result = $this->User_model->postwinnersdata($data_post);
            }
            
        }
       
        //$this->User_model->update_room_bothstatus($this->security->xss_clean($this->post("room_id")));


        ///Amount Crediting to user
        $wallet = $this->User_model->get_wallet_amount($user_id);
        $this->db->where('uniq_id', $user_id);
        $this->db->update('users', array('wallet_amount' => $wallet+$this->security->xss_clean($this->post("tot_amount_send"))));


        $data = array(
            'user_id' =>$this->post("user_id"),
            'trans_type' => "credit",
            'amount' => $this->post("tot_amount_send")
        );

        $this->User_model->debitinserdata($data);

        if ($result) {
            $this->response([
                'status' => TRUE,
                'data' => $result,
                'message' => 'Data Successfully Update'
            ], REST_Controller::HTTP_OK);
        } else {
            $this->response([
                'status' => FALSE,
                'message' => 'Error'
            ], REST_Controller::HTTP_UNAUTHORIZED);
        }
       
    }

    public function getsubmitWinners_post() {

        $room_id = $this->security->xss_clean($this->post("room_id"));
  
        $user = $this->User_model->getwinnersdata($room_id);
        if ($user || count($user)>=0) {
            $this->response([
                'status' => TRUE,
                'data' => $user,
                'message' => 'Success'
            ], REST_Controller::HTTP_OK);
        } else {
          
            $this->response([
                'status' => FALSE,
                'message' => 'Invalid username or password'
            ], REST_Controller::HTTP_UNAUTHORIZED);
        }
    }

    public function roommasterdataupdate_post() {
        $data_post = array(
            'roomId' =>$this->security->xss_clean($this->post("roomId")),
            'winningAmount' =>$this->security->xss_clean($this->post("winningAmount")),
            'totalParticipants' =>$this->security->xss_clean($this->post("totalParticipants"))
        );
  
        $user = $this->User_model->updateroomdetails($data_post);
        
        if ($user) {
            $this->response([
                'status' => TRUE,
                'data' => $user,
                'message' => 'Success'
            ], REST_Controller::HTTP_OK);
        } else {
            $this->response([
                'status' => FALSE,
                'message' => 'Updation error'
            ], REST_Controller::HTTP_UNAUTHORIZED);
        }
    }

    public function roomListWinners_get() {
        $page = $this->get('page');
        $limit = $this->get('limit');
    
        if (!$page) {
            $page = 1;
        }
        if (!$limit) {
            $limit = 10;
        }
    
        $offset = ($page - 1) * $limit;
    
        $rooms = $this->User_model->get_roomsWinners($limit, $offset);
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


    public function deleteroom_post() {
       
        $id = $this->post('id');
        $rooms = $this->User_model->deleteRoom($id);
        
    
        if ($rooms) {
            $this->response([
                'status' => TRUE,
                'message' => 'Rooms Deleted successfully.',
               
            ], REST_Controller::HTTP_OK);
        } else {
            $this->response([
                'status' => FALSE,
                'message' => 'No rooms found.'
            ], REST_Controller::HTTP_NOT_FOUND);
        }
    }  
    
    public function userhist_get() {

       // echo "hii";die;
        // $page = $_GET['page'];
        // $limit = $_GET['limit'];
        // $userid = $_GET['user_id'];
    
        // if (!$page) {
        //     $page = 1;
        // }
        // if (!$limit) {
        //     $limit = 10;
        // }
    
        // $offset = ($page - 1) * $limit;
        $users = $this->User_model->get_alluser_walethist();
        if ($users) {
            $this->response([
                'status' => TRUE,
                'message' => 'Users retrieved successfully.',
                'data' => $users,
                'total_users' => $users,
                
            ], REST_Controller::HTTP_OK);
        } else {
            $this->response([
                'status' => FALSE,
                'message' => 'No users found.'
            ], REST_Controller::HTTP_NOT_FOUND);
        }
    }

    public function room_state_get() {
        $room_id=$this->get('roomId');
        $room = $this->Room_model->get_room($room_id);
        $users = $this->Room_model->get_users($room_id);
        $winners = $this->Room_model->get_winners($room_id);

        $response = array(
            'room' => $room,
            'users' => $users,
            'winners' => $winners
        );
        $this->response([
            'status' => TRUE,
            'message' => 'Rooms retrieved successfully.',
            'data' =>$response,
           
        ], REST_Controller::HTTP_OK);

        //echo json_encode($response);
    }

    public function update_room_state_post() {
        $room_id = $this->input->post('room_id');
        $data = array(
            'lotteryDate' => $this->input->post('lotteryDate'),
            'currentRound' => $this->input->post('currentRound'),
            'scrolling' => $this->input->post('scrolling')
        );

        $this->Room_model->update_room($room_id, $data);
        echo json_encode(array('status' => 'success'));
    }

    public function select_winner_post() {
        $data = json_decode($this->input->raw_input_stream, true);
        $roomId = $data['roomId'];
        $manualWinners = isset($data['manualWinners']) ? $data['manualWinners'] : [];
        
        $room = $this->Room_model->get_room_state($roomId);
        if (!$room) {
            $this->output->set_status_header(404);
            echo json_encode(['error' => 'Room not found']);
            return;
        }

        $users = json_decode($room['users'], true);
        if (!is_array($users)) $users = [];

        $winners = json_decode($room['winners'], true);
        if (!is_array($winners)) $winners = [];

        if (!empty($manualWinners)) {
            foreach ($manualWinners as $manualWinner) {
                if (!in_array($manualWinner, $winners)) {
                    $this->Room_model->add_winner($roomId, $manualWinner);
                }
            }
        } else {
            // Pick a random winner
            $remainingUsers = array_diff($users, $winners);
            if (empty($remainingUsers)) {
                $this->output->set_status_header(400);
                echo json_encode(['error' => 'No remaining users to pick from']);
                return;
            }
            $winner = $remainingUsers[array_rand($remainingUsers)];
            $this->Room_model->add_winner($roomId, $winner);
        }

        echo json_encode(['success' => true]);
    }
    

}
?>
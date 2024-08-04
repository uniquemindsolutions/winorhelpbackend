<?php
class User_model extends CI_Model {

    public function __construct() {
        $this->load->database();
    }


    private function generate_unique_id() {
        // Get the last inserted user ID
        $this->db->select('uniq_id');
        $this->db->order_by('uniq_id', 'DESC');
        $this->db->limit(1);
        $query = $this->db->get('users');
        $last_user = $query->row();

        if ($last_user) {
            // Extract the number from the last user ID
            $last_id_number = (int) substr($last_user->uniq_id, 2);
            // Increment the number by 1
            $new_id_number = $last_id_number + 1;
        } else {
            // If there are no users yet, start with 1
            $new_id_number = 1;
        }

        // Determine the number of digits in the new ID number
        $num_digits = strlen((string) $new_id_number);
        // Calculate the number of leading zeros required
        $total_length = max(6, $num_digits);
        $num_leading_zeros = $total_length - $num_digits;

        // Format the new ID with leading zeros and the prefix 'WH'
        $new_id = 'WH' . str_pad($new_id_number, $total_length, '0', STR_PAD_LEFT);

        return $new_id;
    }

    public function register($data) {
      
        $this->db->select("*");
        $this->db->from('users');
        $this->db->where('email', $data['email']);
        $query = $this->db->get();
        if($query->row_array()){
           
            return false;
        }else{
            $token = bin2hex(random_bytes(50)); // Generate a token
            $data['token']=$token;
            $data['uniq_id'] = $this->generate_unique_id();
           
            return $this->db->insert('users', $data);
        }
    }

    public function login($username, $password) {
        
        $this->db->where('email', $username);
        $query = $this->db->get('users');
        $user = $query->row();
    
        if ($user && $password==$user->password) {
            return $user;
        } else {
            return false;
        }
    }

    public function get_last_room_id() {
        $this->db->select('roomId');
        $this->db->from('rooms');
        $this->db->order_by('roomId', 'DESC');
        $this->db->limit(1);
        $query = $this->db->get();
        return $query->row();
    }


    public function create_room($data) {
        if ($this->is_duplicate_room($data['roomId'])) {
            return false;
        }
        return $this->db->insert('rooms', $data);
    }

    public function is_duplicate_room($roomId) {
        $this->db->where('roomId', $roomId);
        $query = $this->db->get('rooms');
        return $query->num_rows() > 0;
    }

    // Method to fetch rooms with pagination
     public function get_rooms($limit, $offset) {
        // $this->db->where('DATE(endDate) != DATE_SUB(CURDATE(), INTERVAL 1 DAY)');
        $this->db->where('latter_datetime >', 'NOW()', FALSE);
        $this->db->where('isActive_users', 1);
        $query = $this->db->get('rooms');
       
        return $query->result_array();
    }

    public function adminget_rooms($limit, $offset) {
        //$this->db->where('isActive', 1);
        // $this->db->order_by('roomId','desc');
        $this->db->order_by('latter_datetime','desc');
        $query = $this->db->get('rooms');
       
        return $query->result_array();
    }

    
    //
    public function getRoomsByRoomNo($roomNo) {
        $this->db->select("*");
        $this->db->from("rooms");
        $this->db->where("roomId", $roomNo);
        $query = $this->db->get();
        return $query->row();
    }

    // Method to get the total count of users
    public function get_total_rooms() {
        return $this->db->count_all('rooms');
    }

    // Method to fetch users with pagination
     public function get_users($limit, $offset) {
        $this->db->where("username!=", 'admin');
        $query = $this->db->get('users');
        return $query->result_array();
    }

    // Method to get the total count of users
    public function get_total_users() {
        return $this->db->count_all('users');
    }

    public function update_room_status($id, $status) {
        $data = array('isActive_users' => $status);
        $this->db->where('id', $id);
        return $this->db->update('rooms', $data);
    }

    public function delete_room($id) {
        $this->db->where('id', $id);
        return $this->db->delete('rooms');
    }


    public function get_rooms_today($startDate, $startTime, $endDate, $endTime) {
        $this->db->where('startDate >=', $startDate);
        $this->db->where('startTime >=', $startTime);
        $this->db->where('endDate <=', $endDate);
        $this->db->where('endTime <=', $endTime);
        // $this->db->where('isActive', 1);
        $query = $this->db->get('room');
        return $query->result();
    }


    public function debitinserdata($data = array()){

        return $this->db->insert("user_wallet_history", $data);
    }

    

    public function get_user_walethist($limit, $offset,$userid){

        $this->db->select("*");
        $this->db->from("user_wallet_history");
        $this->db->where("user_id", $userid);
        $query = $this->db->get();
    
        return $query->result();
      }

      public function update_terms($data) {
    
        $this->db->where('id', "1");
        return $this->db->update('terms', $data);
    }

    public function get_terms() {
    
        $this->db->where('id', "1");
        $query = $this->db->get('terms');
        return $query->result();
    }

    public function update_privacy($data) {
    
        $this->db->where('id', "1");
        return $this->db->update('privacy', $data);
    }

    public function get_privacy() {
    
        $this->db->where('id', "1");
        $query = $this->db->get('privacy');
        return $query->result();
    }

    public function get_roomUserList($roomId) {
    
        $this->db->where('room_id', $roomId);
        $query = $this->db->get('rooms_userlist');
        return $query->result();
    }

    public function roomuserListInsert($data = array()){
        return $this->db->insert("rooms_userlist", $data);
    }

    public function get_wallet_amount($user_id) {
        // Query the database to get the wallet amount
        $this->db->select('wallet_amount');
        $this->db->where('uniq_id', $user_id);
        $query = $this->db->get('users');
        
        if ($query->num_rows() > 0) {
            return $query->row()->wallet_amount;
        }
        
        return false; // User not found or other error
    }


    public function get_currentamount($data = array()) {
        $this->db->where('uniq_id', $data['user_id']);
        $query = $this->db->get('users');
        return $query->result();
    }

    public function getRoomDetails($roomId) {
        $this->db->where('roomId', $roomId);
        $query = $this->db->get('rooms');
        return $query->row();
    }

    /**@SAVE WINNER */
    public function save_winners($data) {
        return $this->db->insert_batch('winner_list', $data);
    }

    public function get_winners_by_room_id($room_id) {
        $this->db->where('room_id', $room_id);
        $query = $this->db->get('winner_list');
        return $query->result_array();
    }

    public function get_users_by_room_id($room_id) {
        // ru.manuval_winners
        $this->db->select('ru.user_id, u.username, u.email, ru.room_id');
        $this->db->from('rooms_userlist ru');
        $this->db->join('users u', 'ru.user_id = u.uniq_id');
        $this->db->where('ru.room_id', $room_id);
        $query = $this->db->get();
        return $query->result_array();

        $this->db->where('room_id', $room_id);
        $query = $this->db->get('rooms_userlist');
        return $query->result_array();
    }

    public function delete_winners_by_room_id($room_id) {
        $this->db->where('room_id', $room_id);
        return $this->db->delete('winner_list');
    }


    public function updateRoomWinnner($room_id, $data) {
        $this->db->where('roomId', $room_id);
        return $this->db->update('rooms', $data);
    }
    public function get_winnerlist_rooms($limit, $offset) {
        $query = $this->db->get('winner_list');
        return $query->result_array();
    }

    public function get_refsts($data = array()) {
    
        $this->db->where('uniq_id', $data['user_id']);
        $this->db->where('ref_amount_sts', 0);
        $query = $this->db->get('users');
        return $query->result_array();
    }

    public function get_masterdata($data = array()) {
    
        $query = $this->db->get('masterdata');
        return $query->result();
    }

    public function addedpercheck($data = array()) {
    
        $this->db->where('user_id', $data['user_id']);
        $this->db->where('room_id', $data['room_id']);
        $query = $this->db->get('rooms_userlist');
        return $query->result_array();
    }

    
public function getuserdetails($user_id) {
        
        $this->db->where('uniq_id', $user_id);
        $query = $this->db->get('users');
        $user = $query->row();
    
        if ($user) {
            return $user;
        } else {
            return false;
        }
    }


    public function postwinnersdata($data = array()){

        return $this->db->insert("winners_new_list", $data);
    }

    public function getwinnersdata($room_id) {
        
     

        // $this->db->where('room_id', $room_id);
        // $this->db->order_by('winner_orderid','ASC');
        // $query = $this->db->get('winners_new_list');



        $this->db->select('`w`.*,`r`.*');
       // $this->db->from('winners_new_list w');
        $this->db->join('rooms r', 'r.roomId = w.room_id');
        $this->db->where('w.room_id', $room_id);
        $this->db->order_by('w.winner_orderid','ASC');
   
        $query = $this->db->get('winners_new_list w');
        // echo $this->db->last_query();die;

        return $query->result_array();
    
       
    }

    public function update_room_bothstatus($room_id) {
        $data = array('isActive_users' => 0,'isActive' => 0);
        $this->db->where('roomId', $room_id);
        return $this->db->update('rooms', $data);
    }

    public function updateroomdetails($roomdata) {
         //print_r($data);die;
        $data = array('winningAmount' => $roomdata['winningAmount'],'totalParticipants' => $roomdata['totalParticipants']);
        $this->db->where('roomId', $roomdata['roomId']);
        return $this->db->update('rooms', $data);
    }
    
    public function get_roomsWinners($limit, $offset) {
        //$this->db->where('isActive_users', 1);
        $query = $this->db->get('rooms');
       
        return $query->result_array();
    }

    public function deleteRoom($id) {
      
        $this->db->where('id', $id);
        return $this->db->delete('rooms');
    }

    
    public function get_alluser_walethist(){

        $this->db->select("*");
        $this->db->from("user_wallet_history");
        $query = $this->db->get();
    
        return $query->result();
      }

      public function checkduplicateWinner($data) {
        
     
        $this->db->where('user_id', $data['user_id']);
        $this->db->where('room_id', $data['room_id']);
        $this->db->order_by('winner_orderid','ASC');
        $query = $this->db->get('winners_new_list');
        return $query->result_array();
    
       
    }

    public function update_password($user_id, $data) {
        $this->db->where('uniq_id', $user_id);
        return $this->db->update('users', $data);
    }

    

    

}
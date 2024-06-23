<?php
class User_model extends CI_Model {

    public function __construct() {
        $this->load->database();
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
        $query = $this->db->get('rooms', $limit, $offset);
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
        $query = $this->db->get('users', $limit, $offset);
        return $query->result_array();
    }

    // Method to get the total count of users
    public function get_total_users() {
        return $this->db->count_all('users');
    }

    public function update_room_status($id, $status) {
        $data = array('isActive' => $status);
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

    public function get_roomUserList() {
    
        $this->db->where('room_id', "RM000001");
        $query = $this->db->get('rooms_userlist');
        return $query->result();
    }

    public function roomuserListInsert($data = array()){
        return $this->db->insert("rooms_userlist", $data);
    }

    public function get_wallet_amount($user_id) {
        // Query the database to get the wallet amount
        $this->db->select('wallet_amount');
        $this->db->where('id', $user_id);
        $query = $this->db->get('users');
        
        if ($query->num_rows() > 0) {
            return $query->row()->wallet_amount;
        }
        
        return false; // User not found or other error
    }


    public function get_currentamount($data = array()) {
        $this->db->where('id', $data['user_id']);
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
        $this->db->join('users u', 'ru.user_id = u.id');
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
    

}
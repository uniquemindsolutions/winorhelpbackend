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
        $this->db->select('room_id');
        $this->db->from('rooms');
        $this->db->order_by('room_id', 'DESC');
        $this->db->limit(1);
        $query = $this->db->get();
        return $query->row();
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

    
}
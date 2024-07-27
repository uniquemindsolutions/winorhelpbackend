<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Room_model extends CI_Model {
    
    public function __construct() {
        $this->load->database();
    }

    public function get_room($room_id) {
        $query = $this->db->get_where('rooms', array('roomId' => $room_id));
        return $query->row_array();
    }

    public function get_users($room_id) {
        $query = $this->db->get_where('rooms_userlist', array('room_id' => $room_id));
        return $query->result_array();
    }

    public function update_room($room_id, $data) {
        $this->db->where('roomId', $room_id);
        return $this->db->update('rooms', $data);
    }

    public function insert_winner($data) {
        return $this->db->insert('winners_new_list', $data);
    }

    public function get_winners($room_id) {
        $this->db->select('winners_new_list.*, rooms_userlist.user_id');
        $this->db->from('winners_new_list');
        $this->db->join('rooms_userlist', 'winners_new_list.user_id = rooms_userlist.user_id');
        $this->db->where('winners_new_list.room_id', $room_id);
        $query = $this->db->get();
        return $query->result_array();
    }
    public function add_winner($roomId, $winner) {
        $room = $this->get_room_state($roomId);
        if (!$room) return false;

        $winners = json_decode($room['winners'], true);
        if (!is_array($winners)) $winners = [];
        
        $winners[] = $winner;
        $winners = json_encode($winners);
        
        $this->db->where('id', $roomId);
        return $this->db->update('winners_new_list', ['winners' => $winners]);
    }
    public function get_room_state($roomId) {
        $this->db->where('id', $roomId);
        $query = $this->db->get('rooms');
        return $query->row_array();
    }
}
?>

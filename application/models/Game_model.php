<?php
class Game_model extends CI_Model {
    
    public function __construct() {
        $this->load->database();
    }

    public function getGame() {
        $query = $this->db->get('games');
        return $query->row_array();
    }

    public function updateGame($gameState) {
        $this->db->update('games', $gameState);
    }
}
?>

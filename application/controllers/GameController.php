<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class GameController extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Game_model');
    }

    // Fetch Game State
    public function get_game_state() {
        $game = $this->Game_model->getGame();

        if ($game) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($game));
        } else {
            $this->output
                ->set_status_header(404)
                ->set_output(json_encode(['message' => 'Game not found']));
        }
    }

    // Update Game State
    public function update_game_state() {
        $input = json_decode($this->input->raw_input_stream, true);

        if (!isset($input['gameState'])) {
            $this->output
                ->set_status_header(400)
                ->set_output(json_encode(['message' => 'Invalid input']));
            return;
        }

        $update_result = $this->Game_model->updateGame($input['gameState']);

        if ($update_result) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($update_result));
        } else {
            $this->output
                ->set_status_header(500)
                ->set_output(json_encode(['message' => 'Failed to update game state']));
        }
    }
}
?>

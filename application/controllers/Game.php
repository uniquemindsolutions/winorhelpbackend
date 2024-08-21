<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH.'libraries/REST_Controller.php';

class Game extends CI_Controller {

    public function __construct() {
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json');
        header('Access-Control-Allow-Headers: *');
        header('Access-Control-Allow-Credentials', 'true');
        header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE, OPTIONS");
        parent::__construct();
        $this->load->model('Room_model');
        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
            exit;
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

    public function update_room_state() {
        $room_id = $this->input->post('room_id');
        $data = array(
            'lotteryDate' => $this->input->post('lotteryDate'),
            'currentRound' => $this->input->post('currentRound'),
            'scrolling' => $this->input->post('scrolling')
        );

        $this->Room_model->update_room($room_id, $data);
        echo json_encode(array('status' => 'success'));
    }

    public function add_winner() {
        $data = array(
            'room_id' => $this->input->post('room_id'),
            'user_id' => $this->input->post('user_id'),
            'username' => $this->input->post('username'),
            'winner_orderid' => $this->input->post('winner_orderid'),
            'tot_amount_send' => $this->input->post('tot_amount_send')
        );

        $this->Room_model->insert_winner($data);
        echo json_encode(array('status' => 'success'));
    }

    public function get_winners($room_id) {
        $winners = $this->Room_model->get_winners($room_id);
        echo json_encode(array('winners' => $winners));
    }
}
?>

<?php
 Header('Content-type: application/json');
 Header("Access-Control-Allow-Origin: *");
 Header("Access-Control-Allow-Methods: GET");
 header("Access-Control-Allow-Methods: GET, OPTIONS");
 Header("Access-Control-Allow-Headers: Content-Type, Content-Length, Accept-Encoding");
  require APPPATH.'libraries/REST_Controller.php';

class Auth extends REST_Controller{

  public function __construct(){

    parent::__construct();

    // Set headers for CORS


    header('Content-type: application/json');
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET");
    header("Access-Control-Allow-Methods: GET, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Content-Length, Accept-Encoding");


    //load database
    $this->load->database();
    $this->load->model('User_model');
    $this->load->helper('form');
    $this->load->library(array("form_validation", "email"));
    $this->load->helper("security");
    $this->load->helper('url');
  }


  private function handleCors() {
    header('Access-Control-Allow-Origin: '); // You can specify the domain instead of ''
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

    // If this is a preflight request, exit without further processing
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        exit(0);
    }
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

  // POST: <project_url>/index.php/student


  // PUT: <project_url>/index.php/student
 

  // DELETE: <project_url>/index.php/student
  

  // GET: <project_url>/index.php/student



  public function register_post() {
    // $json_input = file_get_contents('php://input');
    // $data = json_decode($json_input, true);

    $token = bin2hex(random_bytes(50)); // Generate a token
    

    $data = [
      'username' => $this->security->xss_clean($this->post("username")),
      'password' => $this->security->xss_clean($this->post("password")),
      'email' => $this->security->xss_clean($this->post('email')),
      'phone' => $this->security->xss_clean($this->post('phone')),
      'ref_code' => $this->security->xss_clean($this->post('ref_code'))
    ];
    $data['token'] = $token;

    if ($this->User_model->register($data)) {
        $data['email_veri'] = 0;

        // Email configuration
        $config = array(
            'protocol' => 'smtp',
            'smtp_host' => 'smtp.gmail.com',
            'smtp_port' => 587,
            'smtp_user' => 'upender540@gmail.com', // Your Gmail address
            'smtp_pass' => 'upender1611', // Your Gmail password
            'smtp_crypto' => 'tls', // Enable TLS encryption
            'mailtype' => 'html',
            'charset' => 'utf-8',
            'newline' => "\r\n"
        );

        $this->email->initialize($config);

        // Send verification email
        $verification_link = base_url() . "api/verify_email/$token";
        $this->email->from('infoumsmails@gmail.com', 'UMS');
        $this->email->to("upenderm8030@gmail.com");
        $this->email->subject('Email Verification');
        $this->email->message("Click the link to verify your email: $verification_link");

        // if ($this->email->send()) {
        //     $this->response([
        //         'status' => TRUE,
        //         'message' => 'User registered successfully. Verification email sent.',
        //         'data' => $data
        //     ], REST_Controller::HTTP_OK);
        // } else {
        //     $this->response([
        //         'status' => TRUE,
        //         'message' => 'User registered, but failed to send verification email.',
        //         'emailerror' => $this->email->print_debugger(),
        //         'data' => $data
        //     ], REST_Controller::HTTP_OK);
        // }

        $this->response([
          'status' => TRUE,
          'message' => 'User registered successfully. Verification email sent.',
          'data' => $data
      ], REST_Controller::HTTP_OK);

    } else {
      // echo $this->db->last_query();
        $this->response([
            'status' => FALSE,
            'message' => 'Email id already registered',
        ], REST_Controller::HTTP_CONFLICT);
    }
    // echo $this->db->last_query();
  }

  public function verify_email($token) {
      $this->db->where('token', $token);
      $user = $this->db->get('users')->row();

      if ($user) {
          $this->db->where('token', $token);
          $this->db->update('users', array('email_veri' => 1));

          $this->response([
              'status' => 'success',
              'message' => 'Email verified successfully.'
          ], REST_Controller::HTTP_OK);
      } else {
          $this->response([
              'status' => 'error',
              'message' => 'Invalid token.'
          ], REST_Controller::HTTP_BAD_REQUEST);
      }
  }

  public function login_post() {
      // $json_input = file_get_contents('php://input');
      // $data = json_decode($json_input, true);

      $email = $this->security->xss_clean($this->post("email"));
      $password = $this->security->xss_clean($this->post("password"));;

      $user = $this->User_model->login($email, $password);

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



}

 ?>

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
  public function index_post(){
    // insert data method

    //print_r($this->input->post());die;

    // collecting form data inputs
    $name = $this->security->xss_clean($this->input->post("name"));
    $email = $this->security->xss_clean($this->input->post("email"));
    $mobile = $this->security->xss_clean($this->input->post("mobile"));
    $course = $this->security->xss_clean($this->input->post("course"));

    // form validation for inputs
    $this->form_validation->set_rules("name", "Name", "required");
    $this->form_validation->set_rules("email", "Email", "required|valid_email");
    $this->form_validation->set_rules("mobile", "Mobile", "required");
    $this->form_validation->set_rules("course", "Course", "required");

    // checking form submittion have any error or not
    if($this->form_validation->run() === FALSE){

      // we have some errors
      $this->response(array(
        "status" => TRUE,
        "message" => "All fields are needed"
      ) , REST_Controller::HTTP_NOT_FOUND);
    }else{

      if(!empty($name) && !empty($email) && !empty($mobile) && !empty($course)){
        // all values are available
        $student = array(
          "name" => $name,
          "email" => $email,
          "mobile" => $mobile,
          "course" => $course
        );

        if($this->student_model->insert_student($student)){

          $this->response(array(
            "status" => TRUE,
            "message" => "Student has been created"
          ), REST_Controller::HTTP_OK);
        }else{

          $this->response(array(
            "status" => FALSE,
            "message" => "Failed to create student"
          ), REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
        }
      }else{
        // we have some empty field
        $this->response(array(
          "status" => TRUE,
          "message" => "All fields are needed"
        ), REST_Controller::HTTP_NOT_FOUND);
      }
    }

    /*$data = json_decode(file_get_contents("php://input"));

    $name = isset($data->name) ? $data->name : "";
    $email = isset($data->email) ? $data->email : "";
    $mobile = isset($data->mobile) ? $data->mobile : "";
    $course = isset($data->course) ? $data->course : "";*/


  }

  // PUT: <project_url>/index.php/student
  public function index_put(){
    // updating data method
    //echo "This is PUT Method";
    $data = json_decode(file_get_contents("php://input"));

    if(isset($data->id) && isset($data->name) && isset($data->email) && isset($data->mobile) && isset($data->course)){

      $student_id = $data->id;
      $student_info = array(
        "name" => $data->name,
        "email" => $data->email,
        "mobile" => $data->mobile,
        "course" => $data->course
      );

      if($this->student_model->update_student_information($student_id, $student_info)){

          $this->response(array(
            "status" => 1,
            "message" => "Student data updated successfully"
          ), REST_Controller::HTTP_OK);
      }else{

        $this->response(array(
          "status" => 0,
          "messsage" => "Failed to update student data"
        ), REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
      }
    }else{

      $this->response(array(
        "status" => 0,
        "message" => "All fields are needed"
      ), REST_Controller::HTTP_NOT_FOUND);
    }
  }

  // DELETE: <project_url>/index.php/student
  public function index_delete(){
    // delete data method
    $data = json_decode(file_get_contents("php://input"));
    $student_id = $this->security->xss_clean($data->student_id);

    if($this->student_model->delete_student($student_id)){
      // retruns true
      $this->response(array(
        "status" => 1,
        "message" => "Student has been deleted"
      ), REST_Controller::HTTP_OK);
    }else{
      // return false
      $this->response(array(
        "status" => 0,
        "message" => "Failed to delete student"
      ), REST_Controller::HTTP_NOT_FOUND);
    }
  }

  // GET: <project_url>/index.php/student
  public function index_get(){
    // list data method
    //echo "This is GET Method";
    // SELECT * from tbl_students;
    $students = $this->student_model->get_students();

    //print_r($query->result());

    if(count($students) > 0){

      $this->response(array(
        "status" => 1,
        "message" => "Students found",
        "data" => $students
      ), REST_Controller::HTTP_OK);
    }else{

      $this->response(array(
        "status" => 0,
        "message" => "No Students found",
        "data" => $students
      ), REST_Controller::HTTP_NOT_FOUND);
    }
  }


  public function register_post() {
    $json_input = file_get_contents('php://input');
    $data = json_decode($json_input, true);

    $token = bin2hex(random_bytes(50)); // Generate a token
    $data['token'] = $token;

    if ($this->User_model->register($data)) {
        $data['email_veri'] = 0;
        $this->db->insert('users', $data);

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

        if ($this->email->send()) {
            $this->response([
                'status' => TRUE,
                'message' => 'User registered successfully. Verification email sent.',
                'data' => $data
            ], REST_Controller::HTTP_OK);
        } else {
            $this->response([
                'status' => TRUE,
                'message' => 'User registered, but failed to send verification email.',
                'emailerror' => $this->email->print_debugger(),
                'data' => $data
            ], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
        }

    } else {
        $this->response([
            'status' => FALSE,
            'message' => 'Email id already registered',
        ], REST_Controller::HTTP_CONFLICT);
    }
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
      $json_input = file_get_contents('php://input');
      $data = json_decode($json_input, true);

      $user = $this->User_model->login($data['email'], $data['password']);

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

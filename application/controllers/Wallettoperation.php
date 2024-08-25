<?php

require APPPATH.'libraries/REST_Controller.php';

class Wallettoperation extends REST_Controller{

  public function __construct(){

    parent::__construct();

    if (isset($_SERVER['HTTP_ORIGIN'])) {
      header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
      header('Access-Control-Allow-Credentials: true');
      header('Access-Control-Max-Age: 86400');    // Cache for 1 day
  }

  // Access-Control headers are received during OPTIONS requests
  if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

      if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
          header("Access-Control-Allow-Methods: GET, POST, OPTIONS, DELETE, PUT");

      if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
          header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

      exit(0);
  }


    //load database
    $this->load->database();
    $this->load->model('User_model');
    $this->load->helper('form');
    $this->load->library(array("form_validation", "email"));
    $this->load->helper("security");
    $this->load->helper('url');
    $this->load->library('session');
  }

  public function index_post(){
    



  }

  public function walletwithdraw_post() {

  
    $amount = $this->security->xss_clean($this->post('amount'));
    $upi = $this->security->xss_clean($this->post('upi'));
    $user_id = $this->security->xss_clean($this->post('user_id'));
    $wallet = $this->User_model->get_wallet_amount($user_id);
    $userrequest = $this->security->xss_clean($this->post('userrequest'));
    if( $userrequest=='1'){
    $trans_type="wdebit";
    }else{
      $trans_type="debit";
    }

    $data = array(
      'user_id' =>$user_id,
      'trans_type' => $trans_type,
      'amount' => $amount
    );

    if ($this->User_model->debitinserdata($data)) {

      $new_wallet = $wallet - $amount;
      $this->db->where('uniq_id', $user_id);
      $this->db->update('users', array('wallet_amount' => $new_wallet));

      if($upi!=''){
        $this->db->where('uniq_id', $user_id);
        $this->db->update('users', array('upi' => $upi));
      }


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
  }

  public function users_getwallet_get() {

    $page = $_GET['page'];
    $limit = $_GET['limit'];
    $userid = $_GET['user_id'];

    if (!$page) {
        $page = 1;
    }
    if (!$limit) {
        $limit = 10;
    }

    $offset = ($page - 1) * $limit;
    $users = $this->User_model->get_user_walethist($limit, $offset,$userid);
    if ($users) {
        $this->response([
            'status' => TRUE,
            'message' => 'Users retrieved successfully.',
            'data' => $users,
            'total_users' => $users,
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




public function getCurrentAmount_get() {

  
  $data = [
    'user_id' => $this->get('user_id'),
  ];
  $cyrrentamount = $this->User_model->get_currentamount($data);
  // echo $this->db->last_query();exit;
  //$total_users = $this->User_model->get_total_users();

  if ($cyrrentamount) {
      $this->response([
          'status' => TRUE,
          'message' => 'current',
          'data' => $cyrrentamount
      ], REST_Controller::HTTP_OK);
  } else {
      $this->response([
          'status' => FALSE,
          'message' => 'No users found.'
      ], REST_Controller::HTTP_NOT_FOUND);
  }
}

public function roomuserlistInsert_post(){

 
  $amount = $this->security->xss_clean($this->post('roomamount'));
  $user_id = $this->security->xss_clean($this->post('user_id'));
  $wallet = $this->User_model->get_wallet_amount($user_id);

  $addedpercheck_data = [
    'user_id' => $this->post('user_id'),
    'room_id' => $this->post('roomnumber'),
  ];
  $addedpercheckcond = $this->User_model->addedpercheck($addedpercheck_data);

 
 
  if ($wallet >= $amount && count($addedpercheckcond)<=0) {


      $data = [
        'user_id' => $this->post('user_id'),
        'startDate'=>date('Y-m-d'),
        'endDate'=>date('Y-m-d'),
        'startTime'=>date('H:i:s'),
        'endTime'=>date('H:i:s'),
        'room_id' => $this->post('roomnumber')
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



          //Refferal Code amount update
  $data_userid = [
    'user_id' => $this->post('user_id'),
  ];
  $cyrrentamount = $this->User_model->get_refsts($data_userid);
      if(count($cyrrentamount)>0){
                      //Prevwalletamount//
                        $data_userid = [
                          'user_id' => $cyrrentamount[0]['ref_code'],
                        ];
                        $referprevamount = $this->User_model->get_refsts($data_userid);
                      //Prevwalletamount//
                        $roomamount=$this->post('roomamount');
                        $ref_per=$this->post('refpercentage');
                        $creditamountval=($roomamount * $ref_per)/100;
                        $refwallet = $this->User_model->get_wallet_amount($cyrrentamount[0]['ref_code']);
                        $addedamount=$creditamountval+$refwallet;
                        $this->db->where('uniq_id', $cyrrentamount[0]['ref_code']);
                        $this->db->update('users', array('wallet_amount' => $addedamount));
                        $this->db->where('uniq_id', $user_id);
                        $this->db->update('users', array('ref_amount_sts' => 1));

                        $datarefcredit = array(
                          'user_id' =>$cyrrentamount[0]['ref_code'],
                          'trans_type' => "credit",
                          'amount' => $creditamountval
                      );
                      $this->User_model->debitinserdata($datarefcredit);

                       
     }
          

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
      $this->response([ 'status' => FALSE, 'message' => 'Wallet amount not enough or useralready added'], REST_Controller::HTTP_BAD_REQUEST);
  }



  

}


 

}

 ?>

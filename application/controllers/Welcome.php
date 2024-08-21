<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Websocketnew extends CI_Controller {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://codeigniter.com/user_guide/general/urls.html
	 */
	
	 public function __construct(){

		parent::__construct();
	
		// Set headers for CORS
		header('Access-Control-Allow-Origin: *');
		header('Access-Control-Allow-Headers: *');
		header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE');
	
	
		//load database
		 $this->load->database();
		// $this->load->model('User_model');
		// $this->load->helper('form');
		// $this->load->library(array("form_validation", "email"));
		// $this->load->helper("security");
		// $this->load->helper('url');

		$this->load->add_package_path(FCPATH . 'vendor/takielias/codeigniter-websocket');
		$this->load->library('Codeigniter_websocket');
			$this->load->remove_package_path(FCPATH . 'vendor/takielias/codeigniter-websocket');
	
			// Run server
			$this->codeigniter_websocket->set_callback('auth', array($this, '_auth'));
			$this->codeigniter_websocket->set_callback('event', array($this, '_event'));
			$this->codeigniter_websocket->run();
	  }
	public function index()
	{
		echo "hiii welocme";die;
		$this->load->view('welcome_message');
	}
}

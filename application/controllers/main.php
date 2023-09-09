<?php 
$path = BASEPATH .'../application/third_party/php/pear';
set_include_path(get_include_path() . PATH_SEPARATOR . $path);

$_SERVER['DOCUMENT_ROOT'] .= '/template';

include_once($_SERVER['DOCUMENT_ROOT']. '/application/third_party/Spreadsheet/Excel/Writer.php');
include_once($_SERVER['DOCUMENT_ROOT']. '/application/third_party/Spreadsheet/Excel/Reader/reader.php');
//include_once('Spreadsheet/Excel/Writer.php');
//include_once('Spreadsheet/Excel/Reader/reader.php');
Class Main extends Auth_Controller  { 
	var $data = array();
	//use `Auth_Controller` if you want the page to be validated if the user is logged in or not, if you want to disable this then use `CI_Controller` instead
	public  function __construct(){
		parent::__construct();
		$this->load->model('main_model');
		$this->load->helper('url');
		$this->load->library('session');  
		$this->load->helper(array('form', 'url'));  
		#$this->has_permission(178);
		$this->data = $this->get_privs();
		/**load your own library
			or add in application/config/autoload/ under libraries
		**/
		//$this->load->library('MY_auth_lib');
	} 
	
	public function index(){
		$this->load->helper('form');
		
		$this->set_header_data(PROJECT_NAME);
			
		$dataIndex['session'] = $this->session->all_userdata();
		$dataIndex['privs'] = $this->data;
		$this->load->view('main/index',$dataIndex);
		
		$this->load->view('templates/footer'); 	
	}
	
}
?>
<?php
Class Users extends Auth_Controller{
	var $data= array();
	public function __construct(){
		parent::__construct();
		$this->load->model('users_model');
		$this->load->database();
		$this->has_permission(179);
		$this->data = $this->get_privs();
	}
	
	public function index($all=0){
		$this->set_header_data(PROJECT_NAME.':Users','User Time Tracker');
		
		$data['privs']= $this->data;
		$data['users'] = $this->get_users($all);
		$data['userTypes'] = $this->getLookup('user_types',1);
        $data['showAll'] = $all;
		$this->load->view('users/index',$data);
		
		$this->load->view('templates/footer');
	}
	
	public function time_tracker(){
		
		$dataHeader['timestatus'] = $this->check_time_status();
		$dataHeader['title'] = 'User Time Tracker';
		$dataHeader['sub_title'] = 'Time Clock Tracker';
		$dataHeader['is_logged'] = $this->session->userdata('logged_in');
		$dataHeader['privs'] = $this->data;
		
		$this->load->view('templates/header',$dataHeader);
		
		$data['users'] = $this->get_users_grp_by_id();
		$this->load->view('users/time_tracker',$data);
		
		$this->load->view('templates/footer');
		
	}
	
	public function seach_time_logs(){
		$data['timedetails'] = $this->users_model->seach_time_logs();
		$data['users'] = $this->get_users_grp_by_id();
		$this->load->view('users/time_logs',$data);
		
	}
	
	public function add(){  
		$data['userTypes'] = $this->getLookup('user_types');
		$this->load->view('users/add',$data); 
	}
	
	public function edit($username)
	{
		$data['user_privs']= $this->users_model->reconstruct_privs($this->users_model->get_user_privileges($username));
		$data['rigths']= $this->users_model->get_rights();
		$data['user'] = $this->users_model->get_user_by_id($username);
		$data['userTypes'] = $this->getLookup('user_types');
		$this->load->view('users/edit',$data);
	}
	
	
	//action  =0 insert; 1 = update
	public function save($action=0){
		
		if($action == 0)
			$result = $this->users_model->save();
		elseif($action == 1)
			$result = $this->users_model->update();
		
		if($result > 0)
			echo  get_err_msgs($result);
		else
			echo 'Saved';		
		
	}


    function active(){
        $post = $this->input->post();
        $this->db->update('users',$post,array('user_name'=>$post['user_name']));
    }
		
	public function profile($user_name,$data=array()){
		$dataHeader['title'] = 'Chat User';
		$dataHeader['sub_title'] = 'User Profile Picture: '.strtoupper($user_name);
		$this->load->view('templates/header',$dataHeader);
		 
		$data['agent_id']	= $user_name;
		$this->load->view('users/profile', $data);
		
		$this->load->view('templates/footer'); 
	}
		
		
	public function do_upload_profile($user_name){
		$config = array(
			'upload_path' => "./uploads/profile/",
			'allowed_types' => "gif|jpg|png|jpeg",
			'overwrite' => TRUE,
			'max_size' => "2048000", // Can be set to particular file size , here it is 2 MB(2048 Kb)
			'max_height' => "3000",
			'max_width' => "3000", 
			'file_name' => $user_name
		);
		
		$this->load->library('upload', $config);
		$data['agent_id'] = $user_name;
		
		if($this->upload->do_upload())
		{
			$data['success'] 			= true;
			$data['upload_data'] 	= $this->upload->data();
			
			//save on the DB
			$this->saveUploadedImage($data['upload_data'],$user_name);
		  $this->profile($user_name,$data);
			
		}else{
			
			$data['error'] =  $this->upload->display_errors();
		  $this->profile($user_name,$data);
			
		}
	}
	
	/**
	* Save the info of selected images on DB
	**/
	public function saveUploadedImage($data,$user_name){
			$data = array(
					'profile_img'=>$data['file_name'], 
					'profile_path'=>$data['full_path']
				);
		$where_arr = array('user_name'=>$user_name);
		
		$this->db->where($where_arr)->update('users',$data);
	}

}
?>
<?php    
Class Chat extends Auth_Controller  { 
	var $data = array();
	//use `Auth_Controller` if you want the page to be validated if the user is logged in or not, if you want to disable this then use `CI_Controller` instead
	public  function __construct(){
		parent::__construct();
		$this->load->model('chat_model');
		$this->load->helper('url');
		$this->load->library('session');  
		$this->load->helper(array('form', 'url'));  
		$this->privs = $this->get_privs();
		
	} 
	
	/**
	* Load the users
	**/
	public function user_list(){
		$this->load->helper('form'); 
		$data['session'] = $this->session->all_userdata();
		$data['privs'] = $this->privs;
		
		$user_list = $this->chat_model->getChatUserList();
		$unread_user_chat = $this->chat_model->getUnReadChat();
		$user_list = $this->chat_model->re_arrange_ulist($user_list,$unread_user_chat);
		
		if(1){ //trial remove once done
			$data['gc_list'] = $this->chat_model->getInvolvedGC();
			$data['unread_group_chat'] = $this->chat_model->getUnreadGroupChat();
		}
		
		$data['users'] = $user_list;
		$data['unread_user_chat'] = $unread_user_chat;
		$data['user_type'] = $this->session->userdata('user_type');
		$this->load->view('chat/user_list',$data);
		 
	}
	
	public function chat_box($participant_id){
		$data['user_type'] = $this->session->userdata('user_type');
		$participant_info = $this->get_user_grp_by_id($participant_id);
		$data['participant_name'] =  $participant_info[$participant_id];
		$this->chat_model->participants[] = $participant_id;  
		$data['chat_id'] = $this->chat_model->get_chat_id();
		$data['target_user_id'] = $participant_id;
		$this->load->view('chat/chat_box',$data);
	}
	
	public function chat_logs($chat_id,$limit='',$do_export=false,$participant_id=''){
		$this->chat_model->chat_id = $chat_id;
		$data['logs'] =  $this->chat_model->get_chat_logs($limit);	
		
		$data['current_user'] = $this->session->userdata('username');
		$data['user_type'] = $this->session->userdata('user_type');
		$data['chat_id'] = $chat_id;
		$data['privs'] = $this->privs;
		
		if(!empty($participant_id)){ //if this param is given get the name of the participant, commonly used during exporting
			$participant_info = $this->get_user_grp_by_id($participant_id);
			$data['participant_name'] =  $participant_info[$participant_id];
		}
		
		$data['do_export'] = $do_export;
		
		$this->load->view('chat/chat_logs',$data);	
	}
	 
	public function save($chat_id){
		$this->chat_model->chat_id = $chat_id;
		$this->chat_model->save();		
		
		$attachment = $this->do_upload_attachment();
		
		if(isset($attachment['success']) && $attachment['success']){
			//if there's an attachement then save it into the chat_log with is_file = 1
			$this->chat_model->saveAttachment($attachment);		
		}else{
			//TODO display error
			if(isset($attachment['error']) && strpos($attachment['error'],'upload_no_file_selected')=== false){
				echo $attachment['error'];
			}
			
		}
	} 
	
	public function do_upload_attachment(){
		
		$config = array(
			'upload_path' => "./uploads/chat_attachment/",
			'allowed_types' => "gif|jpg|png|jpeg|txt|xls|xlsx|doc|docx|pdf|mp4",
			'overwrite' => TRUE,
			'max_size' => "20480", // Can be set to particular file size , 10mbs
			'max_height' => "3000",
			'max_width' => "3000",  
		);
		
		$this->load->library('upload', $config);
		
		if($this->upload->do_upload())
		{
			$data['success'] 			= true;
			$data['upload_data'] 	= $this->upload->data();
			
			//save on the DB
			//$this->saveUploadedImage($data['upload_data'],$user_name);
			
		}else{
			
			$data['error'] =  $this->upload->display_errors();  
			//var_dump($data);
			// exit();
			
		}
		
		return $data;
	}
	
	/**
	* $chat_id the internal id from chat table
	* $chat_log_id internal id from chat_logs table
	* $status status to be set on chat_logs table
	**/
	public function update_chat_status($chat_id,$chat_log_id,$status){
		$this->db->where('id',$chat_log_id)->update('chat_logs',array('chat_status'=>$status));
		
		$this->chat_model->chat_id = $chat_id;
		if($status == 'completed'){
			$this->chat_model->markAsUnread(); 
			$this->chat_model->markAsRead();
		}
	}
	/**
	* Export chat log for particular participant(agent)
	**/
	public function export_chat_log($chat_id,$participant_id){
		$this->chat_logs($chat_id,'all',true,$participant_id);
	}
	
	
	/**
	* GROUP CHAT starts here
	**/
	public function gc_chat_box($chat_id){
		$data['user_type'] = $this->session->userdata('user_type');
		$gc_info = $this->chat_model->getChatInfo($chat_id);
		$data['participant_name'] =  $gc_info[0]['chat_name'];
		$data['target_user_id'] =  '';//not used just to remove warning msg
		// echo '>>'.$chat_id;
		$data['chat_id'] = $chat_id;
		$this->load->view('chat/chat_box',$data);
	}
	
	public function createGC(){
		$this->chat_model->createGC();
	}
	
	public function edit_gc($chat_id){
		
		$data['chat_id'] = $chat_id;
		$data['user_type'] = $this->session->userdata('user_type');
		
		$gc_info = $this->chat_model->getChatInfo($chat_id);
		$data['gc_info'] = $gc_info[0];
		
		$data['participants'] = $this->chat_model->getGCParticipants($chat_id);
		
		$user_list = $this->chat_model->getChatUserList();
		
		$data['users'] = $user_list;
		$this->load->view('chat/edit_gc',$data);
	}
	
	public function updateGC($chat_id){
		$this->chat_model->updateGC($chat_id);
	}
}
?>
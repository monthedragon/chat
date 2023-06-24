<?php
class Chat_model extends CI_Model {
	public $participants = array();
	public $chat_id = 0;
	
	public function __construct()
	{
		$this->load->database();
	}
	
	public function getChatUserList(){
		$user_role = $this->session->userdata('user_type');
		$username = $this->session->userdata('username');
		
		if($user_role == AGENT_CODE){ //FOR ALL AGENTS show ONLY ADMIN USER LIST
		
			$this->db->where('user_type',ADMIN_CODE);
			
		}elseif($user_role == SUPPORT_CODE){ //FOR ALL SUPPORT show ONLY ADMIN AND SUPPORT LIST
		
			$this->db->where_in('user_type',array(ADMIN_CODE,SUPPORT_CODE));
			
		}elseif($user_role == ADMIN_CODE){ //SHOW ALL USERS
			
		}else{ //catcher nothing to show
		
			$this->db->where('user_type','');
			
		}
		
		$this->db->where('user_name !=', $username); //dont show yourself
		
		$retVal = array();
		$this->db->select('*')->from('users')->where('is_active',1);

		$result = $this->db->order_by('user_type,is_active,firstname,lastname')->get()->result_array();
		
		$user_list = array();
		foreach($result as $details){
			$user_list[$details['user_name']] = $details;
		}
		
		return $user_list;
	}

	public function get_chat_id(){
		$username = $this->session->userdata('username');
		$result = $this->db->select('chat_users.chat_id')
						   ->from('chat_users')
						   ->join('chat_users as participant_user','chat_users.chat_id = participant_user.chat_id')
						   ->join('chat ','chat.id  = chat_users.chat_id AND chat.chat_type = "solo"')
						   ->where_in('participant_user.user_id',$this->participants)
						   ->where('chat_users.user_id',$username)
						   ->get()
						   ->result_array(); 
		//echo $this->db->last_query();
		$chat_id = 0;
		
		if($result){
			$chat_id =  $result[0]['chat_id'];
			$this->chat_id  = $chat_id;
		}else{
			$this->init_chat();
			$chat_id = $this->chat_id;
		}
		return $chat_id;
	}
	
	/**
	* Create data for chat and chat_users
	**/
	public function init_chat(){
		$this->create_chat();
		$this->create_chat_users();
	}

	/**
	* Create chat data
	**/	
	public function create_chat(){
		$username = $this->session->userdata('username');
		$chat_type = 'solo'; //for the meantime SOLO is available
		$chat_name = 'solo'; //if multiple chate will be available chat name should be applied
		$data = array(
						'created_by'=>$username,
						'chat_type'=>$chat_type, //
						'chat_name'=>$chat_name
					);
		$this->db->insert('chat',$data);
		$this->chat_id = $this->db->insert_id();
	}
	
	/**
	* Create chat_users
	**/
	public function create_chat_users(){ 
		$username = $this->session->userdata('username');
		$this->participants[] =  $username; 
		
		//set read as 1 initially
		foreach($this->participants as $user_id){
			$data = array(
						'chat_id'=>$this->chat_id,
						'user_id'=>$user_id,
						'is_read'=>1
					);
			$this->db->insert('chat_users',$data);
		}
	}
	
	public function get_chat_logs($limit=''){
		
		if(empty($limit)){
			$limit = CHAT_LOG_PER_ROW;
		}
		
		$this->chat_log_result = array();
		$this->db->select('chat_logs.*,chat_logs.id as chat_log_id, users.firstname, users.lastname, users.user_type ')
							->from('chat_logs')
							->join('users','users.user_name = chat_logs.created_by ')
							->where('chat_id',$this->chat_id);
		
		if($limit != 'all'){
				$this->db->limit($limit);
		}					
							
		$result = $this->db->order_by('chat_logs.date_entered DESC')
							->get()
							->result_array(); 
		//chekc later if LIMIT is needed 
		// echo $this->db->last_query();
		// echo 'test';
				
		$this->markAsRead();
		return array_reverse($result);
	}
	
	public function save($attachment_details=''){
		$username = $this->session->userdata('username');
		$pdata = $this->input->post();
		
		$file_ext = '';
		$file_name = '';
		$is_file = 0;
		$message =  $pdata['message'];
		
		if($attachment_details != ''){ 
			//if there's an attachement save the full_path!
			$is_file = 1;
			$message = $attachment_details['upload_data']['full_path'];
			$file_name = $attachment_details['upload_data']['orig_name'];
			$file_ext  = $attachment_details['upload_data']['file_ext'];
		}

		if(empty($message)) return ;
		
		$data = array(
					'chat_id'=>$this->chat_id,
					'message'=>$message,
					'created_by'=>$username,
					'date_entered'=>date('Y-m-d H:i:s'),
					'is_file'=>$is_file,
					'file_name'=>$file_name,
					'file_ext'=>$file_ext
		);
		$this->db->insert('chat_logs',$data);
		$this->markAsUnread(); 
		$this->markAsRead();
	}
	
	/**
	* Entry point of saving the attachment !
	*/
	public function saveAttachment($attachment_details){
		 $this->save($attachment_details);
	}
	
	/**
	* Mark ever users in the CHAT to be unread so that notification will be sent to them
	*/
	public function markAsUnread(){
		$username = $this->session->userdata('username');
		$this->db->set('is_read',0)
						->where('chat_id',$this->chat_id)
						->where('user_id !=',$username)
						->update('chat_users');
	}
	
	/**
	* Mark as read 
	*/
	public function markAsRead(){
		$username = $this->session->userdata('username');
		$this->db->set('is_read',1)
						->where('chat_id',$this->chat_id)
						->where('user_id',$username)
						->update('chat_users');
	}
	
	public function getUnReadChat(){
		$username = $this->session->userdata('username');
		
		$result = $this->db->select('chat_users.user_id')
											->from('chat_users')
											->join('chat_users AS logged_user',"logged_user.chat_id = chat_users.chat_id AND logged_user.user_id = '{$username}' AND logged_user.is_read = 0")
											->join('chat ','chat.id  = chat_users.chat_id AND chat.chat_type = "solo"')
											->where('chat_users.user_id !=', $username)
											->order_by('chat_users.user_id')
											->get()->result_array();
						
		$unread = array();
		
		//In later once GROUP CHAT requested better appened the chat_id from the LIST and check it using chat_id if the thread is unread or not
		foreach($result as $details){
			$unread[$details['user_id']] = $details;
		}
		
		return $unread;
		
	}
	
	/**
	* Re-arrange the list of the user to be displayed in the chat list
	* Set from above those user who has unread messages
	**/
	public function re_arrange_ulist($user_list,$unread_user_chat){
		$final_list = array();
		foreach($unread_user_chat as $key=>$details){
				if(!isset($user_list[$key])) continue;
				
				$final_list[$key] = $user_list[$key];
				unset($user_list[$key]);
		}
		
		if($user_list){
			$final_list += $user_list;
		}
		
		return $final_list;
	}

	public function getInvolvedGC(){
		
		$username = $this->session->userdata('username');
		
		$result = $this->db->select('chat.id AS chat_id, chat_name')
				->from('chat')
				->join('chat_users',"chat_users.chat_id = chat.id ")
				->join('chat_users AS logged_user',"logged_user.chat_id = chat_users.chat_id AND logged_user.user_id = '{$username}'")
				->where('chat_users.user_id !=', $username)
				->where('chat_type','group')
				->group_by('chat.id')
				->order_by('chat.chat_name')
				->get()->result_array();
		
		return $result;
	}
	
	/**
	* Start of Group Chat
	*/
	
	public function getChatInfo($chat_id){
		return $this->db->select('*')
					->from('chat')
					->where('id',$chat_id)
					->get()->result_array();
	}
	
	
	
	public function getUnreadGroupChat(){
		$username = $this->session->userdata('username');
		
		$result = $this->db->select('chat.id AS chat_id')
											->from('chat_users')
											->join('chat_users AS logged_user',"logged_user.chat_id = chat_users.chat_id AND logged_user.user_id = '{$username}' AND logged_user.is_read = 0")
											->join('chat ','chat.id  = chat_users.chat_id AND chat.chat_type = "group"')
											->where('chat_users.user_id !=', $username)
											->order_by('chat_users.user_id')
											->get()->result_array();
						
		$unread = array();
		
		//In later once GROUP CHAT requested better appened the chat_id from the LIST and check it using chat_id if the thread is unread or not
		foreach($result as $details){
			$unread[$details['chat_id']] = $details;
		}
		
		return $unread;
	}
	
	public function createGC(){
		$pdata = $this->input->post();
		$gc_name = $pdata['gc_name'];
		$err = false;
		
		if($pdata['participants']){
			
			if($this->gcNameDuplicate($gc_name)){
				$err = true;
				$err_msg = 'Duplicate GC name';
			}else{
			
				$username = $this->session->userdata('username');
			
				
				$data = array(
							'created_by'=>$username,
							'chat_type'=>'group',
							'chat_name'=>$gc_name
						);
						
				$this->db->insert('chat',$data);
				$this->group_chat_id = $this->db->insert_id();
				$this->createGCChatUsers();
			}
			
		}else{
			$err = true;
			$err_msg = 'Please select participants to this GC';
		}
		
		if($err){
			ob_clean();
			echo $err_msg;
		}
	}
	
	public function gcNameDuplicate($gc_name, $chat_id = ''){
		$this->db->select('id')
							->from('chat')
							->where('chat_name', $gc_name);
							
		if($chat_id){
			$this->db->where('chat.id !=',$chat_id);
		}			
		
		$result = $this->db->get()->result_array();
		
		if($result){
			return true;
		}
		
		return false;
	}
	
	
	/**
	* Create chat_users
	**/
	public function createGCChatUsers(){ 
		$pdata = $this->input->post();
		
		$participants = $pdata['participants'];
		
		$username = $this->session->userdata('username'); //add currently logged in user
		$participants[] =  $username; 
		
		//set read as 1 initially
		foreach($participants as $user_id){
			$data = array(
						'chat_id'=>$this->group_chat_id,
						'user_id'=>$user_id,
						'is_read'=>1
					);
			$this->db->insert('chat_users',$data);
		}
	}
	
	function getGCParticipants($chat_id){
		$chat_users = $this->db->select('*')->from('chat_users')->where('chat_id',$chat_id)->get()->result_array();
		
		$participants_arr = array();
		foreach($chat_users as $details){
			$participants_arr[$details['user_id']] = true;
		}
		
		return $participants_arr;
	}
	
	
	
	public function updateGC($chat_id){
		
		$pdata = $this->input->post();
		$gc_name = $pdata['gc_name'];
		$err = false;
		
		if($pdata['participants']){
			
			if($this->gcNameDuplicate($gc_name, $chat_id)){
				$err = true;
				$err_msg = 'Duplicate GC name';
			}else{
			
				$this->db->update('chat',array('chat_name'=>$gc_name),array('id'=>$chat_id));
				$this->group_chat_id = $chat_id;
				$this->updateGCParticipants();
			}
			
		}else{
			$err = true;
			$err_msg = 'Please select participants to this GC';
		}
		
		if($err){
			ob_clean();
			echo $err_msg;
		}
	}
	
	//When updating the participant, the logic will involve a delete-insert approach. 
	//One possible issue to consider is when there are unread messages for any of the participants. 
	//And if the GC is updated, then the unread messages will be automatically marked as read. 
	//As a result, notifications from the new chat will disappear until a new chat message comes in.
	function updateGCParticipants(){
		$this->db->where('chat_id',$this->group_chat_id)
				->delete('chat_users');
		$this->createGCChatUsers();
	}
}
<?php
Class Users_model extends CI_Model {
	public function __construct()
	{
		$this->load->database();
	}
	
	function get_user_by_id($username)
	{
		return $this->db->select('*')
					->from('users')
					->where('user_name',$username)
					->get()
					->result_array();
	}
	
	function get_rights()
	{
		return $this->db->select('*')
					->from('rights')
					->where('is_active',1)
					->order_by('right_group,right_name')
					->get()
					->result_array();;
	}
	
	function get_user_privileges($username)
	{
		return $this->db->select('*')
					->from('privilege')
					->where('user_id',$username)
					->where('is_active',1)
					->get()
					->result_array();
	}
	
	function reconstruct_privs($privs)
	{
		$retVal = array();
		
		foreach($privs as $d)
			$retVal[$d['right_id']] = 1;
		
		return $retVal;
	}	
	
	function seach_time_logs(){
		$pdata = $this->input->post();
		
		$this->db->select('*')
		->from('login_trans_log')
		->where('time_stamp >=',$pdata['startDate'] . ' 00:00:00')
		->where('time_stamp <=',$pdata['endDate'] . ' 23:59:59');
		
		if($pdata['users'] != '') 
		     $this->db->where('user_id',$pdata['users']);
		
		$query = $this->db->order_by('time_stamp')
				->get()
				->result_array();
				
		return $query;
	
	}
	
	function save(){
		$pdata = $this->input->post();  
		
		$result = $this->db->select('*')
				->from('users')
				->where('user_name',$pdata['user_name'])
				->get();
		
		if($result->num_rows() > 0)
			return 2;
		else{
			$pdata['user_password'] = md5("${pdata['user_password']}");
			$this->db->insert('users',$pdata);
		}
	}
	
	function update(){
		$pdata  = $this->input->post();
		$isResetPw =0;
		$privs = array();
		
		$username = $pdata['user_name'];
		
		
		if(isset($pdata['privs']))
		{
			$privs = $pdata['privs'];
			unset($pdata['privs']);
		}
		
		if(isset($pdata['reset_pw']))
		{		
			$isResetPw = 1;
			unset($pdata['reset_pw']);
		}
		
		if($isResetPw)
			$pdata['user_password']= md5($username);
			
		$this->db->where('user_name',$username)->update('users',$pdata);
		
		//revoke privileges
		$this->db->where('user_id',$username)->update('privilege',array('is_active'=>0));
		
		foreach($privs as $rightId=>$status)
		{
			$sql = "insert into privilege set right_id=$rightId,user_id='$username' on duplicate key update is_active = 1";
		
			$this->db->query($sql);
		}
		#$privs['is_active'] = 1;
		#$this->db->on_duplicate('privilege',$privs);
		
	}
	
	
	
	
	
	
}
?>
<?php
class Main_model extends CI_Model {
	var $retVal = array();

	public function __construct()
	{
		$this->load->database();
	}
	
	public function change_conn($cd){
		$config['hostname'] = $cd['hostname'];
		$config['username'] = $cd['username'];
		$config['password'] = $cd['password'];
		$config['database'] = $cd['campaign_db'];
		$config['dbdriver'] = "mysql";
		$config['dbprefix'] = "";
		$config['pconnect'] = FALSE;
		$config['db_debug'] = TRUE;

		return $this->load->database($config,true);
	}
	
	function get_chat_user_list(){
		$user_role = $this->session->userdata('user_type');
		$this->db->select("*")
			->from('trans_log')
			->where('contact_id',$id);
		
	}
	
	function get_history($id){
		$user_role = $this->session->userdata('user_type');
		//mon as of May 14 2016
		//This is to show again the history to all users but ONLY CB log will be available
		//ONly ADMIN user can show every logs
		$this->db->select("trans_log.*")
				->from('trans_log')
				->where('contact_id',$id);
				
		if($user_role != ADMIN_CODE){
			$this->db->where('callresult','CB');
		}
		
		return $this->db->order_by('time_stamp','DESC')
					->get()
					->result_array();
	}
	
	 
	public function get_allocated_leads($userid)
	{ 
		$retVal = array();
		$result = $this->db->select('lead_identity,
								count(case when calldate is null then 1 else null end ) as VIRGIN')
				->from('contact_list')
				->where('is_active',1)
				->where('assigned_agent',$userid)
				->group_by('lead_identity')
				->get()
				->result_array();
				
		foreach($result as $r)
			$this->retVal[$r['lead_identity']]['V'] = $r['VIRGIN'];
			
		$this->get_allocated_leads_touched($userid);
		
		
		return $this->retVal;
	} 
}
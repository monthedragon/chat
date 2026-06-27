<?php
class Security_model extends CI_Model {
	public function __construct()
	{
		$this->load->database(); 		
	}

    function login() {

        $pdata = $this->input->post();
        $username = $pdata['user_name'];

        $sql = "SELECT * FROM users
        WHERE user_name = ?
        AND user_password = md5(?)
        AND is_active = 1";

        $query = $this->db->query($sql, array(
            $username,
            $pdata['user_password']
        ));

        if ($query->num_rows() > 0) {

            $result = $query->result_array();
            $user   = $result[0];

            $this->flushOldSession($username);

            // Set user privileges (your existing method)
            $this->set_user_privileges($username);

            // Set session data
            $this->session->set_userdata(array(
                'username'     => $username,
                'logged_in'    => TRUE,
                'full_name'    => $user['firstname'] . ' ' . $user['lastname'],
                'user_type'    => $user['user_type'],
            ));

            return 1;

        } else {
            return 0;
        }
    }

    public function flushOldSession($username){
        $this->db->like('user_data', '"username";s:' . strlen($username) . ':"' . $username . '"')->delete('ci_sessions');    }
	
	function set_user_privileges($username)
	{
		$result = $this->db->select('*')
					->from('privilege')
					->where('user_id',$username)
					->where('is_active',1)
					->get()
					->result_array();
		
		$privs = array();	
		foreach($result as $r)
			$privs[$r['right_id']]= 1;
		
		$this->session->set_userdata(array('privs'=>$privs));
	}
	
	//$process = 1 timein ; 0 timeout
	public function do_time_proc($process){
		if(!$this->session->userdata('logged_in'))
			redirect('security/login');
		else{
			$this->load->database();
			$username = $this->session->userdata('username');
			$this->db->where("user_name",$username)->update('users',array('log_status'=>$process));
			
			
			$insertData = array('user_id'=>$username,
								'log_status'=>$process,
								'ip_address'=>$this->session->userdata('ip_address')
								);
								
			//log every time process
			$this->db->insert('login_trans_log',$insertData);
		}
	}

	function clock_details(){
		$username = $this->session->userdata('username');
		
		$result = $this->db->select('*')->from('login_trans_log')
				->where('user_id',$username)
				->order_by('time_stamp')
				->get()
				->result_array();
		
		return $result;
	}
	
	public function cp_save()
	{
		$username = $this->session->userdata('username');
		$pdata = $this->input->post();
		$oldPw = md5(trim($pdata['old_password']));
		$newPw = (trim($pdata['password']));
		$repPw = (trim($pdata['rep_password'])); //repeat pw
		
		$result = $this->db->select('user_password')->from('users')
				->where('user_name',$username)
				->get()->result_array();
				
		
		if($result[0]['user_password'] != $oldPw)
			return 3;
		elseif($newPw != $repPw)
			return 4;
		elseif(strlen($newPw) <5)
			return 5;
		else{
			$this->db->where('user_name',$username)->update('users',array('user_password'=>md5($newPw)));
			return 1;
		}
	}
}
?>

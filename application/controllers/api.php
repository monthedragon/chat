<?php    
Class Api extends CI_Controller  {
	var $data = array();
	//use `Auth_Controller` if you want the page to be validated if the user is logged in or not, if you want to disable this then use `CI_Controller` instead
	public  function __construct(){
		parent::__construct();
        $this->load->model('chat_model');
        $this->load->model('users_model');
		$this->load->helper('url');
		$this->load->library('session');  
		$this->load->helper(array('form', 'url'));  
//		$this->privs = $this->get_privs();
		
	}

    public function sendMessage()
    {
        // Tell the client that the response is JSON
        $this->output->set_content_type('application/json');

        // 1. Validate API secret
        $secret = $this->input->post('secret');

        if ($secret !== API_CHAT_SECRET_KEY) {
            return $this->output
                ->set_status_header(401)
                ->set_output(json_encode(array(
                    'ok' => false,
                    'error' => 'Unauthorized.'
                )));
        }

        // 2. Get POST data
        $pdata     = $this->input->post();
        $sender = isset($pdata['sender']) ? $pdata['sender'] : '';
        $recipient = isset($pdata['recipient']) ? $pdata['recipient'] : '';
        $message   = isset($pdata['message']) ? $pdata['message'] : '';

        // Create a temporary session identity
        $this->session->set_userdata('username', $sender);

        // 3. Validate required fields
        if (empty($sender) || empty($recipient) || empty($message)) {
            return $this->output
                ->set_status_header(400)
                ->set_output(json_encode(array(
                    'ok' => false,
                    'error' => 'Sender, recipient, and message are required'
                )));
        }

        // 4. Check recipient
        // If recipient does not exist:
        $userErr = array();

        if (!$this->isChatUserExists($recipient)) {
            $userErr[] = $recipient;
        }

        if (!$this->isChatUserExists($sender)) {
            $userErr[] = $sender;
        }

        if (!empty($userErr)) {
            return $this->output
                ->set_status_header(404)
                ->set_output(json_encode([
                    'ok' => false,
                    'error' => 'User not found: ' . implode(', ', $userErr)
                ]));
        }

        // 5. Find or create a SOLO chat
        $this->chat_model->participants[] = $recipient;
        $chat_id = $this->chat_model->get_chat_id();

        $this->chat_model->chat_id = $chat_id;

        // 6. Save message
        $this->chat_model->save();

        // 7. Success
        return $this->output
            ->set_status_header(200)
            ->set_output(json_encode(array(
                'ok' => true,
                'chat_id' => $chat_id
            )));
    }

    private function isChatUserExists($username)
    {
        $userInfo = $this->users_model->get_user_by_id($username);
        return !empty($userInfo);
    }

}
?>
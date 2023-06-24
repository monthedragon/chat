<?php
	
	foreach($users as $details){
		$unread_cls = '';
		if(isset($unread_user_chat[$details['user_name']])){
			$unread_cls = 'div-unread';
		}
		echo "<div class='div-user-chat cursor {$unread_cls}' user_id='{$details['user_name']}' style='display:block'>{$details['firstname']}  {$details['lastname']}</div>";
	}
	
?>

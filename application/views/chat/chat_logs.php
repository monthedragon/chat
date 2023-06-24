<?
if($do_export){
	header('Content-Type: application/vnd.ms-excel'); //mime type
	header('Content-Disposition: attachment;filename="'.$participant_name.'.xls"'); //tell browser what's the file name
	header('Cache-Control: max-age=0');
}

$img_ext_array  = array('jpg','png','gif');

//source of svg: https://www.flaticon.com/packs/file-types
//svg file should have equivalent on this folder: /uploads/icon
$svg_arrays = array('xlsx','xls','pdf','doc');
?>
<table id='tbl-chat' width=100% >
<tr>
	<td style='width:15%'></td>
	<td style='width:75%'></td>
	<td style='width:15%'></td>
</tr>
<?php 
	$old_msg_user = '';
	foreach($logs as $details){
		
		if($old_msg_user != $details['created_by']){
			$old_msg_user = $details['created_by'];
			$chat_by = $details['firstname'].' '.$details['lastname'];
		}else{
			$chat_by = '';
		}
		
		if($current_user == $details['created_by']){
			
			$owner = true;
			$msg_box_class = 'own_msg';
			$owner_user = $chat_by;
			$other_user	= '';
			$datetime_cls = 'own_msg_datetime';
			
		}else{
			
			$owner = false;
			$msg_box_class = 'other_msg';
			$owner_user = '';
			$other_user = $chat_by;
			$datetime_cls = 'other_msg_datetime';
		}
		
		$log_html =  "<tr chat_logid='{}'>";
		$log_html .= '<td valign=top>'.$other_user.'</td>';
		
		$chat_triger_status = ''; 
		//ONLY ADMIN user and CHAT MESSAGE from AGENT can tag as COMPLETED 
		//and has access to privs 199 :chat update to completed or new
		if(!$do_export){ //dont show during export
			
			if((isset($privs[199]) || $user_type == ADMIN_CODE) && !$owner){
				//as of 2019-05-25 JHe requested to make the "SET AS COMPLETED" availabe to all user not only on the agent
				//this code was removed "&& $details['user_type'] == AGENT_CODE"
				
				if($details['chat_status'] == 'new'){ //set the status to completed
					$target_status = 'completed';
				}else{ //set status to new (re-tag)
					$target_status = 'new';
				}
				$chat_triger_status  = " <span status = '{$target_status}' class='spn_chat_status cursor-pointer ' chat_log_id='{$details['chat_log_id']}'>set as {$target_status}</span> |";	
				
			}
		}
		
		$chat_log_status = '';
		if($details['chat_status'] == 'completed'){ 
			//show the current status of the chat log
			if($owner){
				$status_color = 'yellow';
			}else{
				$status_color = 'red';
			}
			
			$chat_log_status = "<div class='chat_log_completed status_{$status_color}'>{$details['chat_status']}</div>";
		}
		
		$time_msg  = "<div class='{$datetime_cls}'>{$chat_triger_status}  {$details['date_entered']}</div>";
		
		if($details['is_file']){
			
			$file_name = $details['file_name'];
			$source_link = base_url()."uploads/chat_attachment/".$file_name;
			$file_ext = strtolower(str_replace('.','',$details['file_ext']));
			
			if(in_array($file_ext,$img_ext_array)){
				$file_view_path = base_url()."uploads/chat_attachment/".$details['file_name'];
			}elseif(in_array($file_ext,$svg_arrays)){
				$file_view_path = base_url()."uploads/icon/{$file_ext}.svg";
			}else{
				$file_view_path = base_url()."uploads/icon/default.svg";
			}
			
			$file_msg = "<img src='{$file_view_path}' width=50px>";
			$chat_message = "<a href='{$source_link}' target=_blank> {$file_msg} </a>";
			$chat_message .= "<br><i>{$file_name}</i>";
			
		}else{
			$chat_message = $chat_log_status . nl2br($details['message']);
		}
		
		$log_html .= "<td valign=top> <div class='{$msg_box_class}'>". $chat_message . $time_msg.'</div></td>';
		
		$log_html .= '<td valign=top>'.$owner_user.'</td>';
		
		$log_html .= '</tr>';
		
		echo $log_html;	
	}
?>
</table>

<script>
	$(function(){
		var url_chat_thread = '<?=base_url()?>chat/chat_logs/<?=$chat_id?>';
		
		$('.spn_chat_status').click(function(){
			var status = $(this).attr('status');	
			var chat_log_id = $(this).attr('chat_log_id');
			
			$.ajax({
				url: '<?=base_url()?>chat/update_chat_status/<?=$chat_id?>/'+chat_log_id+'/'+status,
				success:function(data){
					
					//reload the chat log
					$.ajax({
							url: url_chat_thread,
							success:function(data){
								$('#msg-chat-log').html(data);
							}
						})
						
				}
			})
			
		})
	})
</script>
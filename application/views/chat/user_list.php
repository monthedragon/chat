<?php
	
echo "<form id = 'frm-user-list'>";	

if($user_type == ADMIN_CODE){
	echo "<input type='input' class=required name='gc_name' style='width:80%;margin-bottom:10px;'>";
	echo "<input type=submit value='Create Group chat' id='btn-crate-gc' style='width:83%' class='button'><br>";
	echo '<hr>';
	
}
	echo "<div style='max-height:350px;overflow-x:hidden;overflow-y:auto;'>";
	foreach($users as $details){
		$unread_cls = '';
		if(isset($unread_user_chat[$details['user_name']])){
			$unread_cls = 'div-unread';
		}
		
		$checkbox = ($user_type == ADMIN_CODE) ? "<input type=checkbox value='{$details['user_name']}' class='cursor-pointer' name='participants[]'>" : '';
		echo "{$checkbox}<span style='display:inline-block;height:15px;width:80%;' class='div-user-chat cursor-pointer {$unread_cls}' user_id='{$details['user_name']}' >  {$details['firstname']}  {$details['lastname']}</span>";
		echo '<br>';
	}
	echo '</div>';
echo '</form>';

	if(isset($gc_list)){
		echo '<hr>';
		//2023-02-26 (CHAT GC)
		foreach($gc_list as $details){
			$unread_cls = '';
			if(isset($unread_group_chat[$details['chat_id']])){
				$unread_cls = 'div-unread';
			}
			echo "<div style='width:80%;float:left;' class='div-gc-chat cursor-pointer {$unread_cls}' chat_id='{$details['chat_id']}'>{$details['chat_name']}</div>";
			
			if($user_type == ADMIN_CODE){
				echo "<div style='width:15%;float:right;' class='cursor-pointer e-link'  chat_id='{$details['chat_id']}'>edit</div>";
			}
			
		
		}
	}
?>

<script>
var default_title = '<?=PROJECT_NAME?>';
var position = 0;

//Make the title scrolling to notice easily the new messages
function scrolltitle() {
	var unreadCtr = $('.div-unread').length;
	if(unreadCtr > 0){
		if(unreadCtr == 1){
			msg = 'You have a message from ' + $('.div-unread').html() + ' . . . ';
		}else{
			msg = 'You have '+unreadCtr+' messages . . .';
		}
		
		document.title = msg.substring(position, msg.length) + msg.substring(0, position); 
		position++;
		if (position > msg.length) position = 0
		window.setTimeout("scrolltitle()",200);
	}
}


	$(function(){
		$(".div-user-chat, .div-gc-chat").mouseover(function(){$(this).addClass('highlight_cls');})
		$(".div-user-chat, .div-gc-chat").mouseout(function(){$(this).removeClass('highlight_cls');})
		
		$('.e-link').mouseover(function(){$(this).css('font-weight','bold')});
		$('.e-link').mouseout(function(){$(this).css('font-weight','normal')});
		
		$('#frm-user-list').submit(function(event){
			
			event.preventDefault();
			if($(this).valid()){
				$.ajax({
					url:'<?=base_url()?>chat/createGC',
					data:$(this).serialize(),
					type:'POST',
					// beforeSend:function(){$("#btnSubmit").val('please wait...').prop('disabled',true)},
					success:function(data){
						
						// console.log(data);
						if(data != ''){
							alert(data);
						}else{
							alert('Saved');
							location.reload(true);
						}
					
					}
				});
			}
			
		})
		
		if($('.div-unread').length > 0){
			scrolltitle();
		}else{
			document.title = default_title;
		}
		
		$('.div-user-chat').unbind('click');
		$('.div-user-chat').click(function(){
			var user_id = $(this).attr('user_id');
			var url  ='<?=base_url()?>chat/chat_box/'+user_id;
			var target_interval = $('#interval_holder').html(); //get the targeted intervalId to be cleared once new chat intialized
			
			do_ajax(url,'POST','','div-chat-log');
			
			clearInterval(target_interval); //clear previously created interval to avoid unexpected request
		})
		
		//start of GC
		$('.div-gc-chat').unbind('click');
		$('.div-gc-chat').click(function(){
			var chat_id = $(this).attr('chat_id');
			var url  ='<?=base_url()?>chat/gc_chat_box/'+chat_id;
			var target_interval = $('#interval_holder').html(); //get the targeted intervalId to be cleared once new chat intialized
			
			do_ajax(url,'POST','','div-chat-log');
			
			clearInterval(target_interval); //clear previously created interval to avoid unexpected request
		})
		
		//start of edit GC 
		
		$('.e-link').unbind('click');
		$('.e-link').click(function(){
			var chat_id = $(this).attr('chat_id');
			var url  ='<?=base_url()?>chat/edit_gc/'+chat_id;
			var target_interval = $('#interval_holder').html(); //get the targeted intervalId to be cleared once new chat intialized
			
			do_ajax(url,'POST','','div-chat-log');
			
			clearInterval(target_interval); //clear previously created interval to avoid unexpected request
		})
		
	})
</script>
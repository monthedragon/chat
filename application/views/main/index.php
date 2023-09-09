<!-- chat log interval holder -->
<span style='display:none' id='interval_holder'>s</span>

<div id='chat-list' style='min-height:500px;width:20%;border-right:1px solid gray;float:left'>
	<div id='user-list' style='height:50%;display:block'></div>
	<!--<div id='group-list' style='height:50%;display:block'>GROUP LIST </div>-->
</div>
<div style='height:500px;width:66%;border:0px solid gray;float:left;padding-left:10px'>

	<div id='div-chat-log' style='height:100%;display:block'></div>

	<!-- user list previous lengt holder -->
	<div id='prev-user-list-len' style='display:none'></div>

</div>
<script>
	function load_user_list() {

		$.ajax({
			url: '<?= base_url() ?>chat/user_list',
			success: function(data) {

				dataLen = data.length;
				userListLen = $('#prev-user-list-len').html();

				if (dataLen != userListLen) {
					$('#user-list').html(data);
					$('#prev-user-list-len').html(dataLen);
				}
			}
		})

	}

	$(function() {
		var chat_refresh_time = '<?= CHAT_REFRESH_UNREAD_TIME ?>'
		load_user_list();

		//OFF THE AUTO LOAD FOR CHAT 2017-05-05
		setInterval(load_user_list, chat_refresh_time); //every 2 secs check the user list if for notification purposes 

	})
</script>
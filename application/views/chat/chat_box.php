<div style='height:5%;display:block;'>
	<h2><?php echo $participant_name; ?>
		<?
		//2017-09-09 export button for chat
		if ($user_type == ADMIN_CODE || $user_type == SUPPORT_CODE) {
			$url = base_url() . 'chat/export_chat_log/' . $chat_id . '/' . $target_user_id;
			echo "<input type='button' class='button' value='export chat logs' onclick=\"window.location='{$url}'\">";
		}
		?>
	</h2>
</div>

<div id='msg-chat-log' style='height:70%;display:block; overflow: auto;'></div>

<!-- previous chat log len holder -->
<div id='prev-chat-log-len' style='display:none'></div>

<!-- previous MAX chat ROW  holder -->
<div id='prev-chat-log-per-row' style='display:none'></div>

<div style='height:20%;display:block;margin-top:30px'>
	<form id='frm-chat' nmae='frm-chat' method="post">
		<textarea name='message' id='txt-message' cols=140 rows=4></textarea>
		<?php
		if ($user_type == ADMIN_CODE || $user_type == SUPPORT_CODE) {
			echo "<input type='file' id='userfile' name='userfile' size='20' />";
		}
		?>
		<input type='submit' class='button' value='send'>
	</form>
</div>

<script>
	$(function() {
		var target_user_id = '<?= $target_user_id ?>';
		var chat_id = '<?= $chat_id ?>';
		var url_chat_thread = '<?= base_url() ?>chat/chat_logs/' + chat_id;
		var interval = 0;
		var chat_refresh_time = '<?= CHAT_REFRESH_TIME ?>';
		var chat_log_per_row = <?= CHAT_LOG_PER_ROW ?>;

		//Load chat log
		function load_chat_log(param_chat_log_per_row = '') {
			var do_scroll_down = true;
			if (param_chat_log_per_row == '') {
				if (chat_log_per_row != <?= CHAT_LOG_PER_ROW ?>) {
					param_chat_log_per_row = chat_log_per_row;
				}
			} else {
				do_scroll_down = false;
			}

			$.ajax({
				url: url_chat_thread + '/' + param_chat_log_per_row,
				success: function(data) {
					dataLen = data.length;
					chatLogLen = $('#prev-chat-log-len').html();

					if (dataLen != chatLogLen) {
						$('#msg-chat-log').html(data);

						if (do_scroll_down) {
							//scrolling to the bottom will only be applicable if there is no limit being passed
							$('#msg-chat-log').scrollTop(function() {
								return this.scrollHeight;
							});
						} else {
							//alert($('#tbl-chat').height());
							//TODO once it reloaded VIA scroll UP then the position of the SCROLL should retain
							$('#msg-chat-log').scrollTop(function() {
								return 20;
							});
						}

						$('#prev-chat-log-len').html(dataLen);
					}

				}
			})

		}

		load_chat_log(); //initialize chat log

		//OFF THE AUTO LOAD FOR CHAT 2017-05-05
		interval = setInterval(load_chat_log, chat_refresh_time); //every 2 secs load the chat log
		$('#interval_holder').html(interval); //set the id of interval this will be used later once new chat intialize


		//handle CTRL+ENTER as SUBMIT
		$('#txt-message').keydown(function(event) {

			if ((event.keyCode == 10 || event.keyCode == 13) && event.ctrlKey) {
				// Ctrl-Enter pressed
				$('#frm-chat').submit();
			}

		});

		//SUbmit the message
		$('#frm-chat').submit(function(event) {
			event.preventDefault();
			var fd = new FormData(document.getElementById("frm-chat"));

			//to include the FILE on AJAX upload! (amazing!)
			fd.append("label", "WEBUPLOAD");

			var url = '<?= base_url() ?>chat/save/' + chat_id;
			$.ajax({
				url: url,
				data: fd,
				type: 'POST',
				processData: false, // tell jQuery not to process the data
				contentType: false, // tell jQuery not to set contentType
				success: function(data) {
					$('#txt-message').val('').focus();
					$('#userfile').val('');
					load_chat_log();

					if (data != '') {
						alert('Error code: ' + data + ' \r\nPlease try again');
					}


				}
			})
		});

		//2017-09-09 viewing of OLD CHAT
		$("#msg-chat-log").scroll(function() {
			//viewing of CHAT HISTORY is restricted to ADMIN user ONLY
			if ('<?= $user_type ?>' == '<?= ADMIN_CODE ?>' || '<?= $user_type ?>' == '<?= SUPPORT_CODE ?>') {
				var div = $(this);
				if (div.scrollTop() == 0) {
					//sessionStorage.scrollTop = $(this).scrollTop();
					chat_log_per_row += <?= CHAT_LOG_PER_ROW ?>;
					load_chat_log(chat_log_per_row);
				}
			}
		});

		/*		
				tinyMCE.init({
						mode : "textareas",
						theme : "modern", 
						menubar: false,
				});
		*/

	})
</script>
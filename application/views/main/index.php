<link rel="stylesheet" href="<?= base_url() ?>assets/css/chat.css">

<span style='display:none' id='interval_holder'>s</span>

<div id='chat-list'>
    <div id='user-list' style='height:100%;display:block'></div>
</div>

<div style='height:600px;width:74%;float:left;padding-left:12px;'>
    <div id='div-chat-log' style='height:100%;display:flex;flex-direction:column;'></div>
    <div id='prev-user-list-len' style='display:none'></div>
</div>

<script>

    const ws = new WebSocket('ws://localhost:8080');

	function load_user_list() {
        console.log('laoding user list...')
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
		//setInterval(load_user_list, chat_refresh_time); //every 2 secs check the user list if for notification purposes

        // ── WebSocket ─────────────────────────────────────────────
        let wsReady = false;

        ws.onopen = () => {
            wsReady = true;
            ws.send(JSON.stringify({
                type:   'chat_list',
                userId: '<?=$userId?>',
                testParam: 'test'
            }));
        };

        ws.onmessage = (event) => {
            const msg = JSON.parse(event.data);
            if (msg.type === 'new_message') {
//                appendMessage(msg.data);   // ← we need to create this
                console.log('appendMessage: ');
                console.log(msg.data);
                load_chat_log();
            }

            if (msg.type === 'unread_badge') {
                load_user_list();
            }
        };

        ws.onclose = () => {
            wsReady = false;
            console.log('WS disconnected, reconnecting...');
            setTimeout(() => location.reload(), 3000);
        };

    })


    function switchRoom(chatId) { //on hold for now
        ws.send(JSON.stringify({ type: 'switch_chat', chatId: chatId}));
    }
</script>
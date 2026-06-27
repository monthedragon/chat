<script>
    const BASE_URL = "<?= base_url(); ?>";
</script>

<link rel="stylesheet" href="<?= base_url() ?>assets/css/chat.css">
<span style='display:none' id='interval_holder'>s</span>
<div id='chat-list'>
    <div id='user-list' style='height:100%;display:block'></div>
</div>
<div id="chat-panel">
    <div id='div-chat-log' style='height:100%;display:flex;flex-direction:column;'></div>
    <div id='prev-user-list-len' style='display:none'></div>
</div>
<script>
    let wsReady = false;
    let ws = null;
    let reconnectAttempt = 0;

    function connectWS() {
        ws = new WebSocket('ws://localhost:8080');

        ws.onopen = () => {
            wsReady = true;
            reconnectAttempt = 0; // reset backoff on success
            hideWsStatusBanner();

            ws.send(JSON.stringify({
                type:   'chat_list',
                userId: '<?=$userId?>'
            }));
        };

        ws.onmessage = (event) => {
            const msg = JSON.parse(event.data);
            if (msg.type === 'new_message') {
                load_chat_log();
            }
            if (msg.type === 'unread_badge') {
                load_user_list();
            }
        };

        ws.onerror = () => {
            wsReady = false;
            //console.log('WS unavailable - chat will work without real-time push.');
            showWsStatusBanner();
        };

        ws.onclose = () => {
            wsReady = false;
            showWsStatusBanner();
            scheduleReconnect();
        };
    }

    function scheduleReconnect() {
        reconnectAttempt++;
        // exponential backoff, capped at 30s - avoids hammering the server
        var delay = Math.min(3000 * reconnectAttempt, 30000);
        //console.log('Attempting WS reconnect in ' + (delay/1000) + 's...');
        setTimeout(connectWS, delay);
    }

    function wsSend(payload) {
        if (wsReady && ws && ws.readyState === WebSocket.OPEN) {
            ws.send(JSON.stringify(payload));
        }
    }

    function showWsStatusBanner() {
        if ($('#ws-status-banner').length) return;
        $('<div id="ws-status-banner">Live updates unavailable - reconnecting...</div>')
            .prependTo('#chat-list');
    }

    function hideWsStatusBanner() {
        $('#ws-status-banner').remove();
    }

    function load_user_list() {
        const scrollTopUser = $('#body-users').scrollTop();
        const scrollTopGC    = $('#body-gc').scrollTop();

        const usersCollapsed = $('#section-users').hasClass('collapsed');
        const gcCollapsed     = $('#section-gc').hasClass('collapsed');

        $.ajax({
            url: '<?= base_url() ?>chat/user_list',
            success: function(data) {
                $('#user-list').html(data);

                // ── disable transitions on sections AND headers/arrows ──────
                $('#section-users, #section-gc, .sidebar-section-header, .sidebar-toggle')
                    .addClass('no-transition');

                $('#body-users').scrollTop(scrollTopUser);
                $('#body-gc').scrollTop(scrollTopGC);

                if (usersCollapsed) {
                    $('#section-users').addClass('collapsed');
                    $('.sidebar-section-header[data-target="body-users"]').addClass('collapsed');
                    $('#body-users').hide();
                }

                if (gcCollapsed) {
                    $('#section-gc').addClass('collapsed');
                    $('.sidebar-section-header[data-target="body-gc"]').addClass('collapsed');
                    $('#body-gc').hide();
                }

                requestAnimationFrame(() => {
                    $('#section-users, #section-gc, .sidebar-section-header, .sidebar-toggle').removeClass('no-transition');
                });

        if (!wsReady) showWsStatusBanner();
    }
    })
    }

    $(function() {
        load_user_list();
        connectWS(); // ← initial connection
    })

    function switchRoom(chatId) { //on hold for now
        wsSend({ type: 'switch_chat', chatId: chatId});
    }

    $(document).on('click', '.gc-participant-list .div-user', function(e) {
        if ($(e.target).is('input[type=checkbox]')) return;
        var $checkbox = $(this).find('input[type=checkbox]');
        $checkbox.prop('checked', !$checkbox.prop('checked'));
    });

    $(document).on('click', '#btn-open-create-gc', function() {
        var url = '<?= base_url() ?>chat/gc_form';
        do_ajax(url, 'POST', '', 'div-chat-log');
    });
</script>
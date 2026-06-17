<?php /* chat_box.php — chat header + message area + compose form, all IDs/classes preserved */ ?>
<span id="cur_chat_id" style="display:none"><?=$chat_id?></span>

<div style='display:flex;align-items:center;gap:10px;padding:10px 4px 10px;border-bottom:1px solid #e8e8e8;margin-bottom:6px;'>
    <?php
    // initials avatar for the chat participant
    $nameParts = explode(' ', trim($participant_name));
    $endKey = (end(array_keys($nameParts)));
    $initials  = strtoupper(
        substr($nameParts[0], 0, 1) .
        substr((isset($nameParts[$endKey ]) ? $nameParts[$endKey ] : ''), 0, 1)
    );
    $av_colors = ['av-green','av-purple','av-coral','av-blue','av-amber','av-teal'];
    $av_class  = $av_colors[abs(crc32($target_user_id)) % count($av_colors)];
    echo "<span class='user-avatar {$av_class}' style='width:36px;height:36px;font-size:13px;'>{$initials}</span>";
    ?>
    <div style='flex:1;'>
        <h2 style='font-size:15px;font-weight:500;color:#222;margin:0;border:none;padding:0;'>
            <?php echo $participant_name; ?>
        </h2>
        <span style='font-size:12px;color:#888;'>
            <?php echo ($chat_type == 'GROUP') ? 'Group chat' : 'Direct message'; ?>
        </span>
    </div>
    <?php
    if ($user_type == ADMIN_CODE || $user_type == SUPPORT_CODE) {
        $url = base_url() . 'chat/export_chat_log/' . $chat_id . '/' . $target_user_id;
        echo "<input type='button' class='button export-btn' value='Export logs' onclick=\"window.location='{$url}'\">";
    }
    ?>
</div>

<div id='msg-chat-log' style='flex:1;overflow:auto;padding:8px 4px;border-bottom:1px solid #e8e8e8;'></div>

<div id='prev-chat-log-len' style='display:none'></div>

<?
if(($user_type == ADMIN_CODE || $chat_type == 'SOLO') ||
    ($chat_type == 'GROUP' && $user_type != ADMIN_CODE && !$gc_info['view_only']))
    {
?>
    <!--    Conditional only for non-admin during GROUP chat -->
    <div style=''>
        <form id='frm-chat' name='frm-chat' method='post'>
            <textarea
                name='message'
                id='txt-message'
                rows='4'
                style='width:100%;'
                placeholder='Type a message... (Ctrl+Enter to send)'
                ></textarea>
            <div style='display:flex;align-items:center;gap:8px;margin-top:6px;'>
                <?php
                if ($user_type == ADMIN_CODE || $user_type == SUPPORT_CODE) {
                    echo "<input type='file' id='userfile' name='userfile' size='20' style='font-size:12px;color:#666;flex:1;' />";
                }
                ?>
                <input type='submit' class='button send-btn' value='Send' style='margin-left:auto;'>
            </div>
        </form>
    </div>
<?}?>
<script>
    let chat_id = '<?= $chat_id ?>';
    let url_chat_thread = '<?= base_url() ?>chat/chat_logs/' + chat_id;
    let chat_log_per_row = <?= CHAT_LOG_PER_ROW ?>;

    function load_chat_log(param_chat_log_per_row = '') {

        //webSocket
        ws.send(JSON.stringify({
            type:   'chat_box',
            userId: '<?=$userId?>',
            chatId: chat_id
        }));

        let do_scroll_down = true;
        if (param_chat_log_per_row == '') {
            if (chat_log_per_row != <?= CHAT_LOG_PER_ROW ?>) {
                param_chat_log_per_row = chat_log_per_row;
            }
        } else {
            do_scroll_down = false;
        }

//        console.log('Loading from:'  + url_chat_thread);
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

        //reset user list to read unread message
        load_user_list();
    }

    $(function() {
        $('#txt-message').focus();

        //initialize chat log
        load_chat_log();

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

    })
</script>
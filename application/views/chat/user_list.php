<?php /* user_list.php — sidebar user + group list, enhanced markup, all IDs/classes preserved */ ?>

<?php
echo "<form id='frm-user-list'>";

if ($user_type == ADMIN_CODE) {
    echo "<input type='input' class='required' name='gc_name' placeholder='Group chat name...'>";
    echo "<input type='submit' value='Create Group chat' id='btn-crate-gc' class='button'><br>";
    echo '<hr>';
    echo "<input type='input' id='user_chat_search' name='user_chat_search' placeholder='Search name here'>";
}

echo "<div style='height:350px;max-height:350px;overflow-x:hidden;overflow-y:auto;'>";

foreach ($users as $details) {

    $unread_cls  = '';
    if (isset($unread_user_chat[$details['user_name']])) {
        $unread_cls = 'div-unread';
    }

    $fullName    = "{$details['firstname']} {$details['lastname']}";
    $fullNameCls = strtolower(str_replace(' ', '', $fullName));

    // initials avatar (first letter of first + last name)
    $initials = strtoupper(
        substr($details['firstname'], 0, 1) .
        substr($details['lastname'],  0, 1)
    );

    // cycle through avatar colors based on user_name hash
    $av_colors   = ['av-green','av-purple','av-coral','av-blue','av-amber','av-teal'];
    $av_class    = $av_colors[abs(crc32($details['user_name'])) % count($av_colors)];

    $checkbox = ($user_type == ADMIN_CODE)
        ? "<input type='checkbox' value='{$details['user_name']}' class='cursor-pointer' name='participants[]'>"
        : '';

    echo "<div class='div-user {$fullNameCls}' user_id='{$details['user_name']}' data-name='" . strtolower($fullName) . "'>";
    echo $checkbox;
    echo "<span class='user-avatar {$av_class}'>{$initials}</span>";
    echo "<span class='div-user-chat cursor-pointer {$unread_cls}' >{$fullName}</span>";
    echo "</div>";
}

echo '</div>';
echo '</form>';

if (isset($gc_list)) {
    echo '<hr>';
    echo "<div class='gc-section-label'>Group chats</div>";

    foreach ($gc_list as $details) {
        $unread_cls = '';
        if (isset($unread_group_chat[$details['chat_id']])) {
            $unread_cls = 'div-unread';
        }

        echo "<div style='overflow:hidden;'>";
        echo "<div class='div-gc-chat cursor-pointer {$unread_cls}' chat_id='{$details['chat_id']}'>";
        echo "<span class='gc-icon'>&#128172;</span>";  // speech bubble icon (emoji fallback; replace with SVG if preferred)
        echo "{$details['chat_name']}";
        echo "</div>";

        if ($user_type == ADMIN_CODE) {
            echo "<div class='e-link cursor-pointer' chat_id='{$details['chat_id']}'>edit</div>";
        }

        echo "</div>";
    }
}
?>

<script>
    let cur_chat_id = 0;
    var default_title = '<?= PROJECT_NAME ?>';
    var position = 0;

    //Make the title scrolling to notice easily the new messages
    function scrolltitle() {
        var unreadCtr = $('.div-unread').length;
        if (unreadCtr > 0) {
            if (unreadCtr == 1) {
                msg = 'You have a message from ' + $('.div-unread').html() + ' . . . ';
            } else {
                msg = 'You have ' + unreadCtr + ' messages . . .';
            }

            document.title = msg.substring(position, msg.length) + msg.substring(0, position);
            position++;
            if (position > msg.length) position = 0
            window.setTimeout("scrolltitle()", 200);
        }
    }


    $(function() {

        $('.e-link').mouseover(function() {
            $(this).css('font-weight', 'bold')
        });
        $('.e-link').mouseout(function() {
            $(this).css('font-weight', 'normal')
        });

        $('#frm-user-list').submit(function(event) {

            event.preventDefault();
            if ($(this).valid()) {
                if(!confirm('Create group chat "' + $("input[name=gc_name]").val() + '"?')) return false;
                $.ajax({
                    url: '<?= base_url() ?>chat/createGC',
                    data: $(this).serialize(),
                    type: 'POST',
                    // beforeSend:function(){$("#btnSubmit").val('please wait...').prop('disabled',true)},
                    success: function(data) {

                        // console.log(data);
                        if (data != '') {
                            alert(data);
                        } else {
                            alert('Saved');
                            location.reload(true);
                        }

                    }
                });
            }

        })

        if ($('.div-unread').length > 0) {
            scrolltitle();
        } else {
            document.title = default_title;
        }

        $('.div-user').unbind('click');
        $('.div-user').click(function() {
            var user_id = $(this).attr('user_id');
            var url = '<?= base_url() ?>chat/chat_box/' + user_id;
            var target_interval = $('#interval_holder').html(); //get the targeted intervalId to be cleared once new chat intialized

            do_ajax(url, 'POST', '', 'div-chat-log');

            if($('#cur_chat_id').length > 0){
                switchRoom($('#cur_chat_id').html());
            }

//			clearInterval(target_interval); //clear previously created interval to avoid unexpected request
        })

        //start of GC
        $('.div-gc-chat').unbind('click');
        $('.div-gc-chat').click(function() {
            var chat_id = $(this).attr('chat_id');
            var url = '<?= base_url() ?>chat/gc_chat_box/' + chat_id;
            var target_interval = $('#interval_holder').html(); //get the targeted intervalId to be cleared once new chat intialized

            do_ajax(url, 'POST', '', 'div-chat-log');
            switchRoom($('#cur_chat_id').html());
            //switchRoom(chat_id);

//			clearInterval(target_interval); //clear previously created interval to avoid unexpected request
        })

        //start of edit GC

        $('.e-link').unbind('click');
        $('.e-link').click(function() {
            var chat_id = $(this).attr('chat_id');
            var url = '<?= base_url() ?>chat/edit_gc/' + chat_id;

            do_ajax(url, 'POST', '', 'div-chat-log');
        })

        $('#user_chat_search').on('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                return false;
            }
        });

        // Listen for input changes in the wildcard input field
        $('#user_chat_search').on('input', function() {
            var keyword = $(this).val().toLowerCase().trim();

            if (keyword === '') {
                $('.div-user').show();
                return;
            }

            $('.div-user').each(function() {
                var name = $(this).data('name') || '';
                if (name.indexOf(keyword) !== -1) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });

    })
</script>
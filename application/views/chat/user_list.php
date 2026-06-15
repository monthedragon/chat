<?php /* user_list.php */ ?>

<?php
echo "<form id='frm-user-list'>";

if ($user_type == ADMIN_CODE) {
    echo "<input type='input' class='required' name='gc_name' placeholder='Group chat name...'>";
    echo "<input type='submit' value='Create Group chat' id='btn-crate-gc' class='button'><br>";
//    echo '<hr>';
}

//Search is now available to all
echo "<input type='input' id='user_chat_search' name='user_chat_search' placeholder='Search name here' autocomplete='off'>";
echo "</form>";

// ── Users section ─────────────────────────────────────────────────
echo "<div class='sidebar-section' id='section-users'>";
echo "  <div class='sidebar-section-header' data-target='body-users'>";
echo "      <span>Users</span>";
echo "      <span class='sidebar-toggle'>&#9660;</span>";
echo "  </div>";
echo "  <div class='sidebar-section-body' id='body-users'>";

foreach ($users as $details) {
    $unread_cls  = '';
    if (isset($unread_user_chat[$details['user_name']])) {
        $unread_cls = 'div-unread';
    }

    $fullName    = "{$details['firstname']} {$details['lastname']}";
    $fullNameCls = strtolower(str_replace(' ', '', $fullName));

    $initials = strtoupper(
        substr($details['firstname'], 0, 1) .
        substr($details['lastname'],  0, 1)
    );

    $av_colors = ['av-green','av-purple','av-coral','av-blue','av-amber','av-teal'];
    $av_class  = $av_colors[abs(crc32($details['user_name'])) % count($av_colors)];

    $checkbox = ($user_type == ADMIN_CODE)
        ? "<input type='checkbox' value='{$details['user_name']}' class='cursor-pointer' name='participants[]'>"
        : '';

    echo "<div class='div-user {$fullNameCls}' user_id='{$details['user_name']}' data-name='" . strtolower($fullName) . "'>";
    echo $checkbox;
    echo "<span class='user-avatar {$av_class}'>{$initials}</span>";
    echo "<span class='div-user-chat cursor-pointer {$unread_cls}'>{$fullName}</span>";
    echo "</div>";
}

echo "  </div>"; // end body-users
echo "</div>";   // end section-users

// ── Group chats section ───────────────────────────────────────────
if (isset($gc_list) && $gc_list) {
    echo "<div class='sidebar-section' id='section-gc'>";
    echo "  <div class='sidebar-section-header' data-target='body-gc'>";
    echo "      <span>Group Chats</span>";
    echo "      <span class='sidebar-toggle'>&#9660;</span>";
    echo "  </div>";
    echo "  <div class='sidebar-section-body' id='body-gc'>";

    foreach ($gc_list as $details) {
        $unread_cls = '';
        if (isset($unread_group_chat[$details['chat_id']])) {
            $unread_cls = 'div-unread';
        }

        echo "<div style='overflow:hidden;'>";
        echo "<div class='div-gc-chat cursor-pointer {$unread_cls}' chat_id='{$details['chat_id']}'>";
        echo "<span class='gc-icon'>&#128172;</span>";
        echo "{$details['chat_name']}";
        echo "</div>";

        if ($user_type == ADMIN_CODE) {
            echo "<div class='e-link cursor-pointer' chat_id='{$details['chat_id']}'>edit</div>";
        }

        echo "</div>";
    }

    echo "  </div>"; // end body-gc
    echo "</div>";   // end section-gc
}
?>

<script>
    let cur_chat_id = 0;
    var default_title = '<?= PROJECT_NAME ?>';
    var position = 0;

    function scrolltitle() {
        var unreadCtr = $('.div-unread').length;
        if (unreadCtr > 0) {
            if (unreadCtr == 1) {

                var text = $('.div-unread').clone()
                    .find('.gc-icon')
                    .remove()
                    .end()
                    .html()
                    .trim();

                msg = 'You have a message from ' + text + ' . . . ';
            } else {
                msg = 'You have ' + unreadCtr + ' messages . . .';
            }
            document.title = msg.substring(position, msg.length) + msg.substring(0, position);
            position++;
            if (position > msg.length) position = 0;
            window.setTimeout("scrolltitle()", 200);
        }
    }

    $(function() {

        // ── Collapsible sections ──────────────────────────────────

        $('.sidebar-section-header').click(function() {
            var targetId = $(this).data('target');
            var $body    = $('#' + targetId);
            var $header  = $(this);
            var $section = $header.closest('.sidebar-section'); // ← get parent section

            $body.slideToggle(200, function() {
                if ($body.is(':visible')) {
                    $header.removeClass('collapsed');
                    $section.removeClass('collapsed'); // ← expand section
                } else {
                    $header.addClass('collapsed');
                    $section.addClass('collapsed');    // ← shrink section
                }
            });
        });

        // ── Existing handlers (unchanged) ─────────────────────────
        $('.e-link').mouseover(function() { $(this).css('font-weight', 'bold'); });
        $('.e-link').mouseout(function()  { $(this).css('font-weight', 'normal'); });

        $('#frm-user-list').submit(function(event) {
            event.preventDefault();
            if ($(this).valid()) {
                if (!confirm('Create group chat "' + $("input[name=gc_name]").val() + '"?')) return false;
                $.ajax({
                    url: '<?= base_url() ?>chat/createGC',
                    data: $(this).serialize(),
                    type: 'POST',
                    success: function(data) {
                        if (data != '') {
                            alert(data);
                        } else {
                            alert('Saved');
                            location.reload(true);
                        }
                    }
                });
            }
        });

        if ($('.div-unread').length > 0) {
            scrolltitle();
        } else {
            document.title = default_title;
        }

        $('.div-user').unbind('click');
        $('.div-user').click(function() {
            var user_id = $(this).attr('user_id');
            var url     = '<?= base_url() ?>chat/chat_box/' + user_id;
            do_ajax(url, 'POST', '', 'div-chat-log');
            if ($('#cur_chat_id').length > 0) {
                switchRoom($('#cur_chat_id').html());
            }
        });

        $('.div-gc-chat').unbind('click');
        $('.div-gc-chat').click(function() {
            var chat_id = $(this).attr('chat_id');
            var url     = '<?= base_url() ?>chat/gc_chat_box/' + chat_id;
            do_ajax(url, 'POST', '', 'div-chat-log');
            switchRoom($('#cur_chat_id').html());
        });

        $('.e-link').unbind('click');
        $('.e-link').click(function() {
            var chat_id = $(this).attr('chat_id');
            var url     = '<?= base_url() ?>chat/edit_gc/' + chat_id;
            do_ajax(url, 'POST', '', 'div-chat-log');
        });

        $('#user_chat_search').on('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                return false;
            }
        });

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

    });
</script>
<?
if($do_export){
    header('Content-Type: application/vnd.ms-excel'); //mime type
    header('Content-Disposition: attachment;filename="'.$participant_name.'.xls"'); //tell browser what's the file name
    header('Cache-Control: max-age=0');
}

$img_ext_array  = array('jpg','png','gif');

//source of svg: https://www.flaticon.com/packs/file-types
//svg file should have equivalent on this folder: /uploads/icon
$svg_arrays = array('xlsx','xls','pdf','doc','mp4');

// $reactions expected from controller:
// getReactionsForChat($chat_id, $current_user)
// [ chat_log_id => ['count' => N, 'names' => [...], 'reacted_by_me' => bool] ]
if (!isset($reactions)) $reactions = array();
?>
<?php /* chat_log.php */ ?>

<table id='tbl-chat' width='100%'>
    <tr>
        <td style='width:10%'></td>
        <td style='width:77%'></td>
        <td style='width:13%'></td>
    </tr>
    <?php
    $old_msg_user = '';

    function av_class($username) {
        $colors = ['av-green','av-purple','av-coral','av-blue','av-amber','av-teal'];
        return $colors[abs(crc32($username)) % count($colors)];
    }

    function av_initials($firstname, $lastname) {
        return strtoupper(substr($firstname, 0, 1) . substr($lastname, 0, 1));
    }

    foreach ($logs as $details) {

        $user_changed = ($old_msg_user != $details['created_by']);
        if ($user_changed) {
            $old_msg_user = $details['created_by'];
            $chat_by      = $details['firstname'] . ' ' . $details['lastname'];
        } else {
            $chat_by = '';
        }

        $is_owner = ($current_user == $details['created_by']);
        $log_id   = $details['chat_log_id'];

        // ── avatar ────────────────────────────────────────────────────
        $av_color    = av_class($details['created_by']);
        $av_initials = av_initials($details['firstname'], $details['lastname']);
        $avatar_html = $user_changed
            ? "<span class='user-avatar {$av_color}'>{$av_initials}</span>"
            : "<span class='avatar-spacer'></span>";

        // ── sender name ───────────────────────────────────────────────
        $sender_name = ($user_changed && $chat_by)
            ? "<div class='msg-sender-name'>{$chat_by}</div>"
            : '';

        // ── status trigger link ───────────────────────────────────────
        $chat_triger_status = '';
        if (!$do_export) {
            if ((isset($privs[199]) || $user_type == ADMIN_CODE) && !$is_owner) {
                $target_status      = ($details['chat_status'] == 'new') ? 'completed' : 'new';
                $chat_triger_status = "<span status='{$target_status}' class='spn_chat_status cursor-pointer' chat_log_id='{$log_id}'>set as {$target_status}</span> &nbsp;|&nbsp; ";
            }
        }

        // ── completed badge ───────────────────────────────────────────
        $chat_log_status = '';
        if ($details['chat_status'] == 'completed') {
            $status_color    = $is_owner ? 'yellow' : 'red';
            $chat_log_status = "<div class='chat_log_completed status_{$status_color}'>{$details['chat_status']}</div>";
        }

        // ── message content ───────────────────────────────────────────
        if ($details['is_file']) {
            $file_name   = $details['file_name'];
            $source_link = base_url() . "uploads/chat_attachment/" . $file_name;
            $file_ext    = strtolower(str_replace('.', '', $details['file_ext']));

            if (in_array($file_ext, $img_ext_array)) {
                $file_view_path = base_url() . "uploads/chat_attachment/" . $details['file_name'];
            } elseif (in_array($file_ext, $svg_arrays)) {
                $file_view_path = base_url() . "uploads/icon/{$file_ext}.svg";
            } else {
                $file_view_path = base_url() . "uploads/icon/default.svg";
            }

            $chat_message  = "<a href='{$source_link}' target='_blank'><img src='{$file_view_path}' width='50px'></a>";
            $chat_message .= "<br><i style='font-size:11px;opacity:0.8;'>{$file_name}</i>";
        } else {
            $chat_message = $chat_log_status . nl2br($details['message']);
        }

        // ── meta line (below bubble) ──────────────────────────────────
        $meta = $chat_triger_status . $details['date_entered'];

        // ── reaction data for this message ──────────────────────────
        $r_count  = isset($reactions[$log_id]) ? $reactions[$log_id]['count'] : 0;
        $r_names  = isset($reactions[$log_id]) ? $reactions[$log_id]['names'] : array();
        $r_mine   = isset($reactions[$log_id]) ? $reactions[$log_id]['reacted_by_me'] : false;
        $r_active = $r_mine ? 'active' : '';

        $names_html = '';
        foreach ($r_names as $name) {
            $names_html .= "<div class='reaction-name-row'>" . htmlspecialchars($name) . "</div>";
        }

        // hover-trigger button — only visible on bubble hover, sits at bubble's bottom corner
        $reaction_trigger = "<span class='msg-reaction-trigger {$r_active}' chat_log_id='{$log_id}'>&#128077;</span>";

        // count badge — only rendered if count > 0, always visible (not hover-dependent)
        $reaction_badge = '';
        if ($r_count > 0) {
            $badge_active = $r_mine ? 'reaction-badge-active' : '';
            $reaction_badge = "<div class='msg-reaction-badge {$badge_active}' chat_log_id='{$log_id}'>";
            $reaction_badge .=   "&#128077; <span class='reaction-badge-count'>{$r_count}</span>";
            $reaction_badge .=   "<div class='reaction-names-popup'>{$names_html}</div>";
            $reaction_badge .= "</div>";
        }

        // ── row ───────────────────────────────────────────────────────
        // Key: wrap sender name + bubble + meta in a .msg-wrap div
        // For own: .msg-wrap has text-align:right so bubble+meta align right
        // For other: .msg-wrap has text-align:left

        $log_html = "<tr chat_logid='{$log_id}'>";

        if ($is_owner) {

            $log_html .= "<td></td>";
            $log_html .= "<td valign='top' style='padding-bottom:14px;'>";
            $log_html .= "<div class='msg-wrap msg-wrap-own'>";
            $log_html .=   "{$sender_name}";
            $log_html .=   "<div class='bubble-container'>";
            $log_html .=     "<div class='own_msg'>{$chat_message}</div>";
            $log_html .=     "{$reaction_trigger}";
            $log_html .=     "{$reaction_badge}";
            $log_html .=   "</div>";
            $log_html .=   "<div class='own_msg_datetime'>{$meta}</div>";
            $log_html .= "</div>";
            $log_html .= "</td>";
            $log_html .= "<td valign='top' style='padding-top:4px;padding-left:6px;'>{$avatar_html}</td>";

        } else {

            $log_html .= "<td valign='top' style='padding-top:4px;text-align:right;padding-right:6px;'>{$avatar_html}</td>";
            $log_html .= "<td valign='top' style='padding-bottom:14px;'>";
            $log_html .= "<div class='msg-wrap msg-wrap-other'>";
            $log_html .=   "{$sender_name}";
            $log_html .=   "<div class='bubble-container'>";
            $log_html .=     "<div class='other_msg'>{$chat_message}</div>";
            $log_html .=     "{$reaction_trigger}";
            $log_html .=     "{$reaction_badge}";
            $log_html .=   "</div>";
            $log_html .=   "<div class='other_msg_datetime'>{$meta}</div>";
            $log_html .= "</div>";
            $log_html .= "</td>";
            $log_html .= "<td></td>";

        }

        $log_html .= "</tr>";
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

        // ── reaction toggle ────────────────────────────────────────
        $(document).off('click', '.msg-reaction-trigger, .msg-reaction-badge').on('click', '.msg-reaction-trigger, .msg-reaction-badge', function(e){
            e.stopPropagation();
            var chat_log_id = $(this).attr('chat_log_id');

            $.ajax({
                url: '<?=base_url()?>chat/toggle_reaction/' + chat_log_id,
                type: 'POST',
                success: function(data){
                    $.ajax({
                        url: url_chat_thread,
                        success: function(data){
                            console.log(data);
                            $('#msg-chat-log').html(data);
                        }
                    });
                }
            });
        });
    })
</script>
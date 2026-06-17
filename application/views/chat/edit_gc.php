<?php
echo "<form id='frm-gc-update'>";

// ── GC name + view only + submit ─────────────────────────────────
echo "<div class='gc-form-header'>";

echo "<input type='input' class='required gc-name-input' name='gc_name' value='{$gc_info['chat_name']}' placeholder='Group chat name...'>";

$checked = $gc_info['view_only'] ? 'checked' : '';
echo "<label class='gc-viewonly-label'>
        <input type='checkbox' name='view_only' {$checked}>
        View only (Agent)
      </label>";

echo "<input type='submit' value='Update Group chat' id='btn-update-gc' class='button gc-update-btn'>";
echo "<input type='button' value='Delete' id='btn-delete-gc' class='button gc-delete-btn'>";

echo "</div>";

echo "<hr>";

// ── Participant list ──────────────────────────────────────────────
echo "<div class='gc-participant-list'>";

foreach ($users as $details) {
    $checked  = isset($participants[$details['user_name']]) ? 'checked' : '';
    $fullName = "{$details['firstname']} {$details['lastname']}";

    $initials  = strtoupper(substr($details['firstname'], 0, 1) . substr($details['lastname'], 0, 1));
    $av_colors = ['av-green','av-purple','av-coral','av-blue','av-amber','av-teal'];
    $av_class  = $av_colors[abs(crc32($details['user_name'])) % count($av_colors)];

    echo "<div class='div-user'>";
    echo "<input type='checkbox' {$checked} value='{$details['user_name']}' class='cursor-pointer' name='participants[]'>";

    echo "<span class='div-user-chat'>{$fullName}</span>";
    echo "</div>";
}

echo "</div>";
echo "</form>";
?>

<script>

    $(function(){
        $('#frm-gc-update').submit(function(event) {
            event.preventDefault();
            if ($(this).valid()) {
                if (!confirm('Update group chat "' + $(this).find("input[name=gc_name]").val() + '"?')) return false;
                $.ajax({
                    url:  '<?= base_url() ?>chat/updateGC/<?= $chat_id ?>',
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

        $('#btn-delete-gc').on('click', function() {
            if (!confirm('Delete this group chat? This cannot be undone.')) return false;
            // your delete logic here
        });
    })


</script>
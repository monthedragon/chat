<?php
/**
 * gc_form.php — handles BOTH Create GC and Edit GC
 *
 * Expected variables passed from controller:
 *   $is_edit       (bool)   true = edit mode, false = create mode
 *   $users         (array)  full user list to render as checkboxes
 *   $gc_info       (array)  ['chat_name' => ..., 'view_only' => ...] — only when $is_edit
 *   $chat_id       (int)    only when $is_edit
 *   $participants  (array)  keyed by user_name, only when $is_edit
 */

$is_edit = isset($is_edit) && $is_edit;

$gc_name_val   = $is_edit ? $gc_info['chat_name'] : '';
$view_only_chk = ($is_edit && $gc_info['view_only']) ? 'checked' : '';
$form_id       = $is_edit ? 'frm-gc-update' : 'frm-gc-create';
$submit_label  = $is_edit ? 'Update Group chat' : 'Create Group chat';
$submit_id     = $is_edit ? 'btn-update-gc' : 'btn-crate-gc';
$ajax_url      = $is_edit
    ? base_url() . 'chat/updateGC/' . $chat_id
    : base_url() . 'chat/saveGC';
?>

<form id='<?= $form_id ?>'>

    <div class='gc-form-header'>
        <input type='input' class='required gc-name-input' name='gc_name' value='<?= $gc_name_val ?>' placeholder='Group chat name...'>

        <label class='gc-viewonly-label'>
            <input type='checkbox' name='view_only' <?= $view_only_chk ?>>
            View only (Agent)
        </label>

        <input type='submit' value='<?= $submit_label ?>' id='<?= $submit_id ?>' class='button gc-update-btn'>

        <?php if ($is_edit) { ?>
            <input type='button' value='Delete' id='btn-delete-gc' class='button gc-delete-btn'>
        <?php } ?>
    </div>

    <hr>

    <div class='gc-participant-list'>
        <?php foreach ($users as $details) {

            $checked = ($is_edit && isset($participants[$details['user_name']])) ? 'checked' : '';
            $fullName = "{$details['firstname']} {$details['lastname']}";

            $initials  = strtoupper(substr($details['firstname'], 0, 1) . substr($details['lastname'], 0, 1));
            $av_colors = ['av-green','av-purple','av-coral','av-blue','av-amber','av-teal'];
            $av_class  = $av_colors[abs(crc32($details['user_name'])) % count($av_colors)];
            ?>
            <div class='div-user'>
                <span class='checkbox-zone'>
                    <input type='checkbox' <?= $checked ?> value='<?= $details['user_name'] ?>' class='cursor-pointer' name='participants[]'>
                </span>
                <span class='user-avatar <?= $av_class ?>'><?= $initials ?></span>
                <span class='div-user-chat'><?= $fullName ?></span>
            </div>
        <?php } ?>
    </div>

</form>

<script>
    $('#<?= $form_id ?>').submit(function(event) {
        event.preventDefault();
        if ($(this).valid()) {
            var actionLabel = '<?= $is_edit ? "Update" : "Create" ?>';
            if (!confirm(actionLabel + ' group chat "' + $(this).find("input[name=gc_name]").val() + '"?')) return false;

            $.ajax({
                url:  '<?= $ajax_url ?>',
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

    <?php if ($is_edit) { ?>
    $('#btn-delete-gc').on('click', function() {
        if (!confirm('Delete this group chat? This cannot be undone.')) return false;
        // delete logic to be applied here
    });
    <?php } ?>
</script>
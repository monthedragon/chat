<?php
echo "<form id = 'frm-gc-update'>";	

echo "<input type='input' class=required name='gc_name' style='width:40%;margin-bottom:10px;' value='{$gc_info['chat_name']}'> ";
echo "<input type=submit value='Update Group chat' id='btn-update-gc' style='width:20%' class='button'><br>";
echo '<hr>';
	
	echo "<div style='max-height:500px;overflow-x:hidden;overflow-y:auto;'>";
	foreach($users as $details){
		
		$checked = isset($participants[$details['user_name']]) ? 'checked' : '';
		
		$checkbox =  "<input type=checkbox {$checked} value='{$details['user_name']}' class='cursor-pointer' name='participants[]'>" ;
		echo "{$checkbox}<span style='display:inline-block;height:15px;width:80%;' class='div-user-chat cursor-pointer ' user_id='{$details['user_name']}' >  {$details['firstname']}  {$details['lastname']}</span>";
		echo '<br>';
	}
	echo '</div>';
echo '</form>';
?>

<script>
$('#frm-gc-update').submit(function(event){
			
	event.preventDefault();
	if($(this).valid()){
		$.ajax({
			url:'<?=base_url()?>chat/updateGC/<?=$chat_id?>',
			data:$(this).serialize(),
			type:'POST',
			// beforeSend:function(){$("#btnSubmit").val('please wait...').prop('disabled',true)},
			success:function(data){
				
				// console.log(data);
				if(data != ''){
					alert(data);
				}else{
					alert('Saved');
					location.reload(true);
				}
			
			}
		});
	}
	
})
</script>

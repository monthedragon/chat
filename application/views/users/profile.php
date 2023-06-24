
<?php
	//SUCCESS upload
	if(isset($success)){
?>
		<h3><span style='color:red'>Profile Picture was successfully uploaded!</font></h3> 
		<p>
			<a href='<?=base_url()?>users/'>back to user list</a>
		</p>
<?php
	}
	
	//ERROR upload
	if(isset($error) && !empty($error)){
		echo 'Error: '.$error;
	}
?>

<form enctype="multipart/form-data" accept-charset="utf-8" method="post" action="<?=base_url()?>users/do_upload_profile/<?=$agent_id?>">
<?php echo "<input type='file' name='userfile' size='20' />"; ?>
<?php echo "<input type='submit' name='submit' value='upload' /> ";?>
<?php echo "</form>"?>

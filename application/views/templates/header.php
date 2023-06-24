<!DOCTYPE html>
<html>
<head>
	<title><?php echo $title ?> </title>
	<link rel='stylesheet'   type="text/css"  href='<?=base_url()?>assets/css/default.css'> 
	<link rel='stylesheet'   type="text/css"  href='<?=base_url()?>assets/css/jquery-ui.css'> 
	<link rel='stylesheet'   type="text/css"  href='<?=base_url()?>assets/css/basic.css'> 
	<link rel='stylesheet'   type="text/css"  href='<?=base_url()?>assets/css/dropdown.css'> 
	<link rel='stylesheet'   type="text/css"  href='<?=base_url()?>assets/css/dropdown.linear.css'> 
	<link rel='stylesheet'   type="text/css"  href='<?=base_url()?>assets/css/default.ultimate.css'> 
	<link rel='stylesheet'   type="text/css"  href='<?=base_url()?>assets/css/simplePagination.css'> 
	
	
	
	<script type="text/javascript" src="<?=base_url()?>assets/js/jquery.1.9.1.js"></script>
	<script type="text/javascript" src="<?=base_url()?>assets/js/jquery-ui.js"></script>
	<script type="text/javascript" src="<?=base_url()?>assets/js/jquery-validate.js"></script>
	<script type="text/javascript" src="<?=base_url()?>assets/js/jquery.inline.confirm.js"></script>
	<script type="text/javascript" src="<?=base_url()?>assets/js/jquery.simplemodal-1.4.4.js"></script>
	<script type="text/javascript" src="<?=base_url()?>assets/js/jquery.maskedinput-1.3.js"></script>
	<script type="text/javascript" src="<?=base_url()?>assets/js/jquery.simplePagination.js"></script>
	<script type="text/javascript" src="<?=base_url()?>assets/js/jquery.alphanumeric.pack.js"></script>
    <script type="text/javascript" src="<?=base_url()?>assets/js/tinymce/tinymce.min.js"></script>
    <script type="text/javascript" src="<?=base_url()?>assets/js/utils.js"></script>
	
	
</head>

<body>

<table width="100%" height="100%" align="center" bgcolor="#FFFFFF">
<tr>
<td  align="center" valign='top'>
 
<div class="content">

    <div class='div-top' style="text-align:left;">

		<?if(isset($is_logged)){?>
            <div class='div-logo'  >
                <?=img('assets/images/logo.PNG',FALSE,150)?>

            </div>
			<div class='div-top-userinfo'><?=strtoupper("$user_name ($user_type)")?></div>
		<?}?>
	
		<div class="toplink" style='clear:both'>
			<?if(isset($sub_title)){?>
				<span id='span-sub-title'><?=$sub_title?></span>
			<?}?>
			<span id='span-nav'>

				<ul id="nav" class="dropdown">
					<!--li><a href="<?=base_url()?>/security/cp/" class='a_link'><img src="./assets/images/check.bmp" alt="Change Password"></a>
						<ul>
							<li><span id='spn-time cursor-pointer'>TIME IN</span></li>
							<li><span id='spn-time cursor-pointer'>TIME OUT</span></li>
						</ul>
					</li-->
					<?php if(isset($is_logged)):?>
						<li><a   style='color:red;' href="<?php echo  base_url() . 'main/';?>"> HOME </a></li>
						 
						<li>
							<a > Users</a>
							<ul>
								<?if(isset($privs[180])){?>
									<li><span class='cursor-pointer '><a href="<?php echo  base_url() . 'users/';?>"> user list</a>  </span></li>
									<li><span class='cursor-pointer '><a href='<?=base_url()?>users/add' id='a-user-add' class='a-modal'>add user</a></span></li>
								<?}?>
								<li><a href="<?php echo  base_url() . 'security/cp';?>"  class='a-modal'>change password</a>  </li>
							</ul>

						</li> 
						<li><a href="<?php echo  base_url() . 'security/logout';?>">Logout</a> </li>
					<?php endif;?>

				</ul>

			</span>
		</div>
	</div> 
<div id='div-add-user' class='hidden'></div>
<div id='div-messages'></div>
<div id='div-edit-contact'></div>

	<script>
		$(document).ready(function(){
		
		  
			$(".a-modal").click(function(){
				var url = $(this).prop('href');
				
				$.ajax({
					url:url,
					success:function(data){
						$("#div-add-user").html(data);
						$("#div-add-user").modal({
							containerCss: { height:300,width: 300},
							onOpen:function(dialog){  
									dialog.overlay.fadeIn('fast', function () {
										dialog.container.slideDown('slow', function () {
											dialog.data.fadeIn('slow');
										});
									});
								},
								onClose: function(dialog){
									$("#div-add-user").html(''); 
									dialog.container.slideUp('slow', function () { 
										$.modal.close(); // must call this! 
									}); 
							}
						});
					}
					
				})
				
				return false;
			})
	
			
		})
	</script>
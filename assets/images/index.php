<?php
// access_denied.php — placed inside /uploads/chat_attachment/

$base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://')
    . $_SERVER['HTTP_HOST'] . '/chat/';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>403 Forbidden</title>
    <link rel="stylesheet" href="<?= $base_url ?>assets/css/access_denied.css">
</head>
<body>
<div class="container">
    <div class="code">403</div>
    <div class="title">Access Forbidden</div>
    <div class="message">Directory access is not permitted on this server.</div>
</div>
</body>
</html>
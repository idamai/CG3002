<?php session_start(); ?>
<html>
<head></head>
<body>
<?php
session_destroy();
?>
<script>window.location.href = "login.php"; </script>
</body>
</html>
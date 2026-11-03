<?php
$script = $script ?? '';
$script .= '<script>window.CSRF = ' . json_encode(csrf_token()) . ';</script>';
$script .= vite_script('src/js/app.js');
?>

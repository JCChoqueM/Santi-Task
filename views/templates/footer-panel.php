        </div><!-- /.contenido -->
    </div><!-- /.principal -->
</div><!-- /.dashboard -->
<?php
$script = $script ?? '';
$script .= '<script nonce="' . cspNonce() . '">window.CSRF = ' . json_encode(csrf_token()) . ';</script>';
$script .= vite_script('src/js/app.js');
?>

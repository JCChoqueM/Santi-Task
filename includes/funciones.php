<?php

function debuguear($variable): string
{
    echo "<pre>";
    var_dump($variable);
    echo "</pre>";
    exit;
}

// Escapa / Sanitizar el HTML
function s($html): string
{
    return htmlspecialchars((string) $html);
}

// Función que revisa que el usuario este autenticado
function isAuth(): void
{
    if (!isset($_SESSION['login'])) {
        header('Location: /');
        exit;
    }
}

// Exige un rol. En páginas redirige; en la API responde JSON 403.
function exigirRol(string $rol, bool $api = false): void
{
    if (!isset($_SESSION['login']) || ($_SESSION['rol'] ?? '') !== $rol) {
        if ($api) {
            responderJson(['tipo' => 'error', 'mensaje' => 'Acceso denegado.'], 403);
        }
        header('Location: /');
        exit;
    }
}
function isTutor(bool $api = false): void    { exigirRol('tutor', $api); }
function isTutelado(bool $api = false): void { exigirRol('tutelado', $api); }

function responderJson(array $datos, int $codigo = 200): void
{
    http_response_code($codigo);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($datos);
    exit;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function verificarCsrf(bool $api = false): void
{
    $enviado = $_POST['csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    if (($_SESSION["csrf"] ?? "") === "" || !hash_equals($_SESSION["csrf"], (string) $enviado)) {
        if ($api) {
            responderJson(['tipo' => 'error', 'mensaje' => 'Token CSRF no válido.'], 403);
        }
        http_response_code(403);
        exit('Token CSRF no válido.');
    }
}

// ¿Entorno de desarrollo? Solo si ENVIRONMENT=development. Si falta o es otro valor, se trata como produccion.
function esDesarrollo(): bool
{
    return ($_ENV['ENVIRONMENT'] ?? '') === 'development';
}

// Manifest de Vite (public/build/.vite/manifest.json), leido una vez por peticion; null si no existe
function manifestVite(): ?array
{
    static $manifest = false;
    if ($manifest === false) {
        $ruta = __DIR__ . '/../public/build/.vite/manifest.json';
        $datos = is_file($ruta) ? json_decode((string) file_get_contents($ruta), true) : null;
        $manifest = is_array($datos) ? $datos : null;
    }
    return $manifest;
}

// Valor aleatorio por peticion para los <script> en linea que permite la CSP de produccion
function cspNonce(): string
{
    static $nonce = null;
    if ($nonce === null) {
        $nonce = base64_encode(random_bytes(16));
    }
    return $nonce;
}

// Politica de contenido. En desarrollo, la que necesita el servidor de Vite; en produccion, estricta
// (scripts y estilos solo propios, sin 'unsafe-inline' ni 'unsafe-eval'; el unico script en linea lleva nonce).
function enviarCabecerasSeguridad(): void
{
    if (headers_sent()) {
        return;
    }
    if (esDesarrollo()) {
        $csp = "default-src 'self' http://localhost:5173 ws://localhost:5173; "
            . "script-src 'self' 'unsafe-inline' 'unsafe-eval' http://localhost:5173 ws://localhost:5173 https://cdn.jsdelivr.net; "
            . "style-src 'self' 'unsafe-inline' http://localhost:5173 https://fonts.googleapis.com; "
            . "font-src 'self' data: https://fonts.gstatic.com; "
            . "connect-src 'self' ws://localhost:5173 http://localhost:5173;";
    } else {
        $csp = "default-src 'self'; "
            . "script-src 'self' 'nonce-" . cspNonce() . "'; "
            . "style-src 'self' https://fonts.googleapis.com; "
            . "font-src 'self' data: https://fonts.gstatic.com; "
            . "img-src 'self' data:; "
            . "connect-src 'self'; "
            . "object-src 'none'; base-uri 'self'; form-action 'self'; frame-ancestors 'none';";
    }
    header('Content-Security-Policy: ' . $csp);
}

function vite_script($archivo)
{
    // Si es una URL externa (CDN, etc.), se devuelve directo
    if (preg_match('/^https?:\/\//', $archivo)) {
        return '<script src="' . s($archivo) . '"></script>';
    }

    if (esDesarrollo()) {
        return "<script type=\"module\" src=\"http://localhost:5173/{$archivo}\"></script>";
    }

    $file = manifestVite()[$archivo]['file'] ?? '';
    if ($file === '') {
        // Sin build no hay script: se avisa en el log en vez de romper la pagina
        error_log("Vite: '{$archivo}' no esta en public/build/.vite/manifest.json (falta npm run prod?)");
        return '';
    }

    return '<script type="module" src="/build/' . s($file) . '"></script>';
}

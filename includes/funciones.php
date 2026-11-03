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
    return htmlspecialchars($html);
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

function vite_script($archivo)
{
    // Si es una URL externa (CDN, etc.), se devuelve directo
    if (preg_match('/^https?:\/\//', $archivo)) {
        return "<script src=\"{$archivo}\"></script>";
    }

    $isDev = ($_ENV['ENVIRONMENT'] ?? 'development') === 'development';

    if ($isDev) {
        return "<script type=\"module\" src=\"http://localhost:5173/{$archivo}\"></script>";
    }

    $manifest = json_decode(file_get_contents(__DIR__ . '/../public/build/.vite/manifest.json'), true);
    $file = $manifest[$archivo]['file'] ?? '';

    return "<script type=\"module\" src=\"/build/{$file}\"></script>";
}
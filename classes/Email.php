<?php
namespace Classes;

use PHPMailer\PHPMailer\PHPMailer;

class Email
{
    protected $email;
    protected $nombre;
    protected $token;

    public function __construct($email, $nombre, $token)
    {
        $this->email = $email;
        $this->nombre = $nombre;
        $this->token = $token;
    }

    // Devuelven true si el servidor de correo aceptó el mensaje y false si no.
    public function enviarConfirmacion(): bool
    {
        $nombre = $this->escapar($this->nombre);
        $enlace = $this->escapar($this->enlace('confirmar'));

        $contenido = '<html>';
        $contenido .= "<p><strong>Hola {$nombre}</strong> Has creado tu cuenta en ClaudeTask.com, solo debes confirmarla en el siguiente enlace</p>";
        $contenido .= "<p>Presiona aquí: <a href=\"{$enlace}\">Confirmar Cuenta</a></p>";
        $contenido .= '<p>Si tú no creaste esta cuenta, puedes ignorar el mensaje</p>';
        $contenido .= '</html>';

        return $this->enviar('Confirma tu Cuenta', $contenido);
    }

    public function enviarInstrucciones(): bool
    {
        $nombre = $this->escapar($this->nombre);
        $enlace = $this->escapar($this->enlace('reestablecer'));

        $contenido = '<html>';
        $contenido .= "<p><strong>Hola {$nombre}</strong> Parece que has olvidado tu password, sigue el siguiente enlace para recuperarlo</p>";
        $contenido .= "<p>Presiona aquí: <a href=\"{$enlace}\">Reestablecer Password</a></p>";
        $contenido .= '<p>Si tú no pediste este cambio, puedes ignorar el mensaje</p>';
        $contenido .= '</html>';

        return $this->enviar('Reestablecer tu Password', $contenido);
    }

    protected function escapar($texto): string
    {
        return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
    }

    protected function enlace(string $ruta): string
    {
        return rtrim((string) ($_ENV['APP_URL'] ?? ''), '/') . '/' . $ruta . '?token=' . urlencode((string) $this->token);
    }

    protected function enviar(string $asunto, string $html): bool
    {
        try {
            $mail = new PHPMailer(true); // true: lanza excepciones en vez de fallar en silencio
            $mail->isSMTP();
            $mail->Host = $_ENV['MAIL_HOST'] ?? '';
            $mail->SMTPAuth = true;
            $mail->Port = (int) ($_ENV['MAIL_PORT'] ?? 587);
            $mail->Username = $_ENV['MAIL_USERNAME'] ?? '';
            $mail->Password = $_ENV['MAIL_PASSWORD'] ?? '';
            $mail->Timeout = 10;
            $mail->CharSet = 'UTF-8';

            $mail->setFrom($_ENV['MAIL_FROM'] ?? 'cuentas@claudetask.com', 'ClaudeTask');
            // Al correo del propio usuario (ya validado al registrarse)
            $mail->addAddress((string) $this->email, (string) $this->nombre);
            $mail->Subject = $asunto;
            $mail->isHTML(true);
            $mail->Body = $html;

            return $mail->send();
        } catch (\Throwable $e) {
            // El motivo va al log; al usuario solo se le dice que no se pudo enviar
            error_log('Email: ' . $e->getMessage());
            return false;
        }
    }
}

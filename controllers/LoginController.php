<?php

namespace Controllers;

use Classes\Email;
use Model\IntentoLogin;
use Model\Usuario;
use MVC\Router;

class LoginController
{

    public static function login(Router $router)
    {
        $alertas = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verificarCsrf();
            $usuario = new Usuario($_POST);
            $alertas = $usuario->validarLoginDual();

            if (empty($alertas)) {
                $ident = trim($_POST['email']);
                $intento = IntentoLogin::deClave($ident);

                if ($intento->segundosRestantes() > 0) {
                    Usuario::setAlerta('error', 'Demasiados intentos. Espera ' . $intento->segundosRestantes() . ' segundos.');
                } else {
                    // Con "@" es tutor (correo); si no, tutelado (alias)
                    $encontrado = str_contains($ident, '@')
                        ? Usuario::where('email', $ident)
                        : Usuario::where('alias', $ident);
                    $rolEsperado = str_contains($ident, '@') ? 'tutor' : 'tutelado';

                    $valido = $encontrado
                        && $encontrado->rol === $rolEsperado
                        && $encontrado->confirmado
                        && password_verify($_POST['password'], $encontrado->password);

                    if ($valido) {
                        $intento->limpiar();
                        session_regenerate_id(true);
                        $_SESSION['id'] = $encontrado->id;
                        $_SESSION['nombre'] = $encontrado->nombre;
                        $_SESSION['rol'] = $encontrado->rol;
                        $_SESSION['login'] = true;
                        header('Location: ' . ($encontrado->rol === 'tutor' ? '/dashboard' : '/mis-tareas'));
                        exit;
                    }
                    $intento->fallar();
                    Usuario::setAlerta('error', 'Usuario o contraseña incorrectos');
                }
            }
        }
        $alertas = Usuario::getAlertas();
        //Render a la vista
        $router->render(
            'auth/login',
            [
                'titulo' => 'Iniciar Sesión',
                'alertas' => $alertas
            ]
        );
    }

    public static function logout()
    {
        $_SESSION = [];
        header('Location: /'); exit;


    }
    /* section  crear [inicio] */
    public static function crear(Router $router)
    {

        $alertas = [];
        $usuario = new Usuario;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verificarCsrf();
            $usuario->sincronizar($_POST);
            // El cliente no decide estos campos
            $usuario->id = null;
            $usuario->rol = 'tutor';
            $usuario->alias = null;
            $usuario->curso = null;
            $usuario->creado_por = null;
            $usuario->confirmado = 0;
            //debuguear($usuario);
            $alertas = $usuario->validarNuevaCuenta();
            // debuguear($alertas);

            if (empty($alertas)) {
                $existeUsuario = Usuario::where('email', $usuario->email);
                // debuguear($existeUsuario);
                if ($existeUsuario) {
                    Usuario::setAlerta('error', 'El usuario ya esta registrado');
                    $alertas = Usuario::getAlertas();
                } else {
                    //hashear el password
                    $usuario->hashPassword();
                    //eliminar password2
                    unset($usuario->password2);

                    //Generar token
                    $usuario->crearToken();

                    // debuguear($usuario);

                    //Crear un nuevo usuario
                    $resultado = $usuario->guardar();

                    //enviar email
                    $email = new Email($usuario->email, $usuario->nombre, $usuario->token);
                    // debuguear($email);
                    $email->enviarConfirmacion();
                    if ($resultado) {
                        header('Location: /mensaje'); exit;
                    }
                }
            }
        }
        $router->render(
            'auth/crear',
            [
                'titulo' => 'Crea tu cuenta en ClaudeTask',
                'usuario' => $usuario,
                'alertas' => $alertas
            ]
        );
    }
    /* !section  fin - crear [fin] */

    //Formulario de olvide mi password
    public static function olvide(Router $router)
    {

        $alertas = [];


        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verificarCsrf();
            $usuario = new Usuario($_POST);
            $alertas = $usuario->validarEmail();
            if (empty($alertas)) {
                //buscar el usuario
                $usuario = Usuario::where('email', $usuario->email);
                // debuguear($usuario);
                if ($usuario && $usuario->rol === 'tutor' && $usuario->confirmado === "1") {
                    //generar token
                    $usuario->crearToken();
                    unset($usuario->password2);

                    //actualizar el usuario
                    $usuario->guardar();

                    //enviar el email
                    $email = new Email($usuario->email, $usuario->nombre, $usuario->token);
                    $email->enviarInstrucciones();


                    //imprimir alerta
                    Usuario::setAlerta('exito', 'Hemos enviado las instrucciones a tu email');
                    // debuguear($usuario);
                } else {
                    Usuario::setAlerta('error', 'El usuario no existe o no esta confirmado');
                }
            }
        }
        $alertas = Usuario::getAlertas();
        //muestra la vista
        $router->render(
            'auth/olvide',
            [
                'titulo' => 'Olvide mi password',
                'alertas' => $alertas
            ]
        );
    }
    //Colocar el nuevo password
    public static function reestablecer(Router $router)
    {

        $token = (isset($_GET['token']) && is_string($_GET['token'])) ? s($_GET['token']) : '';
        $mostrar = true;


        if (!$token) {
            header('Location: /');
            exit;
        }
        //Identificar el usuario con este token
        $usuario = Usuario::where('token', $token);

        if (empty($usuario)) {
            Usuario::setAlerta('error', 'Token no valido');
            $mostrar = false;
        }
        // Sin usuario (token no válido) no hay nada que guardar
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $usuario) {
            verificarCsrf();

            // Solo se acepta el password nuevo. No se usa sincronizar($_POST): el cliente
            // podría fijar id, rol, email... y escribir sobre otra cuenta.
            $nuevo = $_POST['password'] ?? '';
            $usuario->password = is_string($nuevo) ? $nuevo : '';

            //Validar el password
            $alertas = $usuario->validarPassword();

            if (empty($alertas)) {
                //hashear el nuevo password
                $usuario->hashPassword();

                //Eliminar el token
                $usuario->token = null;

                //Guardar el usuario en la BD
                $resultado = $usuario->guardar();
                //Redireccionar
                if ($resultado) {
                    header('Location: /'); exit;
                }

                // debuguear($usuario);

            }

        }

        $alertas = Usuario::getAlertas();
        //muestra la vista
        $router->render(
            'auth/reestablecer',
            [
                'titulo' => 'Reestablecer password',
                'alertas' => $alertas,
                'mostrar' => $mostrar
            ]
        );
    }
    //Muestra el mensaje de confirmación
    public static function mensaje(Router $router)
    {
        $router->render(
            'auth/mensaje',
            [
                'titulo' => 'Cuenta creada exitosamente',
            ]
        );
    }

    public static function confirmar(Router $router)
    {

        $token = isset($_GET['token']) ? s($_GET['token']) : '';
        // debuguear($token);
        if (!$token) {
            header('Location: /');
            exit; // importante: detener ejecución tras redirigir
        }

        //Encontrar al usuario con este token
        $usuario = Usuario::where('token', $token);
        // debuguear($usuario);

        if (empty($usuario)) {
            //No se encontro al usuario
            Usuario::setAlerta('error', 'Token no valido');
        } else {
            //confirmar la cuenta
            $usuario->confirmado = "1";
            $usuario->token = null;
            unset($usuario->password2);

            // debuguear($usuario);
            $usuario->guardar();
            Usuario::setAlerta('exito', 'Cuenta confirmada correctamente');

        }
        $alertas = Usuario::getAlertas();

        $router->render(
            'auth/confirmar',
            [
                'titulo' => 'Confirma tu cuenta ClaudeTask',
                'alertas' => $alertas
            ]
        );

    }
}

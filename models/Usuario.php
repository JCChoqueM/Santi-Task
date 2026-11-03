<?php

namespace Model;

class Usuario extends ActiveRecord
{
    protected static $tabla = 'usuarios';
    protected static $columnasDB = ['id', 'nombre', 'email', 'password', 'token', 'confirmado'];

    public $id = null;
    public $nombre = '';
    public $email = '';
    public $password = '';
    public $password2 = '';
    public $password_actual = '';
    public $password_nuevo = '';
    public $token = '';
    public $confirmado = 0;

    public function __construct($args = [])
    {
        $this->id = $args['id'] ?? null;
        $this->nombre = $args['nombre'] ?? '';
        $this->email = $args['email'] ?? '';
        $this->password = $args['password'] ?? '';
        $this->password2 = $args['password2'] ?? '';
        $this->password_actual = $args['password_actual'] ?? '';
        $this->password_nuevo = $args['password_nuevo'] ?? '';
        $this->token = $args['token'] ?? '';
        $this->confirmado = $args['confirmado'] ?? 0;
    }

    /* section4 validar el login de Usuario[inicio] */
    public function validarLogin()
    {


        if (!$this->email) {
            self::$alertas['error'][] = 'El Email es Obligatorio';
        }
        if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            self::$alertas['error'][] = 'Email no valido';
        }


        if (!$this->password) {
            self::$alertas['error'][] = 'El Password no puede ir vacio';
        }

        return self::$alertas;

    }
    /* !section4 fin - validar el login de Usuario[fin] */


    /* section1 validacion para cuetnas nuevas[inicio] */
    public function validarNuevaCuenta()
    {
        if (!$this->nombre) {
            self::$alertas['error'][] = 'El Nombre es Obligatorio';
        }

        if (!$this->email) {
            self::$alertas['error'][] = 'El Email es Obligatorio';
        }

        if (!$this->password) {
            self::$alertas['error'][] = 'El Password no puede ir vacio';
        }
        if (strlen($this->password) < 6) {
            self::$alertas['error'][] = 'El Password debe contener al menos 6 caracteres';
        }
        if (!$this->password2) {
            self::$alertas['error'][] = 'El Password2 no puede ir vacio';
        }
        if ($this->password !== $this->password2) {
            self::$alertas['error'][] = 'Los Passwords no son iguales';
        }

        return self::$alertas;
    }
    /* !section1 fin - validacion para cuetnas nuevas[fin] */

    /* section2 valida un email[inicio] */

    public function validarEmail()
    {
        if (!$this->email) {
            self::$alertas['error'][] = 'El Email es Obligatorio';
        }

        if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            self::$alertas['error'][] = 'Email no valido';
        }
        return self::$alertas;
    }

    /* !section2 fin - valida un email[fin] */

    /* section3 validarPassword[inicio] */

    public function validarPassword()
    {
        if (!$this->password) {
            self::$alertas['error'][] = 'El Password no puede ir vacio';
        }
        if (strlen($this->password) < 6) {
            self::$alertas['error'][] = 'El Password debe contener al menos 6 caracteres';
        }
        return self::$alertas;
    }
    /* !section3 fin - validarPassword[fin] */
    public function validar_perfil()
    {
        if (!$this->nombre) {
            self::$alertas['error'][] = 'El Nombre es Obligatorio';
        }
        if (!$this->email) {
            self::$alertas['error'][] = 'El Email es Obligatorio';
        }
        return self::$alertas;
    }

    /* SECTION  nuevo password[inicio] */
    public function nuevo_password(): array
    {
        if (!$this->password_actual) {
            self::$alertas['error'][] = 'El Password Actual no puede ir vacio';
        }
        if (!$this->password_nuevo) {
            self::$alertas['error'][] = 'El Password Nuevo no puede ir vacio';
        }
        if (strlen($this->password_nuevo) < 6) {
            self::$alertas['error'][] = 'El Password debe contener al menos 6 caracteres';
        }
        return self::$alertas;
    }
    /* !SECTION  fin - nuevo password[fin] */
    /* section2 comprobar_passwrod[inicio] */
    public function comprobar_password(): bool
    {
        return password_verify($this->password_actual, $this->password);
    }

    /* !section2 fin - comprobar_passwrod[fin] */


    /* SECTION  hashea el password[inicio] */
    public function hashPassword(): void
    {
        $this->password = password_hash($this->password, PASSWORD_BCRYPT);
    }
    /* !SECTION  fin - hashea el password[fin] */

    /* section1 generar un token[inicio] */
    public function crearToken(): void
    {
        $this->token = uniqid();  //tb se puede poner md5(uniqueid()); que genera 32 caracteres pero en bd esta configurado con 15 
    }
    /* !section1 fin - generar un token[fin] */
}
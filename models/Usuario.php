<?php

namespace Model;

class Usuario extends ActiveRecord
{
    protected static $tabla = 'usuarios';
    protected static $columnasDB = [
        'id', 'rol', 'nombre', 'email', 'alias', 'curso',
        'password', 'token', 'token_expira', 'confirmado', 'creado_por'
    ];

    public $id = null;
    public $rol = 'tutor';
    public $nombre = '';
    public $email = null;
    public $alias = null;
    public $curso = null;
    public $password = '';
    public $password2 = '';
    public $password_actual = '';
    public $password_nuevo = '';
    public $token = '';
    // Hora Unix en que caduca el token de recuperar contraseña; NULL en el de confirmar cuenta (no caduca)
    public $token_expira = null;
    public $confirmado = 0;
    public $creado_por = null;

    public function __construct($args = [])
    {
        $this->id = $args['id'] ?? null;
        $this->rol = $args['rol'] ?? 'tutor';
        $this->nombre = $args['nombre'] ?? '';
        $this->email = $args['email'] ?? null;
        $this->alias = $args['alias'] ?? null;
        $this->curso = $args['curso'] ?? null;
        $this->creado_por = $args['creado_por'] ?? null;
        $this->password = $args['password'] ?? '';
        $this->password2 = $args['password2'] ?? '';
        $this->password_actual = $args['password_actual'] ?? '';
        $this->password_nuevo = $args['password_nuevo'] ?? '';
        $this->token = $args['token'] ?? '';
        $this->confirmado = $args['confirmado'] ?? 0;
    }

    // El login acepta correo o alias
    public function validarLoginDual(): array
    {
        $id = trim((string) ($this->email ?? ''));
        if ($id === '') {
            self::$alertas['error'][] = 'Escribe tu correo o tu alias';
        }
        if (!$this->password) {
            self::$alertas['error'][] = 'El Password no puede ir vacio';
        }
        return self::$alertas;
    }

    public function validarTutelado(): array
    {
        if (!trim($this->nombre)) {
            self::$alertas['error'][] = 'Escribe el nombre del tutelado';
        }
        if (mb_strlen(trim($this->nombre)) > 100) {
            self::$alertas['error'][] = 'El nombre es demasiado largo (máximo 100 caracteres)';
        }
        if ($this->curso !== null && mb_strlen($this->curso) > 60) {
            self::$alertas['error'][] = 'El curso es demasiado largo (máximo 60 caracteres)';
        }
        if (strlen($this->password) < 6) {
            self::$alertas['error'][] = 'La contraseña necesita al menos 6 caracteres';
        }
        return self::$alertas;
    }

    // Alias único: Nombre + número que no sea predecible
    public static function generarAlias(string $nombre): string
    {
        $primero = explode(' ', trim($nombre))[0] ?? '';
        $ascii = iconv('UTF-8', 'ASCII//TRANSLIT', $primero) ?: '';
        $base = substr(ucfirst(strtolower(preg_replace('/[^A-Za-z]/', '', $ascii))), 0, 30); // alias <= 34 de 60
        if ($base === '') {
            throw new \Exception('Escribe un nombre con letras.');
        }

        foreach ([[2, 25], [3, 40], [4, 80]] as [$digitos, $intentos]) {
            for ($i = 0; $i < $intentos; $i++) {
                $n = (string) random_int(10 ** ($digitos - 1), 10 ** $digitos - 1);
                if (self::numeroPrevisible($n)) continue;
                $alias = $base . $n;
                if (!self::where('alias', $alias)) return $alias;
            }
        }
        throw new \Exception('No se pudo generar un alias único. Prueba con otro nombre.');
    }

    private static function numeroPrevisible(string $s): bool
    {
        $malos = ['00', '12', '19', '20', '000', '123', '321', '100', '1234', '4321'];
        if (in_array($s, $malos, true)) return true;
        if (strlen($s) === 4) {
            if (preg_match('/^(19|20)/', $s)) return true;
            $dd = (int) substr($s, 0, 2);
            $mm = (int) substr($s, 2);
            if ($dd >= 1 && $dd <= 31 && $mm >= 1 && $mm <= 12) return true;
        }
        return false;
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
        } elseif (mb_strlen($this->nombre) > 100) {
            self::$alertas['error'][] = 'El nombre es demasiado largo (máximo 100 caracteres)';
        }

        if (!$this->email) {
            self::$alertas['error'][] = 'El Email es Obligatorio';
        } elseif (!filter_var($this->email, FILTER_VALIDATE_EMAIL) || mb_strlen($this->email) > 255) {
            self::$alertas['error'][] = 'Email no valido';
        }

        if (!$this->password) {
            self::$alertas['error'][] = 'El Password no puede ir vacio';
        }
        if (strlen($this->password) < 6) {
            self::$alertas['error'][] = 'El Password debe contener al menos 6 caracteres';
        }
        if (strlen($this->password) > 72) {   // bcrypt solo usa los primeros 72 bytes
            self::$alertas['error'][] = 'El Password no puede pasar de 72 caracteres';
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
        } elseif (mb_strlen($this->nombre) > 100) {
            self::$alertas['error'][] = 'El nombre es demasiado largo (máximo 100 caracteres)';
        }
        if (!$this->email) {
            self::$alertas['error'][] = 'El Email es Obligatorio';
        } elseif (!filter_var($this->email, FILTER_VALIDATE_EMAIL) || mb_strlen($this->email) > 255) {
            self::$alertas['error'][] = 'Email no valido';
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
        if (strlen($this->password_nuevo) > 72) {   // bcrypt solo usa los primeros 72 bytes
            self::$alertas['error'][] = 'El Password no puede pasar de 72 caracteres';
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
        // uniqid() se deriva de la hora y es adivinable; el token de confirmar/reestablecer debe ser aleatorio.
        // usuarios.token es VARCHAR(255): caben los 32 caracteres.
        $this->token = bin2hex(random_bytes(16));
    }
    // Token de recuperar contraseña: aleatorio y con caducidad (por defecto 1 hora)
    public function crearTokenRecuperacion(int $segundos = 3600): void
    {
        $this->crearToken();
        $this->token_expira = time() + $segundos;
    }

    // Un token de recuperación siempre lleva caducidad; sin ella es de confirmación y no sirve para reestablecer
    public function esTokenRecuperacion(): bool
    {
        return $this->token_expira !== null && $this->token_expira !== '';
    }

    public function tokenCaducado(): bool
    {
        return $this->esTokenRecuperacion() && (int) $this->token_expira < time();
    }
    /* !section1 fin - generar un token[fin] */
}
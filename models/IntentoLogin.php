<?php

namespace Model;

class IntentoLogin extends ActiveRecord
{
    protected static $tabla = 'intentos_login';
    protected static $columnasDB = ['id', 'clave', 'intentos', 'bloqueado_hasta'];

    public $id;
    public $clave;
    public $intentos;
    public $bloqueado_hasta;

    public function __construct($args = [])
    {
        $this->id = $args['id'] ?? null;
        $this->clave = $args['clave'] ?? '';
        $this->intentos = $args['intentos'] ?? 0;
        $this->bloqueado_hasta = $args['bloqueado_hasta'] ?? 0;
    }

    public static function deClave(string $clave): self
    {
        $clave = strtolower(trim($clave));
        return self::where('clave', $clave) ?: new self(['clave' => $clave]);
    }

    public function segundosRestantes(): int
    {
        return max(0, (int) $this->bloqueado_hasta - time());
    }

    public function fallar(): void
    {
        $this->intentos = (int) $this->intentos + 1;
        if ($this->intentos >= 5) {
            $this->bloqueado_hasta = time() + 30;
            $this->intentos = 0;
        }
        $this->guardar();
    }

    public function limpiar(): void
    {
        if ($this->id) {
            $this->eliminar();
        }
    }
}

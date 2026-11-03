<?php
namespace Model;

class Vinculo extends ActiveRecord
{
    protected static $tabla = 'vinculos';
    protected static $columnasDB = ['tutor_id', 'tutelado_id'];

    public $tutor_id, $tutelado_id;

    public function __construct($args = [])
    {
        $this->tutor_id = (int) ($args['tutor_id'] ?? 0);
        $this->tutelado_id = (int) ($args['tutelado_id'] ?? 0);
    }

    public static function existe(int $tutor, int $tutelado): bool
    {
        $r = self::SQL("SELECT * FROM vinculos WHERE tutor_id = {$tutor} AND tutelado_id = {$tutelado} LIMIT 1");
        return !empty($r);
    }

    public static function crearVinculo(int $tutor, int $tutelado): void
    {
        self::$db->query("INSERT IGNORE INTO vinculos (tutor_id, tutelado_id) VALUES ({$tutor}, {$tutelado})");
    }

    public static function quitar(int $tutor, int $tutelado): void
    {
        self::$db->query("DELETE FROM vinculos WHERE tutor_id = {$tutor} AND tutelado_id = {$tutelado} LIMIT 1");
    }

    /** @return Usuario[] */
    public static function tutoresDe(int $tutelado): array
    {
        return Usuario::SQL("SELECT u.* FROM usuarios u JOIN vinculos v ON v.tutor_id = u.id WHERE v.tutelado_id = {$tutelado}");
    }

    /** @return Usuario[] */
    public static function tuteladosDe(int $tutor): array
    {
        return Usuario::SQL("SELECT u.* FROM usuarios u JOIN vinculos v ON v.tutelado_id = u.id WHERE v.tutor_id = {$tutor} ORDER BY u.nombre");
    }
}

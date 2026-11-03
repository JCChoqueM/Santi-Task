<?php
namespace Model;

class Tarea extends ActiveRecord
{
    protected static $tabla = 'tareas';
    protected static $columnasDB = ['id', 'url', 'tutelado_id', 'creada_por', 'titulo', 'descripcion', 'fecha_limite'];

    public $id, $url, $tutelado_id, $creada_por, $titulo, $descripcion, $fecha_limite;

    public function __construct($args = [])
    {
        $this->id = $args['id'] ?? null;
        $this->url = $args['url'] ?? '';
        $this->tutelado_id = $args['tutelado_id'] ?? '';
        $this->creada_por = $args['creada_por'] ?? '';
        $this->titulo = trim($args['titulo'] ?? '');
        $this->descripcion = trim($args['descripcion'] ?? '');
        $this->fecha_limite = ($args['fecha_limite'] ?? '') ?: null;
    }

    public function validar(): array
    {
        if (!$this->titulo) {
            self::$alertas['error'][] = 'La tarea necesita un título';
        }
        return self::$alertas;
    }

    /** @return Parte[] ordenadas */
    public function partes(): array
    {
        $partes = Parte::belongsTo('tarea_id', $this->id);
        usort($partes, fn($a, $b) => $a->orden <=> $b->orden);
        return $partes;
    }

    public function progreso(): array
    {
        $partes = $this->partes();
        $total = count($partes);
        $ok = count(array_filter($partes, fn($p) => $p->estado === 'aprobada'));
        return ['ok' => $ok, 'total' => $total, 'completa' => $total > 0 && $ok === $total];
    }

    public function vencida(): bool
    {
        return $this->fecha_limite && $this->fecha_limite < date('Y-m-d') && !$this->progreso()['completa'];
    }

    public static function generarUrl(): string
    {
        return md5(uniqid((string) mt_rand(), true));
    }
}

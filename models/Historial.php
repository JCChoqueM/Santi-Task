<?php
namespace Model;

class Historial extends ActiveRecord
{
    protected static $tabla = 'historial';
    // creado_en lo llena MySQL (DEFAULT CURRENT_TIMESTAMP): no va aquí
    protected static $columnasDB = ['id', 'tarea_id', 'parte', 'accion', 'por'];

    public $id, $tarea_id, $parte, $accion, $por;

    // Solo lectura: crearObjeto() las rellena al consultar, pero no se guardan.
    // nombre_usuario viene del JOIN de TareaController::historial().
    public $creado_en;
    public $nombre_usuario;

    public function __construct($args = [])
    {
        $this->id = $args['id'] ?? null;
        $this->tarea_id = $args['tarea_id'] ?? '';
        $this->parte = $args['parte'] ?? '';
        $this->accion = $args['accion'] ?? '';
        $this->por = $args['por'] ?? '';
    }

    public static function registrar(int $tarea, string $parte, string $accion, int $por): void
    {
        (new self(['tarea_id' => $tarea, 'parte' => $parte, 'accion' => $accion, 'por' => $por]))->guardar();
    }
}

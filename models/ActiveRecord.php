<?php
namespace Model;
class ActiveRecord
{

    // Base DE DATOS
    protected static $db;
    protected static $tabla = '';
    protected static $columnasDB = [];

    // Alertas y Mensajes
    protected static $alertas = [];
    public $id = null;

    // Definir la conexión a la BD - includes/database.php
    public static function setDB($database)
    {
        self::$db = $database;
    }

    public static function setAlerta($tipo, $mensaje)
    {
        static::$alertas[$tipo][] = $mensaje;
    }
    // Validación
    public static function getAlertas()
    {
        return static::$alertas;
    }

    public function validar()
    {
        static::$alertas = [];
        return static::$alertas;
    }

    // Solo letras, números y guion bajo en nombres de columna (C-01)
    protected static function columnaSegura(string $columna): string
    {
        if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $columna)) {
            throw new \InvalidArgumentException('Columna no válida');
        }
        return $columna;
    }

    // Valor SQL: NULL real o texto entre comillas (A-01). El valor ya viene escapado.
    protected static function literal($valor): string
    {
        return is_null($valor) ? 'NULL' : "'{$valor}'";
    }

    // Registros - CRUD
    public function guardar()
    {
        $resultado = '';
        if (!is_null($this->id)) {
            // actualizar
            $resultado = $this->actualizar();
        } else {
            // Creando un nuevo registro
            $resultado = $this->crear();
        }
        return $resultado;
    }

    public static function all()
    {
        $query = "SELECT * FROM " . static::$tabla;
        $resultado = self::consultarSQL($query);
        return $resultado;
    }

    // Busca un registro por su id
    /**
     * @return static|false
     */
    public static function find($id)
    {
        $id = (int) $id;
        $query = "SELECT * FROM " . static::$tabla . " WHERE id = {$id}";
        $resultado = self::consultarSQL($query);
        return array_shift($resultado);
    }

    // Obtener Registro
    public static function get($limite)
    {
        $query = "SELECT * FROM " . static::$tabla . " LIMIT {$limite}";
        $resultado = self::consultarSQL($query);
        return array_shift($resultado);
    }

    // Busqueda Where con Columna 
    /**
     * @return static|false
     */

    //Busqueda Where con Columna
    public static function where($columna, $valor)
    {
        $columna = static::columnaSegura($columna);
        $valor = self::$db->escape_string((string) $valor);
        $query = "SELECT * FROM " . static::$tabla . " WHERE {$columna} = '{$valor}'";
        $resultado = self::consultarSQL($query);
        return array_shift($resultado);
    }

    //Busca todos los registros que pertenecen a un ID
    public static function belongsTo($columna, $valor)
    {
        $columna = static::columnaSegura($columna);
        $valor = self::$db->escape_string((string) $valor);
        $query = "SELECT * FROM " . static::$tabla . " WHERE {$columna} = '{$valor}'";
        $resultado = self::consultarSQL($query);
        return ($resultado);
    }
    // SQL para Consultas Avanzadas.
    public static function SQL($consulta)
    {
        $query = $consulta;
        $resultado = self::consultarSQL($query);
        return $resultado;
    }

    // crea un nuevo registro
    public function crear()
    {
        $atributos = $this->sanitizarAtributos();
        $valores = array_map([static::class, 'literal'], array_values($atributos));

        $query = "INSERT INTO " . static::$tabla . " (" . join(', ', array_keys($atributos)) . ") ";
        $query .= "VALUES (" . join(', ', $valores) . ")";

        $resultado = self::$db->query($query);
        return ['resultado' => $resultado, 'id' => self::$db->insert_id];
    }

    public function actualizar()
    {
        $atributos = $this->sanitizarAtributos();
        $valores = [];
        foreach ($atributos as $key => $value) {
            $valores[] = "{$key} = " . static::literal($value);
        }
        $id = self::$db->escape_string((string) $this->id);
        $query = "UPDATE " . static::$tabla . " SET " . join(', ', $valores) . " WHERE id = '{$id}' LIMIT 1";
        return self::$db->query($query);
    }

    // Eliminar un registro - Toma el ID de Active Record
    public function eliminar()
    {
        $query = "DELETE FROM " . static::$tabla . " WHERE id = " . self::$db->escape_string($this->id) . " LIMIT 1";
        $resultado = self::$db->query($query);
        return $resultado;
    }

    public static function consultarSQL($query)
    {
        // Consultar la base de datos
        $resultado = self::$db->query($query);

        // Iterar los resultados
        $array = [];
        while ($registro = $resultado->fetch_assoc()) {
            $array[] = static::crearObjeto($registro);
        }

        // liberar la memoria
        $resultado->free();

        // retornar los resultados
        return $array;
    }

    protected static function crearObjeto($registro)
    {
        $objeto = new static;

        foreach ($registro as $key => $value) {
            if (property_exists($objeto, $key)) {
                $objeto->$key = $value;
            }
        }

        return $objeto;
    }

    // Identificar y unir los atributos de la BD
    public function atributos()
    {
        $atributos = [];
        foreach (static::$columnasDB as $columna) {
            if ($columna === 'id')
                continue;
            $atributos[$columna] = $this->$columna;
        }
        return $atributos;
    }

    public function sanitizarAtributos()
    {
        $atributos = $this->atributos();
        $sanitizado = [];
        foreach ($atributos as $key => $value) {
            $sanitizado[$key] = is_null($value) ? null : self::$db->escape_string((string) $value);
        }
        return $sanitizado;
    }

    public function sincronizar($args = [])
    {
        foreach ($args as $key => $value) {
            if (property_exists($this, $key) && !is_null($value)) {
                $this->$key = $value;
            }
        }
    }
}
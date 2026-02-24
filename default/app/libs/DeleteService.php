<?php
class DeleteService
{
    protected $db;

    public function __construct()
    {
        $this->db = Db::factory();
    }

    public function delete($table, $id, $pk = 'id')
    {
        try {
            return $this->db->delete($table, "$pk = $id");
        } catch (KumbiaException $e) {

            // 🔑 Kumbia NO pasa el código 1451 → detectar por mensaje
            if (stripos($e->getMessage(), 'foreign key constraint fails') !== false) {
                return $this->fkError($table, $id);
            }

            // Si no es FK, sí relanzamos
            throw $e;
        }
    }

    protected function fkError($table, $id)
    {
        $dependencias = $this->getDependencias($table);
        $bloqueos = [];

        foreach ($dependencias as $dep) {

            $hijos = $this->db->fetch_all(
                "SELECT id FROM {$dep['tabla']}
                 WHERE {$dep['columna']} = $id
                 LIMIT 5"
            );

            if ($hijos) {
                $bloqueos[] = [
                    'tabla' => $dep['tabla'],
                    'ids'   => array_column($hijos, 'id')
                ];
            }
        }

        return [
            'error'    => true,
            'tipo'     => 'FK',
            'bloqueos' => $bloqueos
        ];
    }

    protected function getDependencias($tablaPadre)
    {
        $sql = "
            SELECT TABLE_NAME AS tabla, COLUMN_NAME AS columna
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
            WHERE REFERENCED_TABLE_SCHEMA = DATABASE()
              AND REFERENCED_TABLE_NAME = '$tablaPadre'
        ";

        return $this->db->fetch_all($sql);
    }
}

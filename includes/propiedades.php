
<?php
/**
 * Clase que mantiene la tabla propiedades
 *
 * @autor   Edgar Messia
 * @static  
 * @package     Valoriza2.Framework
 * @subpackage	FileSystem
 * @since	1.0
 */

class propiedades extends db implements crud {
    const tabla = "propiedades";

    public function actualizar($id, $data){
        return db::update(self::tabla, $data, array("id" => $id));
    }

    public function borrar($id){
        return db::delete(self::tabla, array("id" => $id));
    }

    /**
     * Inserta el contenido en la tabla propietarios
     *
     * @param	Array	$data	Arreglo con la data
     * 
     * @return	Array	Retorna arreglo con parámetos del resultado
     * @since	1.0
     */
    public function insertar($data){
        return db::insert(self::tabla, $data);
    }

    public function listar(){
       return db::select("*", self::tabla);
    }
    
    public function ver($cedula){
        return db::select("*",self::tabla,array("cedula"=>$cedula));
    }

    public function borrarTodo() {
        return db::delete(self::tabla);
    }

    public function propiedadesPropietario($cedula,$cod_admin,$order = null) {
        $sql = "select * from propiedades where cedula=$cedula and cod_admin='$cod_admin' order by id_inmueble";
        $result = db::query($sql);
        return $result;
    }
    
    public function inmueblePorPropietario($cedula,$cod_admin) {
        return db::query("SELECT id_inmueble FROM propiedades WHERE cedula=$cedula and cod_admin='$cod_admin' order by id_inmueble");
    }
    
    public function verPropiedad($inmueble, $apto,$cod_admin) {
        return db::dame_query("select * from ".self::tabla." where id_inmueble='$inmueble' and apto='$apto' and cod_admin='$cod_admin'");
    }
    
}

<?php

/**
 * Description of junta_condominio
 *
 * @author emessia
 */
class junta_condominio extends db implements crud{
    const tabla = "junta_condominio";
    
    public function actualizar($id, $data) {
        return db::update(self::tabla, $data, array("id" => $id));
    }

    public function borrar($id) {
        return db::delete(self::tabla, array("id" => $id));
    }

    public function borrarTodo() {
        return db::delete(self::tabla);
    }

    public function insertar($data) {
        return db::insert(self::tabla, $data);
    }

    public function listar() {
        return db::select("*", self::tabla);
    }

    public function ver($id) {
        return db::select("*",self::tabla,array("id"=>$id));
    }
    
    public function listarJuntaPorInmueble($id_inmueble,$cod_admin) {
        $consulta = "SELECT p.cedula, p.nombre, p.telefono1, p.email, c.descripcion, pro.apto, pro.id_inmueble  
            FROM junta_condominio jc 
            JOIN propietarios p on jc.cedula = p.cedula and jc.cod_admin = p.cod_admin 
            JOIN cargo_jc c on jc.id_cargo = c.id
            JOIN propiedades pro ON p.cedula = pro.cedula and pro.cod_admin = p.cod_admin
            where jc.id_inmueble ='$id_inmueble' and jc.cod_admin='$cod_admin'  
            ORDER BY c.id";
        
        return db::query($consulta);
    }
}
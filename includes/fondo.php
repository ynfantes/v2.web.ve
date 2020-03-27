<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of fondo
 *
 * @author Valoriza 2
 */
class fondo extends db implements crud {
    const tabla = "fondos";
    public function actualizar($id, $data) {
        return db::update(self::tabla, $data, array("id" => $id));
    }

    public function borrar($id) {
       return db::delete(self::tabla, array("id" => $id)); 
    }

    public function borrarTodo() {
        return db::delete(self::tabla);
    }

    public function insertarRegistroFondo($data,$update) {
        return db::insertUpdate(self::tabla, $data,$update);
    }
    
    public function insertar($data) {
        return db::insert(self::tabla, $data);
    }
    
    public function insertarMovimiento($data) {
        return db::insert("fondos_movimiento",$data);
    }
    
    public function obtenerIdCuentaFondo($codigo_condominio,$codigo_gasto,$cod_admin) {
        return db::select("*",self::tabla,
                Array(
                    "id_inmueble"   => $codigo_condominio,
                    "cod_admin"     => $cod_admin,
                    "codigo_gasto"  => $codigo_gasto));
        
    }

    public function listar() {
        return db::select("*", self::tabla,null,null,Array("codigo_gasto"=>"ASC"));
    }
    
    public function listarCuentasDeFondoInmueble($id_inmueble,$cod_admin) {
        return db::select("*",self::tabla,
                Array(
                    "id_inmueble"   =>$id_inmueble,
                    "cod_admin"     => $cod_admin,
                    "mostrar"       => 1
                    ),null,Array("codigo_gasto"=>"ASC"));
    }
    
    public function ver($id) {
        return db::select("*",self::tabla,array("id"=>$id));
    }

    public function consultaEstadoDeCuentaFondo($id_inmueble,$codigo_gasto,$cod_admin) {
        $consulta = "select m.* from fondos_movimiento m join fondos f on m.id_fondo=f.id where "
                . "f.id_inmueble='$id_inmueble' and f.codigo_gasto='$codigo_gasto' and "
                . "f.cod_admin='$cod_admin' order by m.fecha, m.debe, m.concepto";
        return db::query($consulta);
    }
}

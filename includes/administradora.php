<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of administradora
 *
 * @author Valoriza2
 */
class administradora extends db implements crud {
    const tabla = "administradoras";
    public function actualizar($id, $data) {
        return db::update(self::tabla, $data, Array("id"=>$id));
    }

    public function borrar($id) {
        return db::delete(self::tabla, Array("id"=>$id));
    }

    public function borrarTodo() {
        return db::delete(self::tabla);
    }

    public function insertar($data) {
        return db::insert(self::tabla,$data);
    }

    public function listar() {
        return db::select("*", self::tabla);
    }

    public function ver($id) {
      return db::select("*",self::tabla,Array("id"=>$id)); 
    }
    
    public function verPorCodigo($codigo) {
        return db::select("*",self::tabla,Array("codigo"=>$codigo));
    }
    
    public function obtenerEmailAdministradora($codigo) {
        $email = '';
        $r = db::select("email",self::tabla,Array("codigo"=>$codigo));
        if ($r['suceed'] && count($r['data'])>0) {
            $email = $r['data'][0]['email'];
        }
        return $email;
    }
}

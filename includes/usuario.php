<?php
class usuario extends db implements crud  {
    
    const tabla = "usuarios";
    
    public function actualizar($id, $data) {
        return db::update(self::tabla, $data, Array("id"=>$id));
    }

    public function actualizarAsignacionCobrador($id, $data) {  
        return db::update("usuarios_acceso", $data, Array("id"=>$id));
    }
    public function borrar($id) {
        return db::delete(self::tabla, Array("id"=>$id));
    }
    
    public function borrarAsignacionCobrador($id) {
        return db::delete("usuarios_acceso", Array("id"=>$id));
    }
    public function borrarTodo() {
        return db::delete(self::tabla);
    }

    public function insertar($data) {
        return db::insert(self::tabla,$data);
    }

    public function insertarAsignacionCobrador($data) {
        return db::insert("usuarios_acceso",$data);
    }

    public function listar() {
        return db::select("*", self::tabla);
    }

    public function listarCajeros() {
        return db::select("*",self::tabla,Array("directorio"=>"'CAJA'"));
    }
    
    public function listarInmueblesAsignadosAlUsuario($id) {
        $sql= "select u.id, u.id_inmueble, u.id_usuario, i.nombre_inmueble from usuarios_acceso u join inmueble i on u.id_inmueble= i.id where u.id_usuario=".$id;
        return db::dame_query($sql);
    }
    public function ver($id) {
        
    }   
    
    public function login($usuario,$password) {
        
        if ($usuario!="" && $password!="") {
        
            $result = db::select("*",self::tabla,Array("nombre"=>"'".$usuario."'"));
            
            unset($result['query']);
            if ($result['suceed'] == 'true' && count($result['data']) > 0) {
                
                if ($result['data'][0]['clave']==$password) {
                    session_start();
                    $sesion = $this->generarIdInicioSesion($result['data'][0]['cedula']);
                    if ($sesion['suceed']) {
                        $_SESSION['id_sesion'] = $sesion['insert_id'];
                    }
                    $_SESSION['usuario'] = $result['data'][0];
                    $_SESSION['status'] = 'logueado';
                    $result['directorio']=$result['data'][0]['directorio'];
                    unset($result['data']);
                    //header("location:" . URL_INTRANET . "/" . $result['data'][0]['directorio'] );
                    
                } else {
                    unset($result['data']);
                    $result['suceed'] = false;
                    $result['error'] = "alert_pass";
                    
                }
            } else {
                $result['suceed'] = false;
                $result['error'] = "alert_user";
            }
            return $result;
        } else {
            $result['suceed'] = false;
            $result['error'] = "alert_mandatory";
            return $result;
        }
    }

    public static function esUsuarioLogueado() {
        session_start();
        
        if (!isset($_SESSION['status']) || $_SESSION['status'] != 'logueado' || !isset($_SESSION['usuario'])) {
            header("location:".ROOT."intranet.php");
            die();
        }
            
    }
    
    public static  function logout() {
        session_start();
        
        if (isset($_SESSION['status'])) {
        
            unset($_SESSION['status']);
            unset($_SESSION['usuario']);
            session_unset();
            session_destroy();
            if (isset($_COOKIE[session_name()]))
                setcookie(session_name(), '', time() - 1000);
            
        }
        header("location:" . ROOT . "intranet.php");
    }
    
    public function generarIdInicioSesion($cedula) {
        $sql = "insert into sesion(cedula,inicio,fin) values(".$cedula.",now(),now())";
        return db::exec_query($sql);
    }
}
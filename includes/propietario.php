<?php
/**
 * Clase que mantiene la tabla propietario
 *
 * @autor   Edgar Messia
 * @static  
 * @package     Valoriza2.Framework
 * @subpackage	FileSystem
 * @since	1.0
 */

class propietario extends db implements crud  {

    const tabla = "propietarios";

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
    
    public function cambioDeClave($id,$clave) {
        return db::update(self::tabla, Array("clave"=>$clave,"id"=>$id,"cambio_clave"=>1));
    }
    
    public function login($email = '', $password = '') {
        
        if ($email!="" && $password!="") {
            
            $result = db::select("*",self::tabla,Array("clave"=>$password,"email"=>$email,"baja"=>0));
            
            if ($result['suceed'] == 'true' && count($result['data']) > 0) {
                $administra         = array();
                $administradoras    = new administradora;
                $inactivo           = 0 ;
                $administradora     = $administradoras->verPorCodigo($result['data'][0]['cod_admin']);
                
                if ($administradora['suceed'] && count($administradora['data'])>0) {
                    
                    $administra     = $administradora['data'][0];
                    $inactivo       = $administra['inactivo'];
                    $result['error'] = '<strong>¡Servicio Suspendido!</strong> Póngase en contacto al correo: '.
                            $administra['email'].' ó <br/>info@administracion-condominio.com.ve';
                }
                
                // $consulta = "select * from propiedades where cedula in (SELECT cedula FROM `propietarios` where clave='$password' )";
                
                // $propiedades = db::query($consulta);

                $res = db::select("*","junta_condominio",Array("cedula"=>$result['data'][0]['cedula']));
                $junta_condominio = '';
                if ($res['suceed'] && count($res['data'])> 0) {
                    $junta_condominio = $res['data'][0]['id_inmueble'];
                } 
                // registramos la sesion del usuario
                $sesion = $this->generarIdInicioSesion($result['data'][0]['cod_admin'],$result['data'][0]['cedula']);
                if(session_status()  == PHP_SESSION_NONE) {
                    session_start();
                }
                if ($sesion['suceed']) {
                    $_SESSION['id_sesion'] = $sesion['insert_id'];
                }
                
                $_SESSION['usuario']    = $result['data'][0];
                $_SESSION['junta']      = $junta_condominio;
                $_SESSION['administra'] = $administra;
                $_SESSION['status']     = 'logueado';
                $result['suceed']       = true;
                $result['inactivo']     = $inactivo;
                $result['session_id']   = $result['data'][0]['id'];
                
            } else {
                
                if(session_status()  == PHP_SESSION_NONE) {
                    session_start();
                }
                // $result['id']       = $_SESSION['id'];
                $result['suceed']   = false;
                $result['error']    = "<strong>Ups! </strong> Ha ingresado un password incorrecto";
            }
            unset($result['query'],$result['data'],$result['stats']);
            
        } else {
            $result['suceed']   = false;
            $result['error']    = '<strong>Ups! </strong> Datos insuficientes';
        }
        
        return $result;
        
    }
    
    public function generarIdInicioSesion($cod_admin,$cedula) {
        $fecha = date("Y-m-d H:i:s", time());
        $sql = "insert into sesion(cod_admin,cedula,inicio,fin) 
        values('$cod_admin',$cedula,'$fecha','$fecha')";
        return db::exec_query($sql);
    }
    
    public function recuperarContraSena($id) {
        if ($id!="") {
            
            $result = db::select( "*", self::tabla, Array("id"=>$id) );
            
            if ($result['suceed'] == 'true' && count($result['data']) > 0) {
                
                if ($result['data'][0]['email']!='') {
                    
                    $template = '../../enlinea/plantillas/clave-servicio.html';
                    
                    if (file_exists($template)) {

                        $contenido = file_get_contents($template);
                        $mail = new mailto(SMTP);

                        foreach ($result['data'][0] as $key => $value) {
                            $contenido = str_replace("[".$key."]", $value, $contenido);
                        }
                        
                        $r = $mail->enviar_email(
                                "Recuperar Contraseña", 
                                $contenido, 
                                "", 
                                $result['data'][0]['email'],
                                $result['data'][0]['nombre']);
                        
                        if ($r=="") {
                            $result['suceed']=true;
                            $result['mensaje']="Clave enviada al email: ".$result['data'][0]['email'];
                        } else {
                            $result['suceed']=false;
                            $result['mensaje']="No se puedo enviar el correo electrónico.
                                Póngase en contacto con su administradora";
                        }
                    }
                } else {
                    $result['suceed']=false;
                    $result['mensaje']="No tenemos registrado un email a donde enviarle su contraseña.
                        Por favor póngase en contacto con su administradora para actualizar su información de
                        contacto.";
                }
            } else {
                $result=false;
                $result['mensaje']="Propietario no registrado. Si considera
                    que es un error, póngase en contacto su administradora.";
            }
        } else {
            $result['suceed']=false;
            $result['mensaje'] = "Debe introducir su número de cédula de identidad.";
            
        }
            unset($result['query'],$result['data'],$result['stats']);
            return $result;
    }
   
    public static function esPropietarioLogueado() {
        session_start();
        if (!isset($_SESSION['status']) || $_SESSION['status'] != 'logueado' || !isset($_SESSION['usuario'])) {
            header("location:".ROOT);
            die();
        }
    }
    
    public function logout() {
        session_start();
        if (isset($_SESSION['id_sesion'])) {
            $fecha = date("Y-m-d H:i:s ", time());
            $this->exec_query("update sesion set fin='$fecha' where id=".$_SESSION['id_sesion']);
        }
        if (isset($_SESSION['status'])) {
            unset($_SESSION['status']);
            unset($_SESSION['usuario']);

            if (isset($_COOKIE[session_name()]))
                setcookie(session_name(), '', time() - 1000);
            
        }
        session_unset();
        session_destroy();
        header("location:".ROOT);
    }
    
    public static function listarPropietariosClavesActualizadas() {
        $query = "select propiedades.id_inmueble, propiedades.apto , propietarios.id, propietarios.clave 
            from propietarios join propiedades
            on propietarios.cedula = propiedades.cedula
            where propietarios.cambio_clave=1 and baja=0";
        return db::query($query);
    }
    
    public function obtenerPropietariosActualizados($cod_admin) {
        $query = "SELECT p . * , pr.id_inmueble, pr.apto
            FROM propietarios p
            JOIN propiedades pr ON p.cedula = pr.cedula
            WHERE p.modificado = 1 and p.cod_admin='$cod_admin' 
            and baja=0 Order By pr.id_inmueble ASC,pr.apto";
        
        return $this->dame_query($query);
    }
    
    public function listarPropietariosConEmail($id = null) {
        $query = "SELECT p.*,pro.apto, pro.id_inmueble FROM propietarios "
                . "p join propiedades pro on p.cedula = pro.cedula "
                . "where p.email !='' and baja=0";
        if($id != null) {
            $query.= " and pro.cod_admin='".$id."'";
        }
        $query.=" order by pro.apto";
        //$query.= " limit 300,150";
        
        return $this->dame_query($query);
    }
    
    public static function obtenerInfoUltimasSesiones($cod_admin,$cedula, $sesion_actual) {
        $consulta = "SELECT id, inicio, fin, timediff(fin , inicio) as duracion 
            FROM sesion where id <".$sesion_actual ." and cedula=$cedula and 
            cod_admin='$cod_admin' order by id desc limit 0,5";
        return db::query($consulta);
    }

    public function envioMasivoEmail($asunto,$template, $id = null) {
        $propieatarios = $this->listarPropietariosConEmail($id);
        
        if ($propieatarios['suceed'] && count($propieatarios['data'])>0) {
            // cargamos el template
            if (file_exists($template)) {
                $contenido_original = file_get_contents($template);
                
                if ($contenido_original=='') {
                    echo "No se puedo cargar el contenido de ".$template;
                    die();
                }
                // enviamos el email a los destinatarios
                $resultado='';
                $n=1;
                $e=1;
                //$usuario = 'no-responder@v2.web.ve';
                $mail = new mailto(SMTP);
                foreach ($propieatarios['data'] as $propietario) {
                    
                    $contenido = $contenido_original;
                    // hacemos la personalizacion del contenido
                    foreach ($propietario as $key => $value) {
                        $contenido = str_replace("[".$key."]", $value, $contenido);
                    }
                    
                    // aquí enviamos el email
                    $destinatario = $propietario['email'];

                    $r = $mail->enviar_email($asunto, $contenido, '', $destinatario, $propietario['nombre']);
                    $resultado.= $n.".- Mensaje enviado a ".$destinatario;
                    if ($r == '') {
                        $resultado.= " Ok!\n";
                    } else {
                        $resultado.= " Falló\n";
                    }
                    $n++;
                    $e++;
                }
                echo nl2br($resultado);
                
            } else {
                echo $template." no existe";
            }
        }
    }
    
    public function obtenerPropietario($inmueble,$apto) {
        $consulta= "select * from propietarios where cedula in "
                . "(select cedula from propiedades "
                . "where id_inmueble='$inmueble' and apto='$apto')";
        $r = $this->query($consulta);
        $nombre = Array();
        if ($r['suceed'] && count($r['data'])>0) {
            $nombre = $r['data'];
        }
        return $nombre;
    }
    
    public function listarPropietariosPorInmueble($id_inmueble) {
        $consulta = "SELECT p.apto, (select nombre from propietarios where cedula = p.cedula limit 0,1) as nombre  FROM `propiedades` p where p.id_inmueble='$id_inmueble' and p.apto <> 'U$id_inmueble' order by p.apto";
        return $this->query($consulta);
        
    }
    
    public function listarPropietariosMorososPorInmueble($id_inmueble,$meses_mora=1) {
        $consulta = "SELECT DISTINCT p.apto, pr.nombre, pr.recibos FROM `propiedades` p join "
                . "propietarios pr on p.cedula = pr.cedula "
                . "where id_inmueble='$id_inmueble' and pr.recibos >=$meses_mora "
                . "and p.apto <> 'U$id_inmueble' order by p.apto";
        
        return $this->query($consulta);
        
    }
    
    public function emailRegistrado($email) {
        $r = db::select("*",self::tabla,array(
            "email" => $email,
            "baja"  => 0
                ));
        return $r;
    }
    
    public function registrarPropietario($data) {
        return db::insertupdate(self::tabla,$data,$data);
    }
    
}
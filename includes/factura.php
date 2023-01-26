<?php
/**
 * Clase que mantiene la tabla factura
 *
 * @autor   Edgar Messia
 * @static  
 * @package     Valoriza2.Framework
 * @subpackage	FileSystem
 * @since	1.0
 */
class factura extends db implements crud {
    
    const tabla = "facturas";
    
    public function actualizar($id, $data){
        return db::update(self::tabla, $data, array("id" => $id));
    }

    public function borrar($id){
        return db::delete(self::tabla, array("id" => $id));
    }

    public function insertar($data){
        return db::insert(self::tabla, $data);
    }

    public function insertar_detalle_factura($data) {
        return db::insert("factura_detalle",$data);
    }
    
    public function listar(){
       return db::select("*", self::tabla);
    }
    
    public function ver($id){
        return db::select("*",self::tabla,array("id"=>$id));
    }

    public function borrarTodo() {
        return db::delete(self::tabla);
    }
    
    public function estadoDeCuenta($cod_admin, $inmueble, $apto) {
        $consulta = "select * from ".self::tabla.
                " where id_inmueble='$inmueble' and apto='$apto' 
                and cod_admin='$cod_admin' and (facturado-abonado)>0 
                order by periodo ASC";
        return db::query($consulta);
    }
    
    public function facturaPerteneceACliente($factura,$cedula,$cod_admin) {
        
        $query = "select propiedades.* from propiedades join 
                facturas on facturas.apto = propiedades.apto and 
                facturas.id_inmueble = propiedades.id_inmueble 
                where facturas.numero_factura='$factura' and facturas.cod_admin='$cod_admin'";
        $result = $this->dame_query($query);
        
        $pertenece = false;
        
        if ($result['suceed']==true && count($result['data'])>0) {
            $pertenece = $result['data'][0]['cedula']==$cedula;
        }

        if (!$pertenece) {
            
            $query =    "select cedula 
                        from propiedades p 
                        join historico_avisos_cobro h on p.id_inmueble = h.id_inmueble and p.apto = h.apto 
                        where p.cod_admin='$cod_admin' and h.numero_factura='$factura'";

            $result = $this->dame_query($query);
            if ($result['suceed'] && count($result['data'])>0) {
                $pertenece = $result['data'][0]['cedula']==$cedula;
            }
        }
        return $pertenece;
    }
    
    public function avisoExisteEnBaseDeDatos($aviso) {
        $aviso = str_replace(".pdf","",$aviso);
        $cod_admin = substr($aviso,-3);
        $aviso = substr($aviso,0, strlen($aviso)-3);
        //echo('<br>'.$aviso.'<br>');
        $query = "select numero_factura from facturas where numero_factura='$aviso' and cod_admin='$cod_admin'";
        $r=0;
        $result = $this->dame_query($query);
        if ($result['suceed']==true) {
            if (count($result['data'])>0) {
                $r=1;
            }
        } 
        if ($r==0) {
            $query = "select numero_factura from historico_avisos_cobro where numero_factura='$aviso' and cod_admin='$cod_admin'";
            $result = $this->dame_query($query);
            if ($result['suceed']==true) {
                if (count($result['data'])>0) {
                    $r=1;
                }
            }
        }
        return $r;       
    }
    
    public function numeroRecibosPendientesPropietario($cod_admin,$cedula) {
        
        $sql = "SELECT count(f.numero_factura) as cantidad FROM propiedades as p 
                JOIN facturas as f on f.id_inmueble = p.id_inmueble and 
                f.apto = p.apto WHERE p.cedula=$cedula 
                and f.cod_admin='$cod_admin' and p.cod_admin='$cod_admin' and f.facturado>f.abonado";
        $result = db::query($sql);
        return $result;
    }
    
    public static function obtenerAñosHistorico($inmueble,$propietario,$cod_admin) {
        $sql = "SELECT DISTINCT YEAR(periodo) ano FROM historico_avisos_cobro "
                . "where apto='$propietario' and id_inmueble='$inmueble' "
                . "and cod_admin='$cod_admin' order by 1 DESC";
        $result = db::query($sql);
        return $result;
    }
    
    public static function historicoAvisosDeCobro($inmueble,$propietario,$cod_admin,$año=null){
        $sql = "select * from historico_avisos_cobro where id_inmueble='$inmueble' "
                . "and apto='$propietario' and cod_admin='$cod_admin' ";
        if ($año!=null) {
            $sql.= " and periodo between '$año-01-01' and '$año-12-01' ";
        }
        $sql.= "ORDER BY periodo";
        $result = db::query($sql);
        return $result;
    }
}
<?php

/**
 * Clase que mantiene la tabla inmueble
 *
 * @autor   Edgar Messia
 * @static  
 * @package     Valoriza2.Framework
 * @subpackage	FileSystem
 * @since	1.0
 */

class inmueble extends db implements crud {

    const tabla = "inmueble";

    public function actualizar($id,$data){
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
    
    Public function listarInmueblesAutorizados($id_usuario){
        $acceso = db::select('id_inmueble','usuarios_acceso',Array("id_usuario"=>$id_usuario));
        if ($acceso['suceed'] && count($acceso['data'])==0) {
            return db::select("*", self::tabla);
        } else {
            $query = 'select * from inmueble where id in (select id_inmueble from usuarios_acceso where id_usuario='.$id_usuario.')';
            return db::dame_query($query);
        }
    }
    
    public function ver($id){
        return db::select("*",self::tabla,array("id"=>"'".$id."'"));
    }

    public function borrarTodo() {
        return db::delete(self::tabla);
    }
    
    public function estadoDeCuenta($cod_admin,$id) {
        return db::select("*","inmueble_deuda_confidencial",
                Array("id_inmueble" =>  $id,
                      "cod_admin"   =>  $cod_admin)
                );
    }
    
    public function listarInmueblesPorPropietario($cedula,$cod_admin) {
        $consulta = "select * from inmueble i join propiedades p on "
                . "i.id = p.id_inmueble where p.cedula=$cedula and i.cod_admin='$cod_admin'";
        return db::query($consulta);
        
    }
    
    public function insertarEstadoDeCuentaInmueble($data) {
        return db::insert("inmueble_deuda_confidencial", $data,"IGNORE");
    }
    
    public function movimientoFacturacionMensual($inmueble,$cod_admin) {
        $query = "select facturacion_mensual.*, inmueble.nombre_inmueble "
                . "from facturacion_mensual join inmueble "
                . "ON facturacion_mensual.id_inmueble = inmueble.id and "
                . "facturacion_mensual.cod_admin = inmueble.cod_admin "
                . "where id_inmueble='$inmueble' and facturacion_mensual.cod_admin='$cod_admin' "
                . "and periodo >= date_add((select max(periodo) "
                . "from facturacion_mensual where id_inmueble='$inmueble' and cod_admin='$cod_admin'), "
                . "INTERVAL -7 MONTH) order by periodo ASC";
        
        return db::query($query);
    }
    
    public function movimientoCobranzaMensual($inmueble,$cod_admin) {
        $query = "select cobranza_mensual.*,inmueble.nombre_inmueble "
                . "from cobranza_mensual join inmueble "
                . "on cobranza_mensual.id_inmueble = inmueble.id and "
                . "cobranza_mensual.cod_admin = inmueble.cod_admin "
                . "where id_inmueble='$inmueble' and cobranza_mensual.cod_admin='$cod_admin' "
                . "and periodo >= date_add((select max(periodo) "
                . "from facturacion_mensual where id_inmueble='$inmueble' and cod_admin='$cod_admin'), "
                . "INTERVAL -7 MONTH) order by periodo ASC";
        return db::query($query);
    }
    
    public function insertarFacturacionMensual($data) {
        $query = "insert into facturacion_mensual(id_inmueble,periodo,facturado,cod_admin) "
                . "VALUES('".$data['id_inmueble'].
                        "','".$data['periodo'].
                        "','".$data['facturado'].
                        "','".$data['cod_admin']."') "
                . "ON DUPLICATE KEY UPDATE facturado='".$data['facturado']."'";
        
        return db::exec_query($query);
    }
    
    public function insertarCobranzaMensual($data) {
        $query = "insert into cobranza_mensual(id_inmueble,periodo,monto,cod_admin) "
                . "VALUES('".$data['id_inmueble']."','".$data['periodo']."','".$data['monto']."','".$data['cod_admin']."') "
                . "ON DUPLICATE KEY UPDATE monto='".$data['monto']."'";
        
        return db::exec_query($query);
    }
    
    public function agregarCuentaInmueble($data) {
        return db::insert("inmueble_cuenta", $data,"IGNORE");
    }
    public function obtenerCuentasBancariasPorInmueble($cod_admin, $inmueble) {
        return db::select("*","inmueble_cuenta",Array(
            "cod_admin"     => $cod_admin,
            "id_inmueble"   => $inmueble
                ));
    }
    
    public function listarCuentasBancariasPorInmuebleBanco($inmueble,$banco){
        return db::select("*","inmueble_cuenta",Array("id_inmueble"=>$inmueble,"banco"=>$banco));
    }
    
    public function obtenerNombreBancoPorNumeroCuenta($inmueble,$numero_cuenta){
        return db::select("*","inmueble_cuenta",Array("id_inmueble"=>$inmueble,"numero_cuenta"=>$numero_cuenta));
    }
    
    public function obtenerNumeroCuentaPorInmuebleBanco($inmueble,$banco) {
        return db::select("*","inmueble_cuenta",Array("id_inmueble"=>$inmueble,"banco"=>$banco));
    }
    
    public function actualizarCuentaDeBanco($id,$cod_admin,$data){
        return db::update(self::tabla, $data, array("id" => $id,"cod_admin"=>$cod_admin));
    }
    
    public function verDatosInmueble($id,$cod_admin){
        return db::select("*",self::tabla,array("id"=>$id,"cod_admin"=>$cod_admin));
    }
    
    public function listarBancosActivos(){
        return db::select("*","bancos",Array("inactivo"=>0));
    }

    public function getLastBilledPeriod($cod_admin, $id_inmueble) {
        $filter = ["cod_admin" => $cod_admin,"id_inmueble" => $id_inmueble];
        return db::select("max(periodo) as periodo","facturacion_mensual",$filter);
    }

    public function getPropertyByAdmin($cod_admin) {
        return db::select("*",self::tabla,["cod_admin"=>"$cod_admin"]);
    }
}
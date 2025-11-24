<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of pago
 *
 * @author emessia
 */
class pago extends db implements crud {
    const tabla = "pagos";

    public function actualizar($id, $data){
        return $this->update(self::tabla, $data, array("id" => $id));
    }

    public function borrar($id){
        return $this->delete(self::tabla, array("id" => $id));
    }

    public function insertar($data){
        return $this->insert(self::tabla, $data);
    }
    
    public function insertarDetallePago($data) {
        return $this->insert("detalle_pago", $data);
    }
    
    public function listar(){
       return $this->select("*", self::tabla);
    }
    
    public function ver($id){
        return $this->select("*",self::tabla,array("id"=>$id));
    }
    
    public function borrarTodo() {
        return $this->delete(self::tabla);
    }
    
    public function pagoYaRegistrado($data) {
        return $this->select("*", self::tabla, Array(
            "tipo_pago"=>$data['tipo_pago'],
            "numero_documento"=>$data['numero_documento'],
            "numero_cuenta"=>$data['numero_cuenta'],
            "banco_destino"=>$data['banco_destino']));
    }
    
    public function listarPagosPendientes($cod_admin){
        return $this->select("distinct p.*", 
                "pagos p join pago_detalle d on p.id = d.id_pago", 
                Array("estatus"=>"p","p.cod_admin"=>$cod_admin),
                null,
                Array("d.id_inmueble"=>"ASC")
                );
    }
    
    public function listarPagosPendientes_enlinea() {
        $consulta = "select distinct p.*, ifnull(u.id_usuario,0) usuario_intranet from "
                . "pagos p join pago_detalle d on p.id = d.id_pago left join pago_usuario u on p.id = u.id_pago "
                . "where p.estatus='p' and p.id in (select id_pago from pago_en_linea) "
                . "order by d.id_inmueble ASC";
        return $this->query($consulta);
    }
    
    public function listarPagosPendientes_new($cod_admin){
        $consulta = "select distinct p.*, ifnull(u.id_usuario,0) usuario_intranet from "
                . "pagos p join pago_detalle d on p.id = d.id_pago left "
                . "join pago_usuario u on p.id = u.id_pago "
                . "where p.estatus='p' and p.cod_admin='$cod_admin' "
                . "order by d.id_inmueble ASC";
        //colocar esta línea en el criterio de seleccion si se activa
        //el pago por tarjeta de credito
        //and p.id not in (select id_pago from pago_en_linea) 
        return $this->query($consulta);
        
    }
    
    public function detallePagoPendiente($id_pago) {
        return $this ->select("*", "pago_detalle", Array("id_pago"=>$id_pago));
    }
    
    public function detallePagoEnLinea($id) {
        return $this ->select("*", "pago_en_linea", Array("id_pago"=>$id));
    }
    
    public function detalleTodosPagosPendientes() {
        $query = "select * from pago_detalle where id_pago in 
            (select id from pagos where estatus='p')";
        return $this->dame_query($query);
        
    }
    
    public function listarPagosProcesadosRangoFechas($desde = null, $hasta = null, $codigo_inmueble = null) {
        $query = "select distinct p.* from pagos p join pago_detalle d on p.id = d.id_pago where estatus = 'a' ";
        if (!$codigo_inmueble==null) {
            $query.= " and d.id_inmueble ='".$codigo_inmueble."' ";
        }
        if (!$desde == null) {
           $query .= "and p.fecha >='".$desde."' ";
        }
        if (!$hasta == null){
            $query .= "and p.fecha <='".$hasta." 23:59:59'";
        }
        $query.="order by d.id_inmueble ASC, p.id";
        return $this->dame_query($query);
    }
    
    public function procesarPagoEnLinea($data) {
        
        $resultado = Array();
        $this->exec_query("START TRANSACTION");
        try {
            
            $pago_detalle = Array();
            $pago_en_linea = Array();

            $pago_en_linea['tipo_tarjeta'] = $data['tipo_tarjeta'];
            $pago_en_linea['mes']          = $data['mes'];
            $pago_en_linea['year']         = $data['year'];
            $pago_en_linea['TypUsr']       = $data['TypUsr'];
            $pago_en_linea['CardHolderID'] = $data['CardHolderID'];
            $pago_en_linea['CardHolder']   = $data['CardHolder'];
            $pago_en_linea['CardNumber']   = substr($data['CardNumber'],-4);
            $pago_en_linea['TypUsr']       = $data['TypUsr'];
            $pago_en_linea['Code']         = $data['Code'];
            $pago_en_linea['Message']      = $data['Message'];
            $pago_en_linea['ip']           = $data['ip'];
            $pago_en_linea['voucher']      = $data['voucher'];
            $pago_en_linea['subtotal']     = Misc::format_mysql_number($data['subtotal']);
            $pago_en_linea['servicio']     = Misc::format_mysql_number($data['servicio']);
            $pago_en_linea['tasa']         = $data['tasa'];
            
            $pago_detalle['id_factura']    = $data['facturas'];
            $pago_detalle['id_inmueble']   = $data['id_inmueble'];
            $pago_detalle['apto']          = $data['id_apto'];
            $pago_detalle['monto']         = $data['montos'];
            $pago_detalle['periodo']       = $data['periodo'];
            $pago_detalle['id']            = $data['id'];

            if (isset($data['id_usuario'])) {
                $id_usuario = $data['id_usuario'];
                unset($data['id_usuario']);
            }
            unset($data['facturas'], $data['montos'], $data['id_inmueble'], $data['id_apto'],$data['periodo'],$data['id']);
            unset($data['tipo_tarjeta'], $data['ip'],$data['voucher'],$data['subtotal'],$data['servicio'],$data['tasa']);
            unset($data['accion'], $data['mes'],$data['year'],$data['CardHolder'],$data['CardHolderID']);
            unset($data['CardNumber'],$data['CVC'],$data['TypUsr'],$data['registrar'],$data['Code'],$data['Message']);

            $data['fecha_documento']    = Misc::format_mysql_date($data['fecha_documento']);
            $data['monto']              = Misc::format_mysql_number($data['monto']);
            $data['tipo_pago']          = strtoupper($data['tipo_pago']);
            $data['banco_destino']      = strtoupper($data['banco_destino']);

            if (isset($data['banco_origen'])) {
                $data['banco_origen'] = strtoupper($data['banco_origen']);
            }

            $data['numero_cuenta'] = str_replace(" ", "", $data['numero_cuenta']);

            $resultado['pago'] = $this->insertar($data);
            unset($resultado['pago']['query']);

            if ($resultado['pago']['suceed']) {

                $id_pago = $resultado['pago']['insert_id'];
                $resultado['pago_detalle'] = Array();

                if (isset($id_usuario)) {
                    $r = $this->insert("pago_usuario", Array("id_usuario"=>$id_usuario,"id_pago"=>$id_pago));
                }
                for ($i = 0; $i < count($pago_detalle['id']); $i++) {

                    $j = (int)$pago_detalle['id'][$i];

                    $resultado_detalle = $this->insert("pago_detalle", Array(
                        "id_pago"       => $id_pago, 
                        "id_factura"    => $pago_detalle['id_factura'][$j],
                        "id_inmueble"   => $pago_detalle['id_inmueble'][$j],
                        "id_apto"       => $pago_detalle['apto'][$j],
                        "monto"         => $pago_detalle['monto'][$j],
                        "periodo"       =>  Misc::format_mysql_date($pago_detalle['periodo'][$j])));
                    unset($resultado_detalle['query']);
                    array_push($resultado['pago_detalle'], $resultado_detalle);

                    $resultado['suceed'] = $resultado_detalle['suceed'];
                }    
                if (!$resultado_detalle['suceed']) {
                    $resultado['mensaje'] = "Ha ocurrido un error al procesar el pago";
                } else {
                    $this->exec_query("COMMIT");
                    $pago_en_linea['id_pago'] = $id_pago;
                    $this->insert("pago_en_linea",$pago_en_linea);
                    $resultado['suceed'] = true;
                    $resultado['mensaje'] = "Pago procesado con éxito!";
                    // se envia el email de confirmación
                    if ($pago_en_linea['Code'] === 201 ) {
                        $this->enviarEmailPagoEnLinea($id_pago);
                    }
                }

            } else {
                $resultado = $resultado['pago'];
                $this->exec_query("ROLLBACK");
                $resultado['mensaje'] = "Error mientras se procesaba el pago maestro.";
            }
        } catch (Exception $exc) {
            $resultado['suceed'] = false;
            $this->exec_query("ROLLBACK");
            $resultado['mensaje'] = "Error inesperado, contacte con el administrador del sistema";
            echo $exc->getTraceAsString();
        }
        return $resultado;
     }
     
    public function registrarPago($data) {
        
        $resultado = Array();
        $this->exec_query("START TRANSACTION");
        try {
            $res = $this->pagoYaRegistrado($data);
            
            if ($res['suceed'] && !count($res['data']) > 0 ) {
                
                $pago_detalle = Array();
                $pago_detalle['id_factura'] = $data['facturas'];
                $pago_detalle['id_inmueble'] = $data['id_inmueble'];
                $pago_detalle['apto'] = $data['id_apto'];
                $pago_detalle['monto'] = $data['montos'];
                $pago_detalle['periodo']= $data['periodo'];
                $pago_detalle['id']=$data['id'];
                if (isset($data['id_usuario'])) {
                    $id_usuario = $data['id_usuario'];
                    unset($data['id_usuario']);
                }
                unset($data['facturas'], $data['montos'], $data['id_inmueble'], $data['id_apto'],$data['periodo'],$data['id']);
                
                $data['fecha_documento'] = Misc::format_mysql_date($data['fecha_documento']);
                $data['monto'] = Misc::format_mysql_number($data['monto']);
                $data['tipo_pago'] = strtoupper($data['tipo_pago']);
                $data['banco_destino'] = strtoupper($data['banco_destino']);
                
                if (isset($data['banco_origen'])) {
                    $data['banco_origen'] = strtoupper($data['banco_origen']);
                }
                
                $data['numero_cuenta'] = str_replace(" ", "", $data['numero_cuenta']);
                
                $resultado['pago'] = $this->insertar($data);
                unset($resultado['pago']['query']);
                if ($resultado['pago']['suceed']) {

                    $id_pago = $resultado['pago']['insert_id'];
                    $resultado['pago_detalle'] = Array();
                    
                    if (isset($id_usuario)) {
                        $r = $this->insert("pago_usuario", Array("id_usuario"=>$id_usuario,"id_pago"=>$id_pago));
                    }
                    for ($i = 0; $i < count($pago_detalle['id']); $i++) {
                        
                        $j = (int)$pago_detalle['id'][$i];
                        
                        $resultado_detalle = $this->insert("pago_detalle", Array(
                            "id_pago" => $id_pago, 
                            "id_factura" => $pago_detalle['id_factura'][$j],
                            "id_inmueble" => $pago_detalle['id_inmueble'][$j],
                            "id_apto" => $pago_detalle['apto'][$j],
                            "monto" => $pago_detalle['monto'][$j],
                            "periodo"=>  Misc::format_mysql_date($pago_detalle['periodo'][$j])));
                        unset($resultado_detalle['query']);
                        array_push($resultado['pago_detalle'], $resultado_detalle);

                        $resultado['suceed'] = $resultado_detalle['suceed'];
                    }    
                    if (!$resultado_detalle['suceed']) {
                        $resultado['mensaje'] = "Ha ocurrido un error al procesar el pago";
                    } else {
                        $this->exec_query("COMMIT");
                        $resultado['suceed'] = true;
                        $resultado['mensaje'] = "Pago procesado con éxito!";
                        // se envia el email de confirmación
                        
                        $r = $this->enviarEmailPagoRegistrado($data, $pago_detalle, $id_pago);
                        if ($r=="") {
                            $this->actualizar($id_pago, Array("enviado"=>1));
                        } else {
                            $resultado['envio']=$r;
                        }

                    }
                    
                } else {
                    $resultado = $resultado['pago'];
                    $this->exec_query("ROLLBACK");
                    $resultado['mensaje'] = "Error mientras se procesaba el pago maestro.";
                }
            } else {
                $resultado['suceed'] = false;
                $resultado['mensaje'] = "Estimado propietario:\n\nEste pago ya fue registrado con anterioridad, en fecha ".  Misc::date_format($res['data'][0]['fecha'].".");
                if ($res['data'][0]['estatus']=='p') {
                    $resultado['mensaje'].= "\nActualmente está pendiente de ser aplicado a su cuenta.";
                }
                if ($res['data'][0]['estatus']=='a') {
                    $resultado['mensaje'].= "\nEL pago ya fue aplicado a su cuenta.";
                }
                if ($res['data'][0]['estatus']=='a') {
                    $resultado['mensaje'].= "\nEl pago ya fue rechazado. Si considera que es un error nuestro, escríbanos a soporte@v2.web.ve";
                }
            }
        } catch (Exception $exc) {
            $resultado['suceed'] = false;
            $this->exec_query("ROLLBACK");
            $resultado['mensaje'] = "Error inesperado, contacte con el administrador del sistema";
            echo $exc->getTraceAsString();
        }
        return $resultado;
     }
    
    public function procesarPago($id,$estatus,$recibo=null) {
        
        if (!$recibo==null) {
            $this->insert("pago_recibo", array("id_pago"=>$id,"n_recibo"=>$recibo));
            return false;
        }
        $r = $this->actualizar($id, array("estatus"=>$estatus));
        if ($r['suceed']) {
            return "Ok";
        }        
        return "Falló";
    }
    
    public function enviarEmailPagoRegistrado($data, $pago_detalle, $id_pago) {
        $ini = parse_ini_file('emails.ini');
        $inmueble       = new inmueble();
        $codigo_apto    = $pago_detalle['apto'][0];
        $descripcion    = $data['cod_admin']=='004'? 'Cuota Mensual':'Pago de Condominio';
        if (isset($_SESSION['usuario']['directorio'])) {
            $prop = new propietario();
            $datos_propietario = $prop->ver($_SESSION['usuario']['id']);
            $propietario = $datos_propietario[0]['nombre'];
        } else {
            $propietario = $_SESSION['usuario']['nombre'];
        }

        switch (strtoupper($data['tipo_pago'])) {
            case 'D':
                $forma_pago = 'DEPOSITO';
                break;
            case 'TDD':
                $forma_pago = 'T.DEBITO';
                break;
            case 'TDC':
                $forma_pago = 'T.CREDITO';
                break;
            default:
                $forma_pago = 'TRANSFERENCIA';
                break;
        }

        // datos del inmueble
        $datos_inmueble = $inmueble->verDatosInmueble(
                            $pago_detalle['id_inmueble'][0],
                            $_SESSION['usuario']['cod_admin']);
        
        $nombre_inmueble    = '';
        $rif                = '';
        $moneda            = 'Bs';
        if ($datos_inmueble['suceed'] && count($datos_inmueble['data']) > 0) {
            
            $nombre_inmueble    = $datos_inmueble['data'][0]['nombre_inmueble'];
            $rif                = $datos_inmueble['data'][0]['RIF'];
            $moneda             = $datos_inmueble['data'][0]['moneda'];
        }
        
        $mensaje = sprintf($ini['CUERPO_MENSAJE_PAGO_RECEPCION_CONFIRMACION'], 
                $propietario,
                $forma_pago,
                $data['numero_documento'],
                $data['banco_destino'],
                $data['numero_cuenta'],
                $moneda,
                Misc::number_format($data['monto']),
                Misc::date_format($data['fecha_documento']),
                $data['email'], $data['telefono'],
                $propietario,
                $id_pago,
                date("d/m/Y")
        );
        $mensaje .= $ini['PIE_MENSAJE_PAGO'];
        /*
        ob_start();
        ?>
                <page format="135x215" orientation="L">
                <div style="rotate: 90; position: absolute; width: 100mm; height: 4mm; left: 212mm; top: 0; font-style: italic; font-weight: normal; text-align: center; font-size: 2.5mm;">
                Recibo electrónico del servicio de pago web de v2.web.ve
                </div>
                <div style="width: 90%; margin-left:50px">
                <table style="width: 99%;" cellspacing="1mm" cellpadding="0"  >
                <tr>
                    <td style="width: 100%;">
                        <div class="zone" style="height: 26mm;position: relative;font-size: 5mm;">
                            <img src="../../assets/images/_smarty/logo_app.png" alt="logo">
                            <div style="position: absolute; top: 16mm; right:3mm; text-align: right; font-size: 2.5mm;">
                                Fecha de Impresión: <?php echo date('d/m/Y H:i:s'); ?><br>
                                <span style="font-size: 4mm;margin-top: 8px"><b>Comprobante Nº <span style="color: RGB(255, 0, 0)"><?php echo sprintf('%08d', $id_pago); ?></span></b></span>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="zone" style="height: 60mm;text-align: justify; font-size: 3.5mm; padding: 14px;">
                            <p style="line-height: 6mm">
                            Hemos recibido de <b><?php echo $propietario ?></b>, propietario del inmueble
                            <b><?php echo $codigo_apto ?></b> en <b><?php echo $nombre_inmueble . ", RIF.: " . $rif ?></b>, la cantidad de 
                            <b><?php echo $moneda.Misc::number_format($data['monto']) ?></b>,
                            correspondientes al siguiente detalle:<br><br>
                            </p>
                            <table style="width: 500px">
        <?php
        $total = 0;
        for ($i = 0; $i < count($pago_detalle['id']); $i++) {
            $j = (int) $pago_detalle['id'][$i];
            $total += $pago_detalle['monto'][$j];
        ?>
                            <tr>
                                <td style="width: 75%">
                                    <?php echo $descripcion.' ('.Misc::date_periodo_format($pago_detalle['periodo'][$j]).')' ?>
                                </td>
                                <td style="width:25%; text-align: right"><?php echo $moneda.Misc::number_format($pago_detalle['monto'][$j]) ?></td>
                            </tr>
        <?php } ?>
                            <!--tr>
                                <td style="text-align: right"><b>Subtotal:</b></td>
                                <td style="text-align: right; border-top: 1px solid #000"><b><?php //echo Misc::number_format($total)  ?></b></td>
                            </tr-->
                            <tr>
                                <td style="text-align: right"><b>Total:</b></td>
                                <td style="text-align: right; border-top: 1px solid #000"><b><?php echo $moneda.Misc::number_format($total) ?></b></td>
                            </tr>
                            </table>
                        </div>
                    </td>
                </tr>
                </table>
                </div>
                <div style="position: absolute;top: 378; font-weight: normal; font-size: 3mm; left:70px">
                <b>Forma de Pago:</b><br><br>
                <?php echo $forma_pago." Referencia: ".$data['numero_documento']
                        ."  Fecha: ".Misc::date_format($data['fecha_documento'])." "
                        .$data['banco_destino']
                        ." Monto: ".$moneda.Misc::number_format($data['monto']); ?>
                </div>
                </page>
        <?php
        $content = ob_get_clean();
        */
        // convert to PDF
        // require_once('../../includes/html2pdf/html2pdf.class.php');
        // try         
        // {
            /*
            $html2pdf = new HTML2PDF('P', 'Letter', 'fr', true, 'UTF-8', array(0, 10, 0, 0));
            $html2pdf->setDefaultFont("Helvetica");
            // recibo
            $html2pdf->writeHTML($content, isset($_GET['vuehtml']));
            $archivo = $html2pdf->Output('', 'S');
            $voucher = Array("Recibo_electronico.pdf" => $archivo);
            */
            $voucher = null;
            $mail = new mailto(SMTP);
            $mail->enviar_email("Pago de Condominio", $mensaje, '', $data['email'], "", null, null, $voucher);
            //$archivo = '';
        // } 
        // catch (HTML2PDF_exception $e) {
        //     echo "Error PDF :" . $e;
        //     exit;
        // }
    }

    public function reenviarEmailPagoRegistrado($id) {
        $data = $this->ver($id);
        
        if ($data['suceed'] && count($data['data'])>0) {
            
            $descripcion = $data['data'][0]['cod_admin']=='004'? 'Cuota Mensual':'Pago de Condominio';
            $ini = parse_ini_file('emails.ini');
            if (isset($_SESSION['usuario']['nombre'])) {
                $propietario =  $_SESSION['usuario']['nombre'];
            } else {
                $propietario = '';                
            }
            $total      = 0;
            
            $monto = $data['data'][0]['monto'];
            $pago_detalle = $this->detallePagoPendiente($id);

            if ($pago_detalle['suceed'] && count($pago_detalle['data'])>0) {
                $inmueble = new inmueble();
                $codigo_apto = $pago_detalle['data'][0]['id_apto'];
                $codigo_inmueble = $pago_detalle['data'][0]['id_inmueble'];
                $datos_inmueble = $inmueble->verDatosInmueble($codigo_inmueble,
                                            $data['data'][0]['cod_admin']);

                $nombre_inmueble    = '';
                $rif                = '';
                $moneda             = 'Bs';
                if ($propietario=='') {
                    $prop = new propietario();
                    $datos_propietario = $prop->obtenerPropietario($codigo_inmueble, $codigo_apto);

                    $propietario = $datos_propietario[0]['nombre'];
                }
                if ($datos_inmueble['suceed'] && count($datos_inmueble['data'])>0) {
                    $nombre_inmueble = $datos_inmueble['data'][0]['nombre_inmueble'];
                    $rif = $datos_inmueble['data'][0]['RIF'];
                    $moneda = $datos_inmueble['data'][0]['moneda'];
                }
            } else {
                die('No se pudo generar el comprobante. No se encuentra el detalle del pago');
            }
            
            switch (strtoupper($data['data'][0]['tipo_pago'])) {
                case 'D':
                    $forma_pago = 'DEPOSITO';
                    break;
                case 'TDD':
                    $forma_pago = 'T.DEBITO';
                    break;
                case 'TDC':
                    $forma_pago ='T.CREDITO';
                    break;
                default:
                    $forma_pago = 'TRANSFERENCIA';
                    break;
            }
            
            $mensaje = sprintf($ini['CUERPO_MENSAJE_PAGO_RECEPCION_CONFIRMACION'], 
                    $propietario,
                    $forma_pago,
                    $data['data'][0]['numero_documento'],
                    $data['data'][0]['banco_destino'],
                    $data['data'][0]['numero_cuenta'],
                    $moneda,
                    Misc::number_format($data['data'][0]['monto']),
                    Misc::date_format($data['data'][0]['fecha_documento']),
                    $data['data'][0]['email'],$data['data'][0]['telefono'],
                    $propietario,
                    $id,
                    Misc::date_format($data['data'][0]['fecha']));
                    $mensaje.=$ini['PIE_MENSAJE_PAGO'];
                   
            
                $voucher = null;
                $mail = new mailto(SMTP);
                $r = $mail->enviar_email("Pago de Condominio", $mensaje, '', $data['data'][0]['email'], "",null,null,$voucher);

                if ($r=="") {
                    $this->actualizar($id, Array("enviado"=>1));
                    echo "Email enviado a ".$data['data'][0]['email']." Ok!";
                } else {
                    echo($r);
                }
            
        } else {
            echo 'No se consigue la informaci&oacute;n del pago ID: '.$id;
        }
    }
    
    public function enviarEmailPagoEnLinea($id) {
        $data = $this->ver($id);
        $html = '';
        if ($data['suceed'] == TRUE && count($data['data'])>0) {
            $ini = parse_ini_file('emails.ini');
            if (isset($_SESSION['usuario']['nombre'])) {
                $propietario =  $_SESSION['usuario']['nombre'];
            } else {
                $propietario = '';                
            }
            
            $archivo    = '';
            $total      = 0;
            $servicio   = 0;
            
            $pago = $this->ver($id);
            
            if ($pago['suceed'] && count($pago['data'])>0) {
                $monto = $pago['data'][0]['monto'];
                $pago_detalle = $this->detallePagoPendiente($id);
                
                if ($pago_detalle['suceed'] && count($pago_detalle['data']) > 0) {
                    $inmueble = new inmueble();
                    $codigo_apto = $pago_detalle['data'][0]['id_apto'];
                    $codigo_inmueble = $pago_detalle['data'][0]['id_inmueble'];
                    $datos_inmueble = $inmueble->verDatosInmueble($codigo_inmueble, $data['data'][0]['cod_admin']);
                    $nombre_inmueble = "";
                    if ($propietario=='') {
                        $prop = new propietario();
                        $datos_propietario = $prop->obtenerPropietario($codigo_inmueble, $codigo_apto);
                        
                        $propietario = $datos_propietario[0]['nombre'];
                    }
                    if ($datos_inmueble['suceed'] && count($datos_inmueble['data'])>0) {
                        $nombre_inmueble = $datos_inmueble['data'][0]['nombre_inmueble'];
                        $rif = $datos_inmueble['data'][0]['RIF'];
                    }
                } else {
                    die('No se pudo generar el comprobante. No se encuentra el detalle del pago');
                }
                $pago_en_linea = $this->detallePagoEnLinea($id);
                if ($pago_en_linea['suceed'] && count($pago_en_linea['data'])>0) {
                    $html       = html_entity_decode($pago_en_linea['data'][0]['voucher']);
                    $servicio   = $pago_en_linea['data'][0]['servicio'];
                    $ip         = $pago_en_linea['data'][0]['ip'];
                } else {
                    die('No se pudo generar el comprobante. No se encuentra el detalle del pago en l&iacute;nea');
                }

            } else {
                die('No se pudo generar el comprobante. No se encuenta la inforamci&oacute;n del pago');
            }
           
            
            switch (strtoupper($data['data'][0]['tipo_pago'])) {
                case 'D':
                    $forma_pago = 'DEPOSITO';
                    break;
                case 'TDD':
                    $forma_pago = 'T.DEBITO';
                    break;
                case 'TDC':
                    $forma_pago ='T.CREDITO';
                    break;
                
                default:
                    $forma_pago = 'TRANSFERENCIA';
                    break;
            }
            $mensaje = sprintf($ini['CUERPO_MENSAJE_PAGO_RECEPCION_CONFIRMACION'], 
                    $propietario,
                    $forma_pago,
                    $data['data'][0]['numero_documento'],
                    $data['data'][0]['banco_destino'],
                    $data['data'][0]['numero_cuenta'],
                    Misc::number_format($data['data'][0]['monto']),
                    Misc::date_format($data['data'][0]['fecha_documento']),
                    $data['data'][0]['email'],$data['data'][0]['telefono'],
                    $propietario,
                    $id,
                    date("d/m/Y"));
            
                    $mensaje.=$ini['PIE_MENSAJE_PAGO'];
                   
            
                $mail = new mailto(SMTP);
                
                $r = $mail->enviar_email(
                        "Pago electrónico web", 
                        $mensaje, '', 
                        $data['data'][0]['email'], 
                        "",
                        null,
                        null,
                        null);

                if ($r=="") {
                    $this->actualizar($id, Array("enviado"=>1));
                } else {
                    echo($r);
                }
            
        } else {
            echo 'No se consigue la informaci&oacute;n del pago ID: '.$id;
        }
    }
    
    public function enviarEmailPagoProcesado($id,$estatus,$data) {
        $ini = parse_ini_file('emails.ini');
        $mail = new mailto(SMTP);
        
        $s = strtoupper($estatus)=='A' ? "CONFIRMACION" : "RECHAZO";
        $m = strtoupper($estatus)=='A' ? "CONFIRMACION" : "RECHAZO";
        $destinatario = $data['email'];
        $subject = sprintf($ini['ASUNTO_MENSAJE_PAGO_PROCESADO_'.$s]);
        $mensaje = sprintf($ini['CUERPO_MENSAJE_PAGO_PROCESADO_'.$m],
                $data['administradora'],
                $data['forma_pago'],
                $data['numero_documento'],
                $data['banco'],
                $data['cuenta'],
                Misc::number_format($data['monto']),
                Misc::date_format($data['fecha']),
                $ini['CUENTA_PAGOS']
                );
        
        $can = array();
        
        if ($estatus == 'A') {

            if (RECIBO_GENERAL==1) {
                $r = $this->detallePagoReciboGeneral($id);
            } else {
                $r = $this->detallePagoPendiente($id);
            }
            
            if ($r['suceed'] == true) {
                if (count($r['data']) > 0) {
                    $n = 0;
                    foreach ($r['data'] as $factura) {
                        
                        $factura = '../../cancelacion.gastos/' . $factura["id_factura"] . '.pdf';
                        
                        $factura = realpath($factura);
                        
                        if ($factura != "") {
                            $n = $n + 1;
                            $can[] = $factura;
                        }
                        
                    }
                    $mensaje.= "Hemos adjuntado " . $n . " factura(s).";
                    if ($n < count($r['data'])) {
                        $mensaje . -"<br>Falta(ron) factura(s) por adjuntar.";
                    }
                }
            }
        }
        
        $mensaje.= $ini['PIE_MENSAJE_PAGO'];
        
        $r = $mail->enviar_email($subject, $mensaje, "", $destinatario,"",$can);
        
        if ($r=="") {
            $this->actualizar($id, Array("confirmacion"=>1));
            return "Ok";
        }
        return "Falló";
        
    }
    
    public function listarPagosProcesados($inmueble, $apartamento, $cod_admin, $n) {
         if (RECIBO_GENERAL === 1) {
            $consulta = "select p.*,d.n_recibo from pagos p join pago_recibo d on 
            p.id = d.id_pago where p.id in (select id_pago from pago_detalle d where d.id_inmueble='".$inmueble."' and id_apto='".$apartamento."' and p.estatus='a') order by 1 desc, n_recibo DESC LIMIT 0,$n ";
        
            return db::query($consulta);

        } else {

            $r = $this->select("*,CONCAT('01-', periodo ) AS fPeriodo", 
            "cancelacion_gastos",
            [ "id_inmueble" => $inmueble, "id_apto" => $apartamento, "cod_admin" => $cod_admin ],
            null,
            Array("fecha_movimiento"=>"DESC","STR_TO_DATE(CONCAT('01-', periodo), '%d-%m-%Y')"=>"DESC"));
            
            return $r;

        }
        
    }
    
    public static function detalleCancelacionDeGastos($id_factura) {
         $consulta = "select f.*, d.*, i.nombre_inmueble, pro.nombre, p.alicuota
            from facturas f join factura_detalle d on f.numero_factura =
            d.id_factura join inmueble i on i.id = f.id_inmueble 
            JOIN propiedades p ON p.id_inmueble = i.id
            AND p.apto = f.apto
            JOIN propietarios pro ON pro.cedula = p.cedula
            where f.numero_factura ='".$id_factura."' order by d.codigo_gasto ";
        
        return db::query($consulta);
    }
    
    public function numeroRecibosCanceladosPorPropitario($cod_admin,$cedula) {
        $sql = "select c.* from propiedades as p join cancelacion_gastos as c
        on c.id_inmueble = p.id_inmueble and c.id_apto = p.apto 
        where p.cedula=$cedula and c.cod_admin='$cod_admin' and p.cod_admin='$cod_admin'";
        
        return db::query($sql);
    }
    
    public static function facturaPendientePorProcesar($periodo,$inmueble,$apto) {
        $sql = "SELECT p.* FROM pagos p join pago_detalle pd on p.id = pd.id_pago where p.estatus='p' and pd.periodo='".$periodo."' and id_inmueble='".$inmueble."' and id_apto='".$apto."'";
        
        $r = db::query($sql);
        
        return $r;        
    }
    
    public function detallePagoReciboGeneral($id_pago) {
        $consulta = "select id_pago,n_recibo,n_recibo as id_factura from pago_recibo where id_pago=$id_pago";
        return $this->query($consulta);
    }
    
    public function listadoPagosRegistradosPorUsuario($id,$fecha,$start=0, $limit=10,$perfil=null) {
        if ($start < 0) {   
            $start = 0;
        }
        $limit = $start + $limit;
        if ($limit < 1) {
            $limit = 1;
        }	
        if ($perfil=='ADMIN') {
            $sql = "select distinct p.*,pd.id_inmueble, pd.id_apto from pagos p join pago_usuario u on p.id = u.id_pago 
                join pago_detalle pd on p.id = pd.id_pago
                where date(p.fecha)='".$fecha ."' order by fecha ASC limit $start,$limit";
            
        } else {
            $sql = "select distinct p.*,pd.id_inmueble, pd.id_apto from pagos p join pago_usuario u on p.id = u.id_pago 
                join pago_detalle pd on p.id = pd.id_pago
                where u.id_usuario =".$id." and date(p.fecha)='".$fecha. "' order by fecha ASC limit $start,$limit";
        }
        return db::dame_query($sql);
        
    }
    
    public function eliminarPago($id) {
        // eliminar detalle
        $r = $this->delete("pago_detalle", array("id_pago"=>$id));
        if (!$r['suceed']) {
            $r['mensaje']='Ocurrió un error al eliminar el detalle de la transacción';
            return $r;
        }
        // eliminar pago usuario
        $r = $this->delete("pago_usuario", array("id_pago"=>$id));
        if (!$r['suceed']) {
            $r['mensaje']='Ocurrió un error al eliminar la información del usuario';
            return $r;
        }
        // eliminar pag maestro
        $this->borrar($id);
        if (!$r['suceed']) {
            $r['mensaje']='Ocurrió un error al eliminar el detalle de la transacción';
        } else {
            $r['mensaje']='Transacción eliminada con éxito!';
        }
        return $r;
    }
    
    public function insertarMovimientoCaja($data) {
        return db::insert("movimiento_caja",$data);
    }
    
    public function estadoDeCuenta($cod_admin,$inmueble, $apto) {
        $consulta = "select * from movimiento_caja "
                ."where id_inmueble='$inmueble' and id_apto='$apto' "
                . "and cod_admin='$cod_admin' order by fecha_movimiento DESC";
        
        return db::query($consulta);
    }
    
    public function actualizarFactura($inmueble,$apto,$factura,$monto) {
        $monto = misc::format_mysql_number($monto);
        $factura = "01/".$factura;
        $factura = misc::format_mysql_date($factura);
        
        $sql = 'update facturas set abonado = abonado + '.$monto.' where id_inmueble=\''.$inmueble.'\' and apto=\''.$apto.'\' and periodo=\''.$factura.'\'' ;
        
        return $this->exec_query($sql);
    }
    
    public static function listarPagosProcesadosWeb() {
        $consulta = "select d.id_factura,d.id_inmueble,d.id_apto from pagos p join pago_detalle d on p.id = d.id_pago "
                . "where p.estatus='a' and (left(d.id_factura,1)='0' or left(d.id_factura,1)='1') and d.id_inmueble in "
                . "(select id from inmueble) order by d.id_factura";
        return db::query($consulta);
    }

    public function insertarCancelacionDeGastos($data) {
        return db::insert("cancelacion_gastos",$data);
    }

    public function cancelacionExisteEnBaseDeDatos($cancelacion) {
        // Normalizar nombre de archivo y validar formato esperado
        $cancelacion = trim($cancelacion);
        $cancelacion = str_ireplace('.pdf', '', $cancelacion);

        // Si la cadena es muy corta no tiene formato esperado
        if (strlen($cancelacion) <= 10) {
            return 0;
        }

        // Extraer los últimos 3 caracteres como cod_admin y el resto como numero_factura
        $cod_admin = substr($cancelacion, -3);
        $numero_factura = substr($cancelacion, 0, -3);

        // Sanea para evitar inyección y errores en la consulta
        $cod_admin_esc = $this->mysqli->real_escape_string($cod_admin);
        $numero_factura_esc = $this->mysqli->real_escape_string($numero_factura);

        // Consulta: buscar coincidencia exacta en las columnas (mejor que CONCAT en WHERE)
        $query = "SELECT 1 AS existe FROM cancelacion_gastos "
               . "WHERE numero_factura = '" . $numero_factura_esc . "' "
               . "AND cod_admin = '" . $cod_admin_esc . "' "
               . "UNION "
               . "SELECT 1 AS existe FROM movimiento_caja "
               . "WHERE numero_recibo = '" . $numero_factura_esc . "' "
                . "AND cod_admin = '" . $cod_admin_esc . "' LIMIT 1";
        
        $result = $this->dame_query($query);

        return $result['suceed'] && isset($result['data']) && count($result['data']) > 0;
    }

    public function listarPagosEmailRegisroNoEnviado() {
        $opciones = [
                'estatus'=>'p',
                'enviado'=> 0
            ];
        return $this->select("*", self::tabla, $opciones);
    }

    public function listarRecibosRegistrados() {
        $recibos = array();
        $facturas = array();

        $result = $this->select("numero_recibo", "movimiento_caja");
        
        if ($result['suceed'] && count($result['data'])>0) {
            $recibos = array_column($result['data'], 'numero_recibo');
        }
        
        $result = $this->select("*", "cancelacion_gastos");
        if ($result['suceed'] && count($result['data'])>0) {
            $facturas = array_column($result['data'], 'numero_factura');
        }
        return array_merge($recibos, $facturas);
    }
}
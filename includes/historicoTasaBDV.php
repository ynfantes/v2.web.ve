<?php
class historicoTasaBDV extends db implements crud {

    const tabla = "historial_tasa_bcv";

    public function insertar($data)
    {
        return db::insert(self::tabla, $data);
    }

    public function borrar($id)
    {
        
    }

    public function ver($id)
    {
        return db::select('*',self::tabla,['id' => $id]);
    }

    public function actualizar($id, $data)
    {
        return db::update(self::tabla, $data, ["id" => $id]);
    }

    public function listar()
    {
        return db::select('*',self::tabla,null,null,['id' => 'DESC']);
    }

    public function borrarTodo()
    {
        
    }

    public function recordPriceDoolar($datos) {
        return db::insertUpdate(self::tabla, $datos, $datos);
    }

    public function getPriceDollar() {
        $result = [];

        $history = db::select('*',self::tabla,null,null,['fecha' => 'DESC'],null,3);
        
        if ($history['suceed'] && !empty($history['data'])) {
            
            $prices = $history['data'];
            $current_price = $prices[0]['precio'];
            $previous_price = $prices[1]['precio'];
            $price_date = $prices[0]['fecha'];
            $date_update = $prices[0]['fecha_actualizacion'];
            $variation = (($current_price - $previous_price) / $previous_price) * 100;
            $change = $current_price - $previous_price;
            
            $current_price = number_format($current_price,2,",",".");
            $previous_price = number_format($previous_price,2,",",".");
            $symbol = $variation > 0 ? '▲': $variation = 0 ? '▶': '▼';
            $variation = floatval($variation);
            $variation = number_format($variation, 2,",",".");
            $change = number_format($change, 2,",",".");

            $result = [
                'prev'    => $previous_price,
                'price'   => $current_price,
                'unit'    => 'Bs/USD',
                'percent' => $variation,
                'symbol'  => $symbol,
                'change'  => $change,
                'fecha'   => $price_date,
                'updated' => $date_update
            ];
        }

        return $result;
    }
}
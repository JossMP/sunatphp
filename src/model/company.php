<?php

namespace jossmp\sunat\model;

class company extends \jossmp\response\obj
{
    private $id_sunat               = NULL;
    public $ruc                     = NULL;
    public $razon_social            = NULL;
    public $direccion               = NULL;

    public $departamento            = NULL;
    public $provincia               = NULL;
    public $distrito                = NULL;

    public $estado                  = NULL;
    public $condicion               = NULL;
    public $tipo                    = NULL;
    public $nombre_comercial        = NULL;
    public $fecha_inscripcion       = NULL;
    public $sistema_emision         = NULL;
    public $actividad_exterior      = NULL;
    public $sistema_contabilidad    = NULL;
    public $comprobante_impreso     = NULL;
    public $comprobante_electronico = NULL;
    public $ple                     = NULL;
    public $inicio_actividades      = NULL;
    public $actividad_economica     = NULL;
    public $oficio                  = NULL;
    public $ubigeo                  = NULL;
    public $dir_tipo_via            = NULL;
    public $dir_cod_zona            = NULL;
    public $dir_tipo_zona           = NULL;
    public $dir_num                 = NULL;
    public $dir_interior            = NULL;
    public $dir_lote                = NULL;
    public $dir_dpto                = NULL;
    public $dir_manzana             = NULL;
    public $dir_km                  = NULL;
    public $dir_nomb_via            = NULL;
    public $emision_electronica     = NULL;
    public $telefono                = NULL;

    public $establecimientos        = NULL;
    public $cantidad_trabajadores   = NULL;
    public $representantes_legales  = NULL;
    public $deuda_coactiva          = NULL;


    public $fecha_registro          = NULL;
    public $fecha_actualizacion     = NULL;
    public $completo                = NULL;

    public $prefijo                 = NULL;

    private function format_fecha($date, $format_in = NULL)
    {
        if ($format_in == NULL) {
            return $date;
        }
        $date = \DateTime::createFromFormat($format_in, $date);

        if ($date === FALSE) {
            return NULL;
        }
        return $date->format("Y-m-d");
    }

    /**
     * load_data
     *
     * @param mixed $obj
     * @return void
     */
    public function load_data($obj)
    {
        $obj = (is_array($obj)) ? ((object) $obj) : $obj;
        foreach ($this as $ind => $val) {
            if (isset($obj->{$ind})) {
                //$this->{$ind} = $obj->{$ind};
                $this->{'set_' . $ind}($obj->{$ind});
            }
        }
        return $this;
    }

    // functions GET | SET
    public function get_ruc()
    {
        return $this->ruc;
    }
    public function set_ruc($value)
    {
        $this->ruc = $value;
        $this->set_prefijo(substr($this->ruc, 0, 6));
    }

    public function get_razon_social()
    {
        return $this->razon_social;
    }
    public function set_razon_social($value)
    {
        $this->razon_social = $value;
    }

    public function get_direccion()
    {
        return $this->direccion;
    }
    public function set_direccion($value)
    {
        $this->direccion = $value;
    }

    public function get_departamento()
    {
        return $this->departamento;
    }
    public function set_departamento($value)
    {
        $this->departamento = $value;
    }

    public function get_provincia()
    {
        return $this->provincia;
    }
    public function set_provincia($value)
    {
        $this->provincia = $value;
    }

    public function get_distrito()
    {
        return $this->distrito;
    }
    public function set_distrito($value)
    {
        $this->distrito = $value;
    }

    public function get_estado()
    {
        return $this->estado;
    }
    public function set_estado($value)
    {
        $this->estado = $value;
    }

    public function get_condicion()
    {
        return $this->condicion;
    }
    public function set_condicion($value)
    {
        $this->condicion = $value;
    }

    public function get_tipo()
    {
        return $this->tipo;
    }
    public function set_tipo($value)
    {
        $this->tipo = $value;
    }


    public function get_nombre_comercial()
    {
        return $this->nombre_comercial;
    }
    public function set_nombre_comercial($value)
    {
        $this->nombre_comercial = $value;
    }

    public function get_fecha_inscripcion()
    {
        return $this->fecha_inscripcion;
    }
    public function set_fecha_inscripcion($value, $format_in = "d/m/Y")
    {
        $this->fecha_inscripcion = $this->format_fecha($value, $format_in);
    }

    public function get_sistema_emision()
    {
        return $this->sistema_emision;
    }
    public function set_sistema_emision($value)
    {
        $this->sistema_emision = $value;
    }

    public function get_actividad_exterior()
    {
        return $this->actividad_exterior;
    }
    public function set_actividad_exterior($value)
    {
        $this->actividad_exterior = $value;
    }

    public function get_sistema_contabilidad()
    {
        return $this->sistema_contabilidad;
    }
    public function set_sistema_contabilidad($value)
    {
        $this->sistema_contabilidad = $value;
    }

    public function get_comprobante_impreso()
    {
        return $this->comprobante_impreso;
    }
    public function set_comprobante_impreso($value)
    {
        $this->comprobante_impreso = $value;
    }


    public function get_comprobante_electronico()
    {
        return $this->comprobante_electronico;
    }
    public function set_comprobante_electronico($value)
    {
        $this->comprobante_electronico = $value;
    }

    public function get_ple()
    {
        return $this->ple;
    }
    public function set_ple($value)
    {
        $this->ple = $value;
    }

    public function get_inicio_actividades()
    {
        return $this->inicio_actividades;
    }
    public function set_inicio_actividades($value, $format_in = "d/m/Y")
    {
        $this->inicio_actividades = $this->format_fecha($value, $format_in);
    }

    public function get_actividad_economica()
    {
        return $this->actividad_economica;
    }
    public function set_actividad_economica($value)
    {
        $this->actividad_economica = $value;
    }

    public function get_establecimientos()
    {
        return $this->establecimientos;
    }
    public function set_establecimientos($value)
    {
        $this->establecimientos = $value;
    }

    public function get_cantidad_trabajadores()
    {
        return $this->cantidad_trabajadores;
    }
    public function set_cantidad_trabajadores($value)
    {
        $this->cantidad_trabajadores = $value;
    }

    public function get_representantes_legales()
    {
        return $this->representantes_legales;
    }
    public function set_representantes_legales($value)
    {
        $this->representantes_legales = $value;
    }

    public function get_oficio()
    {
        return $this->oficio;
    }
    public function set_oficio($value)
    {
        $this->oficio = $value;
    }

    public function get_ubigeo()
    {
        return $this->ubigeo;
    }
    public function set_ubigeo($value)
    {
        $this->ubigeo = $value;
    }

    public function get_deuda_coactiva()
    {
        return $this->deuda_coactiva;
    }
    public function set_deuda_coactiva($value)
    {
        $this->deuda_coactiva = $value;
    }

    public function get_dir_tipo_via()
    {
        return $this->dir_tipo_via;
    }
    public function set_dir_tipo_via($value)
    {
        $this->dir_tipo_via = $value;
    }

    public function get_dir_cod_zona()
    {
        return $this->dir_cod_zona;
    }
    public function set_dir_cod_zona($value)
    {
        $this->dir_cod_zona = $value;
    }

    public function get_dir_tipo_zona()
    {
        return $this->dir_tipo_zona;
    }
    public function set_dir_tipo_zona($value)
    {
        $this->dir_tipo_zona = $value;
    }

    public function get_dir_num()
    {
        return $this->dir_num;
    }
    public function set_dir_num($value)
    {
        $this->dir_num = $value;
    }

    public function get_dir_interior()
    {
        return $this->dir_interior;
    }
    public function set_dir_interior($value)
    {
        $this->dir_interior = $value;
    }

    public function get_dir_lote()
    {
        return $this->dir_lote;
    }
    public function set_dir_lote($value)
    {
        $this->dir_lote = $value;
    }

    public function get_dir_dpto()
    {
        return $this->dir_dpto;
    }
    public function set_dir_dpto($value)
    {
        $this->dir_dpto = $value;
    }

    public function get_dir_manzana()
    {
        return $this->dir_manzana;
    }
    public function set_dir_manzana($value)
    {
        $this->dir_manzana = $value;
    }

    public function get_dir_km()
    {
        return $this->dir_km;
    }
    public function set_dir_km($value)
    {
        $this->dir_km = $value;
    }

    public function get_dir_nomb_via()
    {
        return $this->dir_nomb_via;
    }
    public function set_dir_nomb_via($value)
    {
        $this->dir_nomb_via = $value;
    }

    public function get_emision_electronica()
    {
        return $this->emision_electronica;
    }
    public function set_emision_electronica($value, $format_in = "d/m/Y")
    {
        $this->emision_electronica = $this->format_fecha($value, $format_in);
    }

    public function get_telefono()
    {
        return $this->telefono;
    }
    public function set_telefono($value)
    {
        $this->telefono = $value;
    }

    public function get_prefijo()
    {
        return $this->prefijo;
    }
    public function set_prefijo($value)
    {
        $this->prefijo = $value;
    }

    // DB
    public function get_fecha_registro()
    {
        return $this->fecha_registro;
    }
    public function set_fecha_registro($value)
    {
        $this->fecha_registro = $value;
    }

    public function get_fecha_actualizacion()
    {
        return $this->fecha_actualizacion;
    }
    public function set_fecha_actualizacion($value)
    {
        $this->fecha_actualizacion = $value;
    }

    public function get_completo()
    {
        return $this->completo;
    }
    public function set_completo($value)
    {
        $this->completo = $value;
    }
}

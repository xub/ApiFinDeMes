<?php

namespace App\Document\Findemes;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\Document
 */
class Balance
{
    /**
     * @MongoDB\Id(strategy="auto")
     */
    protected $id;
 
    /**
     * @MongoDB\Field(type="string")
     * */
    private $idusuario;

    /**
     * @MongoDB\Field(type="string")
     * */
    private $idcategoria;

    /**
     * @MongoDB\Field(type="string")
     * */
    private $idempresa;

    /**
     * @MongoDB\Field(type="string")
     * */
    private $nombre;
   
    /**
     * @MongoDB\Field(type="float")
     * */
    private $importe;

    /**
     * @MongoDB\Field(type="date")
     * */
    private $fecha;

    /**
     * @MongoDB\Field(type="string")
     * */
    private $nota;

    /**
     * @MongoDB\Field(type="string")
     * */
    private $tipo;

    public function __construct()
    {
       // parent::__construct();
        // your own logic
    }

    /**
     * Set id
     *
     * @param string $params
     * @return Paises
     */
    public function setId($params) {
        $this->id = $params;
        
        return $this;
    }
    
    /**
     * Get id
     *
     * @return string
     */
    public function getId() {
        return $this->id;
    }
    
    /**
     * Set nombre
     *
     * @param string $params
     * @return Paises
     */
    public function setNombre($params) {
        $this->nombre = $params;
        
        return $this;
    }
    
    /**
     * Get nombre
     *
     * @return string
     */
    public function getNombre() {
        return $this->nombre;
    }  
    
    /**
     * Set idusuario
     *
     * @param string $params
     * @return Empresas
     */
    public function setIdusuario($params) {
        $this->idusuario = $params;
        
        return $this;
    }
    
    /**
     * Get idusuario
     *
     * @return string
     */
    public function getIdusuario() {
        return $this->idusuario;
    }  

    /**
     * Set idcategoria
     *
     * @param string $params
     * @return Empresas
     */
    public function setIdcategoria($params) {
        $this->idcategoria = $params;
        
        return $this;
    }
    
    /**
     * Get idcategoria
     *
     * @return string
     */
    public function getIdcategoria() {
        return $this->idcategoria;
    }  

    /**
     * Set idempresa
     *
     * @param string $params
     * @return Empresas
     */
    public function setIdempresa($params) {
        $this->idempresa = $params;
        
        return $this;
    }
    
    /**
     * Get idempresa
     *
     * @return string
     */
    public function getIdempresa() {
        return $this->idempresa;
    }
    
    /**
     * Set importe
     *
     * @param string $params
     * @return Empresas
     */
    public function setImporte($params) {
        $this->importe = $params;
        
        return $this;
    }
    
    /**
     * Get importe
     *
     * @return string
     */
    public function getImporte() {
        return $this->importe;
    }  
    
    /**
     * Set nota
     *
     * @param string $params
     * @return Empresas
     */
    public function setNota($params) {
        $this->nota = $params;
        
        return $this;
    }
    
    /**
     * Get nota
     *
     * @return string
     */
    public function getNota() {
        return $this->nota;
    }  

    /**
     * Set fecha
     *
     * @param string $params
     * @return Empresas
     */
    public function setFecha($params) {
        $this->fecha = $params;
        
        return $this;
    }
    
    /**
     * Get fecha
     *
     * @return string
     */
    public function getFecha() {
        return $this->fecha;
    }  

    /**
     * Set tipo
     *
     * @param string $params
     * @return Empresas
     */
    public function setTipo($params) {
        $this->tipo = $params;
        
        return $this;
    }
    
    /**
     * Get tipo
     *
     * @return string
     */
    public function getTipo() {
        return $this->tipo;
    }  
    
}
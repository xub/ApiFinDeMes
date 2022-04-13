<?php

namespace App\Document\Findemes;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\Document
 */
class Categorias
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
    private $nombre;

    /**
     * @MongoDB\Field(type="date")
     * */
    private $fecha;

    /**
     * Set id
     *
     * @param string $params
     * @return Clientes
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
     * Set idusuario
     *
     * @param string $params
     * @return Categorias
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
     * Set nombre
     *
     * @param string $params
     * @return Clientes
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
     * Set fecha
     *
     * @param string $params
     * @return Categorias
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

}
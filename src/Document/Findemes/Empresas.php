<?php

namespace App\Document\Findemes;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\Document
 */
class Empresas
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

    
}
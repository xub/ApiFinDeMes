<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Bundle\MongoDBBundle\Validator\Constraints\Unique as MongoDBUnique;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

/**
 * @MongoDB\Document(collection="users")
 * @MongoDBUnique(fields="email")
 */
class User implements UserInterface , PasswordAuthenticatedUserInterface
{
    /**
     * @MongoDB\Id
     */
    protected $id;

    /**
     * @MongoDB\Field(type="string")
     * @MongoDB\UniqueIndex(order="asc")
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    protected $email;

    /**
     * @MongoDB\Field(type="string")
     * @Assert\NotBlank()
     */
    protected $password;

    /**
     * @MongoDB\Field(type="string")
     * @Assert\NotBlank()
     */
    protected $empresa;

    /**
     * @MongoDB\Field(type="string")
     * @Assert\NotBlank()
     */
    protected $producto;

    /**
     * @MongoDB\Field(type="string")
     * @Assert\NotBlank()
     */
    protected $username;

    /**
     * @MongoDB\Field(type="hash")
     */
    private $roles = [];

    public function getId()
    {
        return $this->id;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getEmpresa()
    {
        return $this->empresa;
    }

    public function getProducto()
    {
        return $this->producto;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @deprecated since Symfony 5.3
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }
 
    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }  

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function setUsername($username)
    {
        $this->username = $username;
    }

    public function setEmpresa($empresa)
    {
        $this->empresa = $empresa;
    }

    public function setProducto($producto)
    {
        $this->producto = $producto;
    }
}
 
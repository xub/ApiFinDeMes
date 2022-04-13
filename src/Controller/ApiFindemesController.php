<?php

/**
 * Clase ApiFindemes
 * Api con JWT y Auth, base en MongoDB
 * Sergio Sambataro para findemes.ar
 * GNU General Public License v3.0
 *
 * Mod 03/2022
 *
 * API Controller
 *
 */

namespace App\Controller;

use Doctrine\Bundle\MongoDBBundle\ManagerRegistry; 
use MongoDB\BSON\UTCDateTime as UTCDatetime;

use App\Document\User;
use App\Document\Findemes\Empresas;
use App\Document\Findemes\Balance;
use App\Document\Findemes\Categorias;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Class ApiController
 *
 * @Route("/api")
 */
class ApiFindemesController extends AbstractFOSRestController {

    private $passwordEncoder;
    private $client;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder, HttpClientInterface $client) {
        $this->passwordEncoder = $passwordEncoder;
        $this->client = $client;
    }

    /**
     * @Rest\Post("/v1/register_findemes", name="register_findemes")
     *
     * @OA\Response(
     *     response=201,
     *     description="User was successfully registered"
     * )
     *
     * @OA\Response(
     *     response=500,
     *     description="User was not successfully registered"
     * )
     *
     * @OA\Parameter(
     *     name="_name",
     *     in="query",
     *     description="The username",
     *     schema={}
     * )
     *
     * @OA\Parameter(
     *     name="_email",
     *     in="query",
     *     description="The username",
     *     schema={}
     * )
     *
     * @OA\Parameter(
     *     name="_username",
     *     in="query",
     *     description="The username",
     *     schema={}
     * )
     *
     * @OA\Parameter(
     *     name="_password",
     *     in="query",
     *     description="The password"
     * )
     *
     * @OA\Tag(name="User")
     */
    public function register_findemesAction(Request $request, UserPasswordHasherInterface $passwordHasher, ManagerRegistry $documentManager) {
        $serializer = $this->get('serializer');

        $user = [];
        $message = "";

        //Damos de alta al administrador de su nueva empresa
        $mine = $documentManager->getManager('default');
        $findemes = $documentManager->getManager('findemes');

        try {
            $code = 200;
            $error = false;

            $name = $request->request->get('_name');
            $email = $request->request->get('_email');
            $username = $request->request->get('_username');
            $password = $request->request->get('_password');
            $empresa = $request->request->get('_empresa');
            $producto = $request->request->get('_producto');
            
            $roles[] = 'ROLE_ADMIN';

            $user = new User();
            $user->setUsername($email);
            $user->setEmail($email);
            $user->setRoles($roles);
            //$user->setEnabled(true);
            $user->setEmpresa($empresa);
            $user->setProducto($producto);

            // hash the password (based on the security.yaml config for the $user class)
            $hashedPassword = $passwordHasher->hashPassword($user,$password);
            $user->setPassword($hashedPassword);
            $mine->persist($user);

            $emp = new Empresas();
            $emp->setNombre($user->getEmpresa());
            $emp->setIdusuario($user->getId());
            $findemes->persist($emp);

            $findemes->flush();
            $mine->flush();
        } catch (Exception $ex) {
            $code = 500;
            $error = true;
            $message = "An error has occurred trying to register the user - Error: {$ex->getMessage()}";
        }

        $response = [
            'code' => $code,
            'error' => $error,
            'data' => $code == 200 ? $user : $message,
        ];

        return new Response($serializer->serialize($response, "json"));
    }

    /**
    * @Rest\Post("/v1/addmodbalance", name="addmodbalance")
    *
    * @OA\Response(
    *     response=200,
    *     description="Alta y modificacion de balance"
    * )
    *
    * @OA\Response(
    *     response=500,
    *     description="Error al tratar de dar de alta o modificar el balance"
    * )
    *
    * @OA\Parameter(
    *     name="email",
    *     in="query",
    *     description="Email del usuario"
    * )
    *
    * @OA\Parameter(
    *     name="row",
    *     in="query",
    *     description="Arreglo con datos"
    * )
    * @OA\Tag(name="Balance")
     */
    public function addmodBalance(Request $request, ManagerRegistry $documentManager) {
        $serializer = $this->get('serializer');

        $html = [];
        $message = "";

        $dm = $documentManager->getManager('findemes');
        $em = $documentManager->getManager('default');
        
        try {
            $code = 200;
            $error = false;

            $id = $request->get("id");
            $email = $request->get("email");
            $rowdata = json_decode($request->get("row"), true);

            $u = $em->getRepository('App:User')->findOneBy(array('email' => $email));
            $empresa = $dm->getRepository('App:Findemes\Empresas')->findOneBy(array('nombre' => $u->getEmpresa()));

            if ($u->getRoles()[0] == 'ROLE_CLIENTE' || $u->getRoles()[0] == 'ROLE_ADMIN') {
                
                $balance = $dm->getRepository('App:Findemes\Balance')->find($id);

                if ($balance) {
                    
                    if($rowdata["tipo"]=='gastos'){
                        $importe=$rowdata["importe"];
                        if($rowdata["importe"]>0)
                        $importe=$rowdata["importe"]*-1;
                    }else{
                        $importe=$rowdata["importe"];
                    }

                    $balance->setNombre($rowdata["nombre"]);
                    $balance->setImporte(number_format($importe, 2, '.', ''));
                    $balance->setNota($rowdata["nota"]);
                    $balance->setIdempresa($empresa->getId());
                    $balance->setIdusuario($u->getId());
                    $balance->setIdcategoria($rowdata["categoria"]);
                    $balance->setTipo($rowdata["tipo"]);
        
                    $fechae = date('Y-m-d');

                    if($rowdata["fecha"])
                        $fechae = $rowdata["fecha"];

                    $fecha = new UTCDateTime(strtotime($fechae) * 1000);
                    $balance->setFecha($fecha);  
        
                    $dm->persist($balance);
                    $dm->flush();
                } else {

                    if($rowdata["tipo"]=='gastos'){
                        $importe=$rowdata["importe"];
                        if($rowdata["importe"]>0)
                        $importe=$rowdata["importe"]*-1;
                    }else{
                        $importe=$rowdata["importe"];
                    }

                    $balance = new Balance();
                    $balance->setNombre($rowdata["nombre"]);
                    $balance->setImporte(number_format($importe, 2, '.', ''));
                    $balance->setNota($rowdata["nota"]);
                    $balance->setIdempresa($empresa->getId());
                    $balance->setIdusuario($u->getId());
                    $balance->setIdcategoria($rowdata["categoria"]);
                    $balance->setTipo($rowdata["tipo"]);
        
                    $fechae = date('Y-m-d');

                    if($rowdata["fecha"])
                    $fechae = $rowdata["fecha"];

                    $fecha = new UTCDateTime(strtotime($fechae) * 1000);
                    $balance->setFecha($fecha); 
        
                    $dm->persist($balance);
                    $dm->flush();
       
                }
            }

            $response = new Response();
            $response->headers->set('Content-Type', 'application/json');

            $html[] = array(
                'id' => $balance->getId(),
            );

        } catch (Exception $ex) {
            $code = 500;
            $error = true;
            $message = "An error has occurred trying to get all Balance - Error: {$ex->getMessage()}";
        }

        $response = [
            'code' => $code,
            'error' => $error,
            'data' => $code == 200 ? $html : $html,
        ];

        return new Response($serializer->serialize($response, "json"));
    }

    /**
    * @Rest\Post("/v1/gettotal", name="gettotal")
    *
    * @OA\Response(
    *     response=200,
    *     description="Total del balance"
    * )
    *
    * @OA\Response(
    *     response=500,
    *     description="Error al tratar de obtener el total"
    * )
    *
    * @OA\Parameter(
    *     name="id",
    *     in="query",
    *     description="Id de usuario"
    * )
    *
    * @OA\Parameter(
    *     name="email",
    *     in="query",
    *     description="Email del usuario"
    * )
    *
    * @OA\Parameter(
    *     name="row",
    *     in="query",
    *     description="Arreglo con datos"
    * )
    * @OA\Tag(name="Balance")
     */
    public function getTotal(Request $request, ManagerRegistry $documentManager) {
        $serializer = $this->get('serializer');

        $html = [];
        $message = "";

        $dm = $documentManager->getManager('findemes');
        $em = $documentManager->getManager('default');

        try {
            $code = 200;
            $error = false;
            $total=0;

            $email = $request->get("email");
            $id = $request->get("id");
            $rowdata = json_decode($request->get("row"), true);
   
            $u = $em->getRepository('App:User')->findOneBy(array('email' => $email));
            $empresa = $dm->getRepository('App:Findemes\Empresas')->findOneBy(array('nombre' => $u->getEmpresa()));
    
            if ($u->getRoles()[0] == 'ROLE_CLIENTE' || $u->getRoles()[0] == 'ROLE_ADMIN') {
                $builder2 = $dm->createAggregationBuilder('App:Findemes\Balance');

                $fecha = new \DateTime();
                $fecha->modify('first day of this month');
                $fechai = $fecha->format('Y/m/d');         
                
                $fecha = new \DateTime();
                $fecha->modify('last day of this month');
                $fechaf = $fecha->format('Y/m/d'); 

                $builder2
                        ->match()
                        ->field('fecha')->gte($fechai)->lte($fechaf)
                        ->field('idusuario')->equals($u->getId())
                        ->group()
                        ->field('id')
                        ->expression(null)
                        ->field('importe')
                        ->sum('$importe');
        
                $result2 = $builder2->execute();
        
                if (count($result2->toArray()) > 0) {
                    foreach ($result2 as $rew) {
                        $tot = number_format($rew['importe'], 2, '.', '');
                    }
                } else {
                    $tot = 0;
                }
            
            }

            $response = new Response();
            $response->headers->set('Content-Type', 'application/json');

            $i = 0;
            $es = '';
            $html = array(
                'total' => $tot,
            );

        } catch (Exception $ex) {
            $code = 500;
            $error = true;
            $message = "An error has occurred trying to get total - Error: {$ex->getMessage()}";
        }

        $response = [
            'code' => $code,
            'error' => $error,
            'data' => $code == 200 ? $html : $html,
        ];

        return new Response($serializer->serialize($response, "json"));
    }

    /**
    * @Rest\Post("/v1/getbalance", name="getbalance")
    *
    * @OA\Response(
    *     response=200,
    *     description="Listado de balance"
    * )
    *
    * @OA\Response(
    *     response=500,
    *     description="Error al tratar de listar balance"
    * )
    *
    * @OA\Parameter(
    *     name="id",
    *     in="query",
    *     description="Id del balance"
    * )
    *
    * @OA\Parameter(
    *     name="email",
    *     in="query",
    *     description="Email del usuario"
    * )
    * @OA\Tag(name="Balance")
     */
    public function getBalance(Request $request, ManagerRegistry $documentManager) {
        $serializer = $this->get('serializer');

        $html = [];
        $message = "";

        $dm = $documentManager->getManager('findemes');
        $em = $documentManager->getManager('default');

        try {
            $code = 200;
            $error = false;

            $email = $request->get("email");
            $id = $request->get("id");

            $u = $em->getRepository('App:User')->findOneBy(array('email' => $email));

            if ($u->getRoles()[0] == 'ROLE_CLIENTE' || $u->getRoles()[0] == 'ROLE_ADMIN') {
                $balance = $dm->getRepository('App:Findemes\Balance')->findBy(array('idusuario' => $u->getId()));
            }

            $response = new Response();
            $response->headers->set('Content-Type', 'application/json');

            if($balance) {
                foreach($balance as $bal){

                    $nombre='';
                    $nom = $dm->getRepository('App:Findemes\Categorias')->findOneBy(array('id' => $bal->getIdcategoria()));
                    if ($nom){
                        $nombre=$nom->getNombre();
                    }

                    $html[] = array(
                        'nombre' => $bal->getNombre(),
                        'categoria' => $nombre,
                        'importe' => $bal->getImporte(),
                        'nota' => $bal->getNota(),
                        'fecha' => $bal->getFecha()->format('Y-m-d'),
                        'id' => $bal->getId(),
                    );
                }
            }else{
                $html = '';
            }

        } catch (Exception $ex) {
            $code = 500;
            $error = true;
            $message = "An error has occurred trying to get all Boards - Error: {$ex->getMessage()}";
        }

        $response = [
            'code' => $code,
            'error' => $error,
            'data' => $code == 200 ? $html : $html,
        ];

        return new Response($serializer->serialize($response, "json"));
    }

    /**
    * @Rest\Post("/v1/getbalanceid", name="getbalanceid")
    *
    * @OA\Response(
    *     response=200,
    *     description="Listado de balance por id"
    * )
    *
    * @OA\Response(
    *     response=500,
    *     description="Error al tratar de listar balance por id"
    * )
    *
    * @OA\Parameter(
    *     name="id",
    *     in="query",
    *     description="Id del balance"
    * )
    *
    * @OA\Parameter(
    *     name="email",
    *     in="query",
    *     description="Email del usuario"
    * )
    * @OA\Tag(name="Balance")
     */
    public function getBalanceid(Request $request, ManagerRegistry $documentManager) {
        $serializer = $this->get('serializer');

        $html = [];
        $message = "";

        $dm = $documentManager->getManager('findemes');
        $em = $documentManager->getManager('default');

        try {
            $code = 200;
            $error = false;

            $email = $request->get("email");
            $id = $request->get("id");

            $u = $em->getRepository('App:User')->findOneBy(array('email' => $email));

            if ($u->getRoles()[0] == 'ROLE_CLIENTE' || $u->getRoles()[0] == 'ROLE_ADMIN') {
                $balance = $dm->getRepository('App:Findemes\Balance')->findOneBy(array('idusuario' => $u->getId(),'id' => $id  ));
            }

            $response = new Response();
            $response->headers->set('Content-Type', 'application/json');

            if($balance) { 
                    $html[] = array(
                        'nombre' => $balance->getNombre(),
                        'importe' => $balance->getImporte(),
                        'categoria' => $balance->getIdcategoria(),
                        'nota' => $balance->getNota(),
                        'fecha' => $balance->getFecha()->format('Y-m-d'),
                        'id' => $balance->getId(),
                        'tipo' => $balance->getTipo(),
                    );
            }else{
                $html = '';
            }

        } catch (Exception $ex) {
            $code = 500;
            $error = true;
            $message = "An error has occurred trying to get all Balance ID - Error: {$ex->getMessage()}";
        }

        $response = [
            'code' => $code,
            'error' => $error,
            'data' => $code == 200 ? $html : $html,
        ];

        return new Response($serializer->serialize($response, "json"));
    }

    /**
    * @Rest\Post("/v1/delmovimiento", name="delmovimiento")
    *
    * @OA\Response(
    *     response=200,
    *     description="Elimina un movimiento del balance"
    * )
    *
    * @OA\Response(
    *     response=500,
    *     description="Error al tratar de eliminar un movimiento del balance"
    * )
    *
    * @OA\Parameter(
    *     name="id",
    *     in="query",
    *     description="Id del movimiento"
    * )
    *
    * @OA\Tag(name="Balance")
     */
    public function delMovimiento(Request $request, ManagerRegistry $documentManager) {
        $serializer = $this->get('serializer');

        $id = $request->get("id");
        $code = 200;
        $error = false;
    
        $dm = $documentManager->getManager('findemes');

        $movimiento = $dm->getRepository('App:Findemes\Balance')->find($id);
        $dm->remove($movimiento);
        $dm->flush();

        $html[] = array(
            'id' => $id,
        );
        
        $response = [
            'code' => $code,
            'error' => $error,
            'data' => $code == 200 ? $html : $html,
        ];
        return new Response($serializer->serialize($response, "json"));

    }

    /**
    * @Rest\Post("/v1/getcategorias", name="getcategorias")
    *
    * @OA\Response(
    *     response=200,
    *     description="Lista Categorias"
    * )
    *
    * @OA\Response(
    *     response=500,
    *     description="Error al listar categorias"
    * )
    *
    * @OA\Parameter(
    *     name="email",
    *     in="query",
    *     description="Email del usuario"
    * )
    *
    * @OA\Tag(name="Categorias")
     */
    public function getCategorias(Request $request, ManagerRegistry $documentManager) {
        $serializer = $this->get('serializer');

        $user = [];
        $message = "";

        $dm = $documentManager->getManager('findemes');
        $em = $documentManager->getManager('default');

        try {
            $code = 200;
            $error = false;

            $email = $request->get("email");

            $u = $em->getRepository('App:User')->findOneBy(array('email' => $email));
            $categorias = $dm->getRepository('App:Findemes\Categorias')->findBy(array('idusuario' => $u->getId()));

            $response = new Response();
            $response->headers->set('Content-Type', 'application/json');

            if($categorias){
                foreach ($categorias as $row) {
                    $html[] = array(
                        'nombre' => $row->getNombre(),
                        'id' => $row->getId(),
                    );
                }
            }else{
                $html[] = array();
            }

        } catch (Exception $ex) {
            $code = 500;
            $error = true;
            $message = "An error has occurred trying to get all Categorias - Error: {$ex->getMessage()}";
        }

        $response = [
            'code' => $code,
            'error' => $error,
            'data' => $code == 200 ? $html : $html,
        ];

        return new Response($serializer->serialize($response, "json"));
    }

    /**
    * @Rest\Post("/v1/getcategoria", name="getcategoria")
    *
    * @OA\Response(
    *     response=200,
    *     description="Listado de Categoria"
    * )
    *
    * @OA\Response(
    *     response=500,
    *     description="Error al listar la categoria"
    * )
    *
    * @OA\Parameter(
    *     name="email",
    *     in="query",
    *     description="Email del usuario"
    * )
    *
    * @OA\Tag(name="Categorias")
     */
   public function getCategoria(Request $request, ManagerRegistry $documentManager) {
        $serializer = $this->get('serializer');

        $user = [];
        $message = "";

        $dm = $documentManager->getManager('findemes');
        $em = $documentManager->getManager('default');

        try {
            $code = 200;
            $error = false;

            $id = $request->get("id");

            $categoria = $dm->getRepository('App:Findemes\Categorias')->findOneBy(array('id' => $id ));

            $response = new Response();
            $response->headers->set('Content-Type', 'application/json');

            if($categoria) {
       
                $html[] = array(
                    'nombre' => $categoria->getNombre(),
                    'id' => $categoria->getId(),
                );

            }

        } catch (Exception $ex) {
            $code = 500;
            $error = true;
            $message = "An error has occurred trying to get Categoria - Error: {$ex->getMessage()}";
        }

        $response = [
            'code' => $code,
            'error' => $error,
            'data' => $code == 200 ? $html : $html,
        ];

        return new Response($serializer->serialize($response, "json"));
    }

    /**
    * @Rest\Post("/v1/addmodcategorias", name="addmodcategorias")
    *
    * @OA\Response(
    *     response=200,
    *     description="Alta y Modificacion de Categoria"
    * )
    *
    * @OA\Response(
    *     response=500,
    *     description="Error al Alta o Modificacion de categoria"
    * )
    *
    * @OA\Parameter(
    *     name="email",
    *     in="query",
    *     description="Email del usuario"
    * )
    *
    * @OA\Tag(name="Categorias")
     */
     public function addmodCategorias(Request $request, ManagerRegistry $documentManager) {
        $serializer = $this->get('serializer');

        $user = [];
        $message = "";

        $dm = $documentManager->getManager('findemes');
        $em = $documentManager->getManager('default');

        try {
            $code = 200;
            $error = false;
                
            $email = $request->get("email");
            $id = $request->get("id");
            $rowdata = json_decode($request->get("row"), true);

            $u = $em->getRepository('App:User')->findOneBy(array('email' => $email));

            $categoria = $dm->getRepository('App:Findemes\Categorias')->find($id);

                if ($categoria) {
                    $categoria->setNombre($rowdata["nombre"]);
                    $categoria->setIdusuario($u->getId());
        
                    $fechae = date('Y/m/d');
                    $fecha = new UTCDateTime(strtotime($fechae) * 1000);
                    $categoria->setFecha($fecha);  
        
                    $dm->persist($categoria);
                    $dm->flush();
                } else {
                    $categoria = new Categorias();
                    $categoria->setNombre($rowdata["nombre"]);
                    $categoria->setIdusuario($u->getId());
        
                    $fechae = date('Y/m/d'); //esto cambia de fichada web
                    $fecha = new UTCDateTime(strtotime($fechae) * 1000);
                    $categoria->setFecha($fecha); 
          
                    $dm->persist($categoria);
                    $dm->flush();
       
                }

            $response = new Response();
            $response->headers->set('Content-Type', 'application/json');

            $html[] = array(
                'nombre' => $categoria->getNombre(),
                'id' => $categoria->getId(),
            );

        } catch (Exception $ex) {
            $code = 500;
            $error = true;
            $message = "An error has occurred trying to add mod Categoria - Error: {$ex->getMessage()}";
        }

        $response = [
            'code' => $code,
            'error' => $error,
            'data' => $code == 200 ? $html : $html,
        ];

        return new Response($serializer->serialize($response, "json"));
    }

    /**
    * @Rest\Post("/v1/delcategoria", name="delcategoria")
    *
    * @OA\Response(
    *     response=200,
    *     description="Eliminar una categoria"
    * )
    *
    * @OA\Response(
    *     response=500,
    *     description="Error al tratar de eliminar una categoria"
    * )
    *
    * @OA\Parameter(
    *     name="id",
    *     in="query",
    *     description="Id del movimiento"
    * )
    *
    * @OA\Tag(name="Categorias")
     */
    public function delCategoria(Request $request, ManagerRegistry $documentManager) {
        $serializer = $this->get('serializer');

        $id = $request->get("id");

        $code = 200;
        $error = false;
    
        $dm = $documentManager->getManager('findemes');

        $categoria = $dm->getRepository('App:Findemes\Categorias')->find($id);
        $dm->remove($categoria);
        $dm->flush();

        $html[] = array(
            'id' => $id,
        );
        
        $response = [
            'code' => $code,
            'error' => $error,
            'data' => $code == 200 ? $html : $html,
        ];
        return new Response($serializer->serialize($response, "json"));

    }

    /**
    * @Rest\Post("/v1/getgrafico", name="getgrafico")
    *
    * @OA\Response(
    *     response=200,
    *     description="Grafico del balance"
    * )
    *
    * @OA\Response(
    *     response=500,
    *     description="Error al tratar de listar el grafico"
    * )
    *
    * @OA\Parameter(
    *     name="id",
    *     in="query",
    *     description="Id del usuario"
    * )
    *
    * @OA\Parameter(
    *     name="email",
    *     in="query",
    *     description="Email del usuario"
    * )
    * @OA\Tag(name="Balance")
     */
    public function getGrafico(Request $request, ManagerRegistry $documentManager) {
        $serializer = $this->get('serializer');

        $user = [];
        $message = "";

        $dm = $documentManager->getManager('findemes');
        $em = $documentManager->getManager('default');

        try {
            $code = 200;
            $error = false;
            $email = $request->get("email");
            $id = $request->get("id");

            $u = $em->getRepository('App:User')->findOneBy(array('email' => $email));
            if ($u->getRoles()[0] == 'ROLE_CLIENTE' || $u->getRoles()[0] == 'ROLE_ADMIN') {
                $balance = $dm->getRepository('App:Findemes\Balance')->findBy(
                    ['idusuario' => $u->getId()],
                    ['fecha' => 'ASC']
                );

            }

            $response = new Response();
            $response->headers->set('Content-Type', 'application/json');
            
            
            $tot = 0;
            $toting = 0;
            $mesant='';

            if($balance) {
                
                foreach($balance as $bal){
                    $labels[$bal->getFecha()->format('M')]=$bal->getFecha()->format('M');

                    if($mesant!==$bal->getFecha()->format('M')){
                        $tot=0;
                        $toting=0;
                    }

                    if($bal->getImporte() < 0){
                        $tot=$tot + $bal->getImporte();
                        $gastos[$bal->getFecha()->format('M')]= number_format($tot, 2, '.', '');
                    }else{
                        $toting=$toting + $bal->getImporte();
                        $ingresos[$bal->getFecha()->format('M')]= number_format($toting, 2, '.', '');
                    }

                    $mesant=$bal->getFecha()->format('M');
                }


                $labels = array_values($labels);
                $gastos = array_values($gastos);
                $ingresos = array_values($ingresos);

                $html[] = array(
                    'labels' => $labels,
                    'gastos' => $gastos,
                    'ingresos' => $ingresos,
                );
                    
            }else{
                $html = '';
            }

        } catch (Exception $ex) {
            $code = 500;
            $error = true;
            $message = "An error has occurred trying to get all Grafico - Error: {$ex->getMessage()}";
        }

        $response = [
            'code' => $code,
            'error' => $error,
            'data' => $code == 200 ? $html : $html,
        ];

        return new Response($serializer->serialize($response, "json"));
    }

    /**
    * @Rest\Post("/v1/getinforme", name="getinforme")
    *
    * @OA\Response(
    *     response=200,
    *     description="Informe"
    * )
    *
    * @OA\Response(
    *     response=500,
    *     description="Error al tratar de listar el informe"
    * )
    *
    * @OA\Parameter(
    *     name="id",
    *     in="query",
    *     description="Id del usuario"
    * )
    *
    * @OA\Parameter(
    *     name="email",
    *     in="query",
    *     description="Email del usuario"
    * )
    * @OA\Tag(name="Informe")
     */
    public function getInforme(Request $request, ManagerRegistry $documentManager) {
        $serializer = $this->get('serializer');

        $email = $request->get("email");
        $id = $request->get("id");
        $rowdata = json_decode($request->get("row"), true);

        $dm = $documentManager->getManager('findemes');
        $em = $documentManager->getManager('default');

        $u = $em->getRepository('App:User')->findOneBy(array('email' => $email));

        $fechai = $rowdata['fechainicio'];
        $fechai = date('Y/m/d', strtotime($fechai)); //esto cambia de fichada web
        $fechai = new UTCDateTime(strtotime($fechai) * 1000);

        $fechaf = $rowdata['fechafin'];
        $fechaf = date('Y/m/d', strtotime($fechaf)); //esto cambia de fichada web
        $fechaf = new UTCDateTime(strtotime($fechaf) * 1000);

        $builder2 = $dm->createAggregationBuilder('App:Findemes\Balance');
        $builder2
        ->match()
        ->field('fecha')->gte($fechai)->lte($fechaf)
        ->field('idusuario')->equals($u->getId())
        ->group()
        ->field('id')
        ->expression('$idcategoria')
        ->field('idcategoria')->first('$idcategoria')
        ->field('importe')
        ->sum('$importe');

        $code = 200;
        $error = false;

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');

        $i = 0;
        $es = '';

        $result2 = $builder2->execute();
        if (count($result2->toArray()) > 0) {
            foreach ($result2 as $rew) {
                $cat = $dm->getRepository('App:Findemes\Categorias')->findOneBy(array('id' => $rew['idcategoria'] ));
                $nombre='Sin Categoria';
                if($cat)
                    $nombre=$cat->getNombre();
                $html[] = array(
                    'nombre' => $nombre,
                    'importe' => $rew['importe'],
                    'fecha' => '',
                );
            }
        } else {
            $tot = 0;
        }

        $response = [
            'code' => $code,
            'error' => $error,
            'data' => $code == 200 ? $html : $html,
        ];

        return new Response($serializer->serialize($response, "json"));
    }

}

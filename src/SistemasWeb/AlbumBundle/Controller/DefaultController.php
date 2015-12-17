<?php

namespace SistemasWeb\AlbumBundle\Controller;

use SistemasWeb\AlbumBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    public function indexAction()
    {
        if($this->isLogged()) {
            $session = $this->getRequest()->getSession();
            $user = $session->get('login');
            return $this->redirect($this->generateUrl('user_homepage'));
    }
        return $this->render('AlbumBundle:Default:index.html.twig');

    }
    public function loginAction(Request $request)
    {
        if($this->isLogged()) {
            $session = $this->getRequest()->getSession();
            $user = $session->get('login');
            return $this->redirect($this->generateUrl('user_homepage'));
        }
        if($this->get('request')->getMethod() == 'POST') {
            $username = $request->get('username');
            $password = sha1($request->get('password'));
            $em = $this->getDoctrine()->getEntityManager();
            $repository = $em->getRepository('AlbumBundle:User');
            $user = $repository->findOneBy( array('username'=>$username, 'password'=>$password) );
            if($user) {
                if(!$user->getActive()) {
                    return $this->render('AlbumBundle:Default:login.html.twig', array('message'=>'El usuario no esta activado'));
                }
                $session = $this->getRequest()->getSession();
                $session->set('login', $user);
                return $this->redirect($this->generateUrl('album_homepage'));
            } else {
                return $this->render('AlbumBundle:Default:login.html.twig', array('message'=>'La contraseña o el usuario es incorrecta'));
            }
        } else {
            return $this->render('AlbumBundle:Default:login.html.twig');
        }
    }
    public function registroAction(Request $request)
    {
        if($this->isLogged()) {
            $session = $this->getRequest()->getSession();
            $user = $session->get('login');
            return $this->redirect($this->generateUrl('user_homepage'));
        }
        if($this->get('request')->getMethod() == 'POST') {
            $user = new User();
            $user->setActive(false);
            $user->setAdmin(false);
            $user->setUsername($request->request->get('username'));
            $user->setPassword(sha1($request->request->get('password')));
            $user->setEmail($request->request->get('email'));

            try {
                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $emaitza = $em->flush();
            } catch (\Exception $e) {
                return $this->render('AlbumBundle:Default:registro.html.twig', array('message'=>'Ya existe un usuario con ese nombre o correo'));
            }

            return $this->render('AlbumBundle:Default:registro.html.twig', array('success'=>'Usuario correctamente registrado'));
        } else {
            return $this->render('AlbumBundle:Default:registro.html.twig');
        }
    }
    private function isLogged() {
        $session = $this->getRequest()->getSession();
        $user = $session->get('login');
        if($user) {
            return true;
        }
        return false;
    }
}

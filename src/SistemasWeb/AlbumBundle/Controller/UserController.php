<?php

namespace SistemasWeb\AlbumBundle\Controller;

use SistemasWeb\AlbumBundle\Entity\Album;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class UserController extends Controller
{
    public function logoutAction() {
        $session = $this->getRequest()->getSession();
        $session->clear();
        return $this->redirect($this->generateUrl('album_homepage'));
    }
    public function dashboardAction() {
        if(!$this->isLogged()) {
            return $this->redirect($this->generateUrl('album_homepage'));
        }
        $session = $this->getRequest()->getSession();
        $user = $session->get('login');
        if($this->isAdmin()) {
            return $this->render('AlbumBundle:User:index.html.twig', array('user'=> $user));
        }
        return $this->render('AlbumBundle:User:index.html.twig', array('user'=> $user));
    }
    public function createAlbumAction(Request $request) {
        if(!$this->isLogged()) {
            return $this->redirect($this->generateUrl('album_homepage'));
        }
        $session = $this->getRequest()->getSession();
        $user = $session->get('login');
        $em = $this->getDoctrine()->getEntityManager();
        $repository = $em->getRepository("AlbumBundle:Album");
        if($this->get('request')->getMethod() == 'POST') {
            $nombre = $request->request->get('nombre');
            $privacidad = $request->request->get('privacidad');
            $existe = $repository->findOneBy(array('nombre' => $nombre));
            if($existe) {
                $albums = $repository->findBy(array('userId' => $user->getId()));
                return $this->render('AlbumBundle:User:create.html.twig', array('user'=> $user, 'albums' => $albums, 'message' => 'Ya existe un album con ese nombre'));
            }
            $album = new Album();
            $album->setNombre($nombre);
            $album->setUserId($user->getId());
            $album->setPrivate(0);
            $album->setPublic(0);
            switch($privacidad) {
                case 'private':
                    $album->setPrivate(1);
                    break;
                case 'public':
                    $album->setPublic(1);
                    break;
            }
            $em->persist($album);
            $em->flush();
        }
        $albums = $repository->findBy(array('userId' => $user->getId()));
        if($this->isAdmin()) {
            return $this->render('AlbumBundle:User:create.html.twig', array('user'=> $user, 'albums' => $albums));
        }
        return $this->render('AlbumBundle:User:create.html.twig', array('user'=> $user, 'albums' => $albums));
    }

    public function editAction(Request $request) {
        if(!$this->isLogged()) {
            return $this->redirect($this->generateUrl('album_homepage'));
        }
        $session = $this->getRequest()->getSession();
        $user = $session->get('login');
        $em = $this->getDoctrine()->getEntityManager();
        $repository = $em->getRepository("AlbumBundle:User");
        if($this->get('request')->getMethod() == 'POST') {
            $oldpassword = $request->request->get('oldpassword');
            $newpassword = $request->request->get('newpassword');
            $usuarioExistente = $repository->findOneBy(array('username' => $user->getUsername(), 'password' => sha1($oldpassword)));
            if ($usuarioExistente) {
                $usuarioExistente->setPassword(sha1($newpassword));
                $em->persist($usuarioExistente);
                $em->flush();
                $session->clear();
                return $this->render('AlbumBundle:User:edit.html.twig', array('user' => $user, 'success' => 'Modificado password'));
            } else {
                return $this->render('AlbumBundle:User:edit.html.twig', array('user' => $user, 'message' => 'Contraseña incorrecta'));
            }
        }
        return $this->render('AlbumBundle:User:edit.html.twig', array('user'=> $user));
    }

    private function isLogged() {
        $session = $this->getRequest()->getSession();
        $user = $session->get('login');
        if($user) {
            return true;
        }
        return false;
    }
    private function isAdmin() {
        $session = $this->getRequest()->getSession();
        $user = $session->get('login');

        if ($user->getAdmin()) {
            return true;
        } else {
            return false;
        }
    }
}
?>
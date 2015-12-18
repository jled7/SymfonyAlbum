<?php

namespace SistemasWeb\AlbumBundle\Controller;

use SistemasWeb\AlbumBundle\Entity\Album;
use SistemasWeb\AlbumBundle\Entity\Photo;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
        return $this->render('AlbumBundle:User:create.html.twig', array('user'=> $user, 'albums' => $albums));
    }

    public function uploadPhotoAction(Request $request, $id) {
        if(!$this->isLogged()) {
            return $this->redirect($this->generateUrl('album_homepage'));
        }
        $session = $this->getRequest()->getSession();
        $user = $session->get('login');
        $em = $this->getDoctrine()->getEntityManager();
        $repository = $em->getRepository("AlbumBundle:Album");
        $repositoryPhotos = $em->getRepository("AlbumBundle:Photo");
        $album = $repository->findOneBy(array('id' => $id));
        $photos = $repositoryPhotos->findBy(array('albumId' => $id));
        return $this->render('AlbumBundle:User:add.html.twig', array('user'=> $user, 'album' => $album , 'photos' => $photos));
    }
    public function uploadAction(Request $request) {
        if(!$this->isLogged()) {
            return new Response("Not logged");
        }
        $session = $this->getRequest()->getSession();
        $user = $session->get('login');
        $em = $this->getDoctrine()->getEntityManager();
        $repositoryPhotos = $em->getRepository("AlbumBundle:Photo");
        if($this->get('request')->getMethod() == 'POST') {
            $nombre = $request->request->get('nombre');
            $album = $request->request->get('album');
            $image = $_FILES['image'];
            $tmp_file = $image['tmp_name'];

            $stream_file = fopen($tmp_file, 'rb');
            $photo = new Photo();
            $photo->setNombre($nombre);
            $photo->setAlbumId($album);
            $photo->setUserId($user->getId());
            $photo->setImage(stream_get_contents($stream_file));

            $em->persist($photo);
            $em->flush();
        }
        return new Response("Uploaded");
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

    public function showImageAction($id) {
        $session = $this->getRequest()->getSession();
        $user = $session->get('login');
        $em = $this->getDoctrine()->getEntityManager();
        $repository = $em->getRepository("AlbumBundle:Photo");
        $repositoryAlbums = $em->getRepository("AlbumBundle:Album");
        $photo = $repository->findOneBy(array('id' => $id));
        $album = $repositoryAlbums->findOneBy(array('id' => $photo->getAlbumId()));
        if($album->getPrivate() == 1) {
            if(!($album->getUserId() == $user->getId())) {
                return $this->render('AlbumBundle:Default:error.html.twig', array('message' => 'Esta foto es privada', 'user' => $user));
            }
        }
        return $this->render('AlbumBundle:User:photo.html.twig', array('user' => $user,'photo' => $photo));
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
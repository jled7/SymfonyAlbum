<?php

namespace SistemasWeb\AlbumBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminController extends Controller
{

    public function dashboardAction() {
        if(!$this->isLogged()) {
            return $this->redirect($this->generateUrl('album_homepage'));
        }
        if(!$this->isAdmin()) {
            return $this->redirect($this->generateUrl('album_homepage'));
        }
        $session = $this->getRequest()->getSession();
        $user = $session->get('login');
        return $this->render('AlbumBundle:Admin:index.html.twig', array('user'=> $user));
    }


    public function editAlbumAction() {
        $em = $this->getDoctrine()->getEntityManager();
        $repository = $em->getRepository("AlbumBundle:Album");
        $albums = $repository->findAll();
        $session = $this->getRequest()->getSession();
        $user = $session->get('login');
        return $this->render('AlbumBundle:Admin:album.html.twig', array('albums'=> $albums, 'user' => $user));
    }

    public function editUserAction() {
        $em = $this->getDoctrine()->getEntityManager();
        $repository = $em->getRepository("AlbumBundle:User");
        $users = $repository->findAll();
        $session = $this->getRequest()->getSession();
        $user = $session->get('login');
        return $this->render('AlbumBundle:Admin:user.html.twig', array('users'=> $users, 'user' => $user));
    }

    public function editPhotoAction() {
        $em = $this->getDoctrine()->getEntityManager();
        $repository = $em->getRepository("AlbumBundle:Photo");
        $photos = $repository->findAll();
        $session = $this->getRequest()->getSession();
        $user = $session->get('login');
        return $this->render('AlbumBundle:Admin:photo.html.twig', array('photos'=> $photos, 'user' => $user));
    }

    public function activateUserAction($id) {
        $em = $this->getDoctrine()->getEntityManager();
        $repository = $em->getRepository("AlbumBundle:User");
        $user = $repository->findOneBy(array('id' => $id));
        if($user) {
            $user->setActive(1);
            $em->persist($user);
            $em->flush();
        }
        return $this->redirect($this->generateUrl('admin_user'));
    }

    public function deactivateUserAction($id) {
        $em = $this->getDoctrine()->getEntityManager();
        $repository = $em->getRepository("AlbumBundle:User");
        $user = $repository->findOneBy(array('id' => $id));
        if($user) {
            $user->setActive(0);
            $em->persist($user);
            $em->flush();
        }
        return $this->redirect($this->generateUrl('admin_user'));
    }
    public function removeUserAction($id) {
        $em = $this->getDoctrine()->getEntityManager();
        $repository = $em->getRepository("AlbumBundle:User");
        $user = $repository->findOneBy(array('id' => $id));
        if($user) {
            $em->remove($user);
            $em->flush();
        }
        return $this->redirect($this->generateUrl('admin_user'));
    }
    public function removeAlbumAction($id) {
        $em = $this->getDoctrine()->getEntityManager();
        $repository = $em->getRepository("AlbumBundle:Album");
        $album = $repository->findOneBy(array('id' => $id));
        if($album) {
            $em->remove($album);
            $em->flush();
        }
        return $this->redirect($this->generateUrl('admin_album'));
    }

    public function removePhotoAction($id) {
        $em = $this->getDoctrine()->getEntityManager();
        $repository = $em->getRepository("AlbumBundle:Photo");
        $photo = $repository->findOneBy(array('id' => $id));
        if($photo) {
            $em->remove($photo);
            $em->flush();
        }
        return $this->redirect($this->generateUrl('admin_photos'));
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
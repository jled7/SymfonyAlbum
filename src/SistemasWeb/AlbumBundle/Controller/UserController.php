<?php

namespace SistemasWeb\AlbumBundle\Controller;

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
        return $this->render('AlbumBundle:User:index.html.twig', array('user'=> $user));
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
?>
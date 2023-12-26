<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route('/connexion', name: 'security.login', methods: ['GET', 'POST'])]
    public function login(AuthenticationUtils $authentification): Response
    {

        return $this->render('pages/security/login.html.twig', [
            'last_username' => $authentification->getLastUsername(),
            'error' => $authentification->getLastAuthenticationError(),
        ]);
    }

    #[Route('/deconnexion', name:'security.logout', methods: ['GET'])]
    public function logout()
    {
        //nothing to do here
    }
}

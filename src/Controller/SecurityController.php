<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;


class SecurityController extends AbstractController
{

    /**
     * this controller allow us to login
     *
     * @param AuthenticationUtils $authentification
     * @return Response
     */
    #[Route('/connexion', name: 'security.login', methods: ['GET', 'POST'])]
    public function login(AuthenticationUtils $authentification): Response
    {

        return $this->render('pages/security/login.html.twig', [
            'last_username' => $authentification->getLastUsername(),
            'error' => $authentification->getLastAuthenticationError(),
        ]);
    }


    /**
     * this controller allow us to logout
     *
     * @return void
     */
    #[Route('/deconnexion', name:'security.logout', methods: ['GET'])]
    public function logout()
    {
        //nothing to do here
    }


    /**
     * this conttroller is used to register a new user
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route('/inscription', name: 'security.registration', methods: ['GET', 'POST'])]
    public function registration(Request $request, EntityManagerInterface $manager) : Response
    {
        $user = new User();
        $user->setRoles(['ROLE_USER']);
        $form = $this->createForm(RegistrationType::class, $user);

        $form->handleRequest($request);
        // dd($form->getData());
        if ($form->isSubmitted() && $form->isValid()) {

            $user = $form->getData();



            //save user
            $manager->persist($user);
            $manager->flush();

            $this->addFlash('success', 'Votre compte a bien été créé');

            return $this->redirectToRoute('security.login');
        }


        return $this->render('pages/security/registration.html.twig', [
            'form' => $form->createView()]);
    }
}

<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Form\UserPasswordType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserController extends AbstractController
{

    /**
     * this controller is used to edit the user profile
     *
     * @param User $user
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route('/utilisateur/edition/{id}', name: 'user.edit', methods: ['GET', 'POST'])]
    public function edit(User $user, Request $request, EntityManagerInterface $manager,
    UserPasswordHasherInterface $hasher): Response
    {
    
        if (!$this->getUser()) {
            // Si personne n'est connecté, rediriger vers la page de connexion
            return $this->redirectToRoute('security.login');
        }
        if ($this->getUser() !== $user) {

            return $this->redirectToRoute('recipe.index');
        }

        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            if($hasher->isPasswordValid($user, $form->getData()->getPlainPassword())) {
                $user = $form->getData();
                $manager->persist($user);
                $manager->flush();
    
                $this->addFlash('success', 'Votre profil a bien été modifié !');
            }else{
                $this->addFlash('warning', 'Votre mot de passe est incorrect !');
            }

            return $this->redirectToRoute('recipe.index');
        }
    
        return $this->render('pages/user/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/utilisateur/edition-mot-de-passe/{id}', name: 'user.edit.password', methods: ['GET', 'POST'])]
    public function editPassword(User $user, Request $request, UserPasswordHasherInterface $hasher, EntityManagerInterface $manager) : Response
    {
        $form = $this->createForm(UserPasswordType::class, $user);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            // dd($form->getData());
            if($hasher->isPasswordValid($user, $form->getData()['plainPassword'])){
                $user->setPassword($hasher->hashPassword($user, $form->getData()['newPassword']));

                
                $manager->persist($user);
                $manager->flush();
                $this->addFlash('success', 'Votre mot de passe a bien été modifié !');
            }else{
                $this->addFlash('warning', 'Votre mot de passe est incorrect !');
            }

            return $this->redirectToRoute('recipe.index');
        
        }

        return $this->render('pages/user/edit_password.html.twig', 
        [
            'form' => $form->createView(),
        ]);
    }
    
}

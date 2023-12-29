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
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;

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
        if (!$this->isGranted('ROLE_USER') && ($this->getUser()->getId() != $user->getUser()->getId())) {
            throw new AccessDeniedException('Vous n\'avez pas le droit d\'accéder à cette ressource.');
        }
        
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
    /**
     * this controller just doesn't fucking work
     *
     * @param User $user
     * @param Request $request
     * @param UserPasswordHasherInterface $hasher
     * @param EntityManagerInterface $manager
     * @return Response
     */
    public function editPassword(User $user, Request $request, UserPasswordHasherInterface $hasher, EntityManagerInterface $manager) : Response
    {
        if (!$this->isGranted('ROLE_USER') && ($this->getUser()->getId() != $user->getUser()->getId())) {
            throw new AccessDeniedException('Vous n\'avez pas le droit d\'accéder à cette ressource.');
        }
    
        $form = $this->createForm(UserPasswordType::class, $user);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            // dd($form->getData());
            if($hasher->isPasswordValid($user, $form->getData()['plainPassword'])){
                // $user->setCreatedAt(new \DateTimeImmutable());
                $user->setPlainPassword($form->getData()['newPassword']);

                
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

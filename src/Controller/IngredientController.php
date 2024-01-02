<?php

namespace App\Controller;

use App\Entity\Ingredient;
use App\Form\IngredientType;
use App\Repository\IngredientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;



class IngredientController extends AbstractController
{

    /**
     * @param IngredientRepository $repository
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @return Response
     */
    #[Route('/ingredient', name: 'ingredient.index', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function index(IngredientRepository $repository, PaginatorInterface $paginator, Request $request): Response
    {
        // $this->denyAccessUnlessGranted('ROLE_USER');
        $ingredients = $paginator->paginate(
            $repository->findBy(['user' => $this->getUser()]),
            $request->query->getInt('page', 1),
            10
        );
        return $this->render('pages/ingredient/index.html.twig', [
            'ingredients' => $ingredients
        ]);
    }


    /**
     * this controller allow us to add ingredient in database
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route('/ingredient/nouveau', name: 'ingredient.new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function new(Request $request, EntityManagerInterface $manager) : Response
    {
        // $this->denyAccessUnlessGranted('ROLE_USER');
        $ingredient = new Ingredient();
        $form = $this->createForm(IngredientType::class, $ingredient);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $ingredient = $form->getData();
            $ingredient->setUser($this->getUser());

            $manager->persist($ingredient);
            $manager->flush();
            
            $this->addFlash('success', 'L\'ingredient a bien été ajouté');
            return $this->redirectToRoute('ingredient.index');
        }

        return $this->render('pages/ingredient/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * this controller allow us to edit ingredient in database
     *
     * @param Ingredient $ingredient
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route('/ingredient/edition/{id}', name: 'ingredient.edit', methods: ['GET', 'POST'])]
     public function edit(Ingredient $ingredient, Request $request, EntityManagerInterface $manager): Response
    {
        if (!$this->isGranted('ROLE_USER') && ($this->getUser()->getId() != $ingredient->getUser()->getId())) {
            throw new AccessDeniedException('Vous n\'avez pas le droit d\'accéder à cette ressource.');
        }
        // dd($this->getUser()->getId());
        // dd($ingredient->getUser()->getId());
        $form = $this->createForm(IngredientType::class, $ingredient);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $ingredient = $form->getData();

            $manager->persist($ingredient);
            $manager->flush();
            
            $this->addFlash('success', 'L\'ingredient a bien été modifié');
            return $this->redirectToRoute('ingredient.index');
        }

        return $this->render('pages/ingredient/edit.html.twig', [
            'form' => $form->createView()
        ]);
    }



    /**
     * this controller allow us to delete ingredient in database
     *
     * @param Ingredient $ingredient
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route('/ingredient/suppression/{id}', name: 'ingredient.delete', methods: ['GET', 'POST'])]

    public function delete(Ingredient $ingredient, EntityManagerInterface $manager): Response
    {
        if (!$this->isGranted('ROLE_USER') && ($this->getUser()->getId() != $ingredient->getUser()->getId())) {
            throw new AccessDeniedException('Vous n\'avez pas le droit d\'accéder à cette ressource.');
        }
        $manager->remove($ingredient);
        $manager->flush();

        $this->addFlash('success', 'L\'ingredient a bien été supprimé');
        return $this->redirectToRoute('ingredient.index');
    }
}

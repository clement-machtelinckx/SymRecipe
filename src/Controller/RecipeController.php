<?php

namespace App\Controller;

use App\Entity\Recipe;
use App\Form\RecipeType;
use App\Repository\RecipeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;



class RecipeController extends AbstractController
{
    /**
     * this controller display all recipes
     *
     * @param RecipeRepository $repository
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @return Response
     */
    #[Route('/recette', name: 'recipe.index', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function index(RecipeRepository $repository, PaginatorInterface $paginator, Request $request): Response
    {

        $recipes = $paginator->paginate(
            $repository->findBy(['user' => $this->getUser()]), // on récupère les recettes de l'utilisateur connecté
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('pages/recipe/index.html.twig', [
            'recipes' => $recipes,
        ]);
    }


    /**
     * this controller allow us to show all public recipe
     *
     * @param PaginatorInterface $paginator
     * @param RecipeRepository $repository
     * @param Request $request
     * @return Response
     */
    #[Route('/recette/publique', name: 'recipe.index.public', methods: ['GET'])]
    public function indexPublic(PaginatorInterface $paginator, RecipeRepository $repository, Request $request) : Response
    {
        $recipes = $paginator->paginate(
            $repository->findPublicRecipe(100),
            $request->query->getInt('page', 1),
            10

            );
        return $this->render('pages/recipe/index_public.html.twig',
        [
            'recipes' => $recipes,
        ]);
    }



    /**
     * this controller allow us to show a recipe
     *
     * @param Recipe $recipe
     * @return Response
     */
    #[Route('/recette/{id}', name: 'recipe.show', methods: ['GET'])]
    public function show(Recipe $recipe): Response
    {

        if (!($this->isGranted('ROLE_USER') && ($this->getUser()->getId() == $recipe->getUser()->getId()) || $recipe->isIsPublic())) {
            throw new AccessDeniedException('Vous n\'avez pas le droit d\'accéder à cette ressource.');
        }
        

        return $this->render('pages/recipe/show.html.twig', [
            'recipe' => $recipe,
        ]);
    }
    /**
     * fonction pour rajouter des recettes dans la bdd
     *
     * @param EntityManagerInterface $manager
     * @param Request $request
     * @return Response
     */
    #[Route('/recette/nouveau', name: 'recipe.new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function new(EntityManagerInterface $manager, Request $request, Recipe $recipe): Response
    {

        $recipe = new Recipe();
        $form = $this->createForm(RecipeType::class, $recipe);
        
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {

            $recipe = $form->getData();
            $recipe->setUser($this->getUser());
            $manager->persist($recipe);
            $manager->flush();

            $this->addFlash('success', 'La recette a bien été ajoutée');

            return $this->redirectToRoute('recipe.index');
        }

        return $this->render('pages/recipe/new.html.twig', [
            'form' => $form->createView()
        ]);
    }


    /**
     * controller pour editer une recette redicrection non foctionnel : /
     *
     * @param Recipe $recipe
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route('/recette/edition/{id}', name: 'recipe.edit', methods: ['GET', 'POST'])]
    public function edit(Recipe $recipe, Request $request, EntityManagerInterface $manager): Response
    {
        if (!$this->isGranted('ROLE_USER') && ($this->getUser()->getId() != $recipe->getUser()->getId())) {
            throw new AccessDeniedException('Vous n\'avez pas le droit d\'accéder à cette ressource.');
        }

        $form = $this->createForm(RecipeType::class, $recipe);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $recipe = $form->getData();

            $manager->persist($recipe);
            $manager->flush();
            
            $this->addFlash('success', 'La recette a bien été modifié');

            return $this->redirectToRoute('recipe.index');
        }

        return $this->render('pages/recipe/edit.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * this controller allow us to delete a recipe
     *
     * @param Recipe $recipe
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route('/recette/suppression/{id}', name: 'recipe.delete', methods: ['GET', 'POST'])]
    public function delete(Recipe $recipe, EntityManagerInterface $manager): Response
    {
        if (!$this->isGranted('ROLE_USER') && ($this->getUser()->getId() != $recipe->getUser()->getId())) {
            throw new AccessDeniedException('Vous n\'avez pas le droit d\'accéder à cette ressource.');
        }
        $manager->remove($recipe);
        $manager->flush();

        $this->addFlash('success', 'La recette a bien été supprimé');
        return $this->redirectToRoute('recipe.index');
    }
}

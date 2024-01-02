<?php

namespace App\Controller;

use App\Entity\Mark;
use App\Entity\Recipe;
use App\Form\MarkType;
use App\Form\RecipeType;
use App\Repository\MarkRepository;
use App\Repository\RecipeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
     * fonction pour rajouter des recettes dans la bdd
     *
     * @param EntityManagerInterface $manager
     * @param Request $request
     * @return Response
     */
    #[Route('/recette/nouveau', name: 'recipe.new', methods: ['GET', 'POST'])]
    // #[IsGranted('ROLE_USER')]
    public function new(EntityManagerInterface $manager, Request $request): Response
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
 * This controller allows us to show a recipe.
 *
 * @param Recipe $recipe
 * @param Request $request
 * @return Response
 */
#[Route('/recette/{id}', name: 'recipe.show', methods: ['GET', 'POST'])]
public function show(Recipe $recipe, Request $request, EntityManagerInterface $manager, MarkRepository $markRepository): Response
{
    if (!($this->isGranted('ROLE_USER') && ($this->getUser()->getId() == $recipe->getUser()->getId()) || $recipe->isIsPublic())) {
        throw new AccessDeniedException('Vous n\'avez pas le droit d\'accéder à cette ressource.');
    }

    // Créez un formulaire de notation (MarkType) associé à l'entité Mark
    $markForm = $this->createForm(MarkType::class);

    // Gérez la soumission du formulaire de notation
    $markForm->handleRequest($request);

    if ($markForm->isSubmitted() && $markForm->isValid()) {
        // Obtenez les données du formulaire de notation
        $markData = $markForm->getData();

        // Associez la note à la recette actuelle et à l'utilisateur connecté
        $markData->setUser($this->getUser());
        $markData->setRecipe($recipe);

        // Vérifiez si une note existe déjà pour cet utilisateur et cette recette
        $existingMark = $markRepository->findOneBy([
            'user' => $this->getUser(),
            'recipe' => $recipe
        ]);

        if (!$existingMark) {
            // Persistez la note dans la base de données
            $manager->persist($markData);
            $this->addFlash('success', 'Vous avez noté cette recette.');

            $manager->flush();
            // Réinitialisez le formulaire pour éviter de le rendre dans la vue
            $markForm = $this->createForm(MarkType::class);
            return $this->redirectToRoute('recipe.show', ['id' => $recipe->getId()]);
        } else {

            $existingMark->setMark($markData->getMark());

            $this->addFlash('warning', 'Vous avez modifié la note de cette recette.');
            $manager->flush();
            return $this->redirectToRoute('recipe.show', ['id' => $recipe->getId()]);

        }
    }

    return $this->render('pages/recipe/show.html.twig', [
        'recipe' => $recipe,
        'markForm' => $markForm->createView(),
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

<?php

namespace App\Controller;

use App\Repository\RecipeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home.index', methods: ['GET'])]
    public function index(RecipeRepository $recipeRepository): Response
    {
        $recipes = $recipeRepository->findBy(['isPublic' => true], ['createdAt' => 'DESC']);

        // Mélangez l'ordre des recettes de manière aléatoire
        shuffle($recipes);

        // Limitez le nombre de recettes à afficher (par exemple, 5 recettes)
        $recipes = array_slice($recipes, 0, 3);

        return $this->render('pages/home.html.twig', [
            'recipes' => $recipes,
        ]);
    }
}

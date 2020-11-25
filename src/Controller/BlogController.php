<?php

namespace App\Controller;

use App\Entity\Article;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class BlogController extends AbstractController
{

    // Chaque méthode du controller est associé à une route bien spécifique
    // Lorsque nous envoyons la route '/blog' dans l'URL du navigateur, cela exécute automatiquement dans le controller, la méthode associée à celle-ci
    // Chaque méthode renvoie un template sur le navigateur en fonction de la route transmise

    /**
     * @Route("/blog", name="blog")
     */
    public function index(ArticleRepository $repo): Response
    {
        // On appelle la classe Repository de la classe Article
        // Une classe Repository permet uiquement de sélectionner des données en BDD
        // $repo = $this->getDoctrine()->getRepository(Article::class);
        // dump($repo);

        // findAll() est une méthode issue de la classe ArticleRepository et permet de sélectionner l'ensemble d'une table SQL (SELECT * FROM)
        $articles = $repo->findAll();
        dump($articles);

        return $this->render('blog/index.html.twig', [
            'articles' => $articles // On envoie sur le template, les articles sélectionnés en BDD
        ]);
    }

    /**
     * @Route("/", name="home")
     */
    public function home(): Response
    {
        return $this->render('blog/home.html.twig', [
            'title' => 'Bienvenue sur le blog Symfony',
            'age' => 16
        ]);
    }

    
    /**
     * @Route("/blog/new", name="blog_create")
     */
    public function create(Request $request, EntityManagerInterface $manager)
    {
        dump($request);

        if($request->request->count() > 0)
        {
            $article = new Article;
            $article->setTitle($request->request->get('title'))
                    ->setContent($request->request->get('content'))
                    ->setImage($request->request->get('image'))
                    ->setCreatedAt(new \DateTime());

            $manager->persist($article);
            $manager->flush();

            return $this->redirectToRoute('blog_show', [
                'id' => $article->getId()
            ]);

        }

        return $this->render('blog/create.html.twig');
    }

    // Nous utilisons le concept de route paramétrée pour faire en sorte de récupérer le bon ID du bon article
    // Nous avons définit le paramètre de type {id} directement dans la route

    /**
     * @Route("/blog/{id}", name="blog_show")
     */
    public function show(Article $article): Response
    {
        // On appelle le repository de la classe Article afin de sélectionner dans la table Article
        // $repo = $this->getDoctrine()->getRepository(Article::class);

        // La méthode find() issue de la classe ArticleRepository permet de sélectionner un article en BDD en fonction de son ID
        // $article = $repo->find($id);

        return $this->render('blog/show.html.twig', [
            'article' => $article // On envoie sur le templat l'article sélectionné en BDD
        ]);
    }

}

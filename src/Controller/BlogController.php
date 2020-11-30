<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Comment;
use App\Form\ArticleType;
use App\Form\CommentType;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class BlogController extends AbstractController
{

    // Chaque méthode du controller est associée à une route bien spécifique
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
     * @Route("blog/{id}/edit", name="blog_edit")
     */
    public function form(Article $article = null, Request $request, EntityManagerInterface $manager)
    {

        // Nous avons défini 2 routes différentes, une pour l'insertion et une pour la modification
        // Lorsque l'on envoie la route '/blog/new' dans l'URL, on définit un Article $article NULL, sinon Symfony tente de récupérer un article dn BDD et nous avosn une erreur
        // Lorque l'on envoie la route '/blo/{id}/edit', Symfony sélectionne en BDD la'article en fonction de l'ID transmis dans l'URL et écrase NULL par l'article récupéré en BDD dans l'objet $article

        // dump($request);

        // if($request->request->count() > 0)
        // {
        //     $article = new Article;
        //     $article->setTitle($request->request->get('title'))
        //             ->setContent($request->request->get('content'))
        //             ->setImage($request->request->get('image'))
        //             ->setCreatedAt(new \DateTime());

        //     $manager->persist($article);
        //     $manager->flush();

        //     return $this->redirectToRoute('blog_show', [
        //         'id' => $article->getId()
        //     ]);

        // }

        // On entre dans la condition IF seulement dans le cas de la création d'un nouvel article, c'est à dire pour la route '/blog/new', $article est NULL, on crée un nouvel objet Article
        // Dans le cas d'une modification, $article n'est pas NULL, il contient l'article sélectionné en BDD à modifier, on entre pas dans la condition IF

        if(!$article)
        {
            $article = new Article;   
        }

        // $article->setTitle("Titre à la con")
        //         ->setContent("Contenu à la con");

        // createFormBuilder() : methode issue de la classe BlogController permettant de créer un formulaire HTML qui sera lié à notre objet Article, c'est à dire que les champs du formulaire vont remplir l'objet Article

        // $form = $this->createFormBuilder($article)
        //             ->add('title') // Permet de créer des champs du formulaire
        //             ->add('content')
        //             ->add('image')
        //             ->getForm(); // Permet de valider le formulaire

        // On importe la classe ArticleType qui permet de générer le formuaire d'ajout / modification des articles
        // On précise que le formulaire a pour but de remplir les setters de l'objet article
        $form = $this->createForm(ArticleType::class, $article);

                    $form->handleRequest($request); // handleRequest permet de vérifier si tous les champs ont bien été remplis et la méthode va bindé l'objet Article, c'est à dire que si un titre de l'article a été saisi, il sera envoyé directement dans le bon setter de l'objet Article

                    dump($request); // On observe les données saisies dans le formulaire dans la propriété 'request'

                    // Si le formulaire a bien été soumis et que toutes les données sont valides, alors on entre dans la condition IF
                    if($form->isSubmitted() && $form->isValid())
                    {
                        // Si l'article n'a pas d'ID, cela veut dire que nous sommes dans le cas d'une insertion, alors on entre dans la condition IF
                        if(!$article->getId())
                        {
                        $article->setCreatedAt(new \DateTime()); // on rempli le setter de la date puisque nous n'avons pas de champs date dans le formulaire
                        }

                        $manager->persist($article); // On prépare l'insertion en BDD
                        $manager->flush(); // On exécture l'insertion en BDD

                        // Une fois l'insertion exécutée, on redirige vers le détail de l'article qui vient d'être inséré
                        return $this->redirectToRoute('blog_show', [
                            'id' => $article->getId() // On transmet dans la route, l'ID de l'article qui vient d'être inséré grâce au getter de l'objet Article
                        ]);
                    }

        return $this->render('blog/create.html.twig', [
            'formArticle' => $form->createView(),
            'editMode' => $article->getId() // Si l'ID existe, alors 'editMode' renvoie true. Il s'agit donc d'une modification.
        ]);
    }

    // Nous utilisons le concept de route paramétrée pour faire en sorte de récupérer le bon ID du bon article
    // Nous avons définit le paramètre de type {id} directement dans la route

    /**
     * @Route("/blog/{id}", name="blog_show")
     */
    public function show(Article $article, Request $commentRequest, EntityManagerInterface $commentManager): Response
    {

        $comment = new Comment;

        $commentForm = $this->createForm(CommentType::class, $comment);

        $commentForm->handleRequest($commentRequest);

        dump($commentRequest);

        if($commentForm->isSubmitted() && $commentForm->isValid())
        {
            $comment->setCreatedAt(new \DateTime())
                    ->setArticle($article);

            $commentManager->persist($comment);
            $commentManager->flush();

            $this->addFlash('success', "Le commentaire a bien été posté !");

            return $this->redirectToRoute('blog_show', [
                'id' => $article->getId()
            ]);
        }

      


        // On appelle le repository de la classe Article afin de sélectionner dans la table Article
        // $repo = $this->getDoctrine()->getRepository(Article::class);

        // La méthode find() issue de la classe ArticleRepository permet de sélectionner un article en BDD en fonction de son ID
        // $article = $repo->find($id);

        return $this->render('blog/show.html.twig', [
            'article' => $article, // On envoie sur le template l'article sélectionné en BDD
            'commentForm' => $commentForm->createView()
        ]);
    }

}

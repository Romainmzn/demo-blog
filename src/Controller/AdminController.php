<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleType;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    /**
     * @Route("/admin", name="admin")
     */
    public function index(): Response
    {
        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }

    /**
     * @Route("/admin/articles", name="admin_articles")
     */
    public function adminArticles(ArticleRepository $repo, EntityManagerInterface $manager): Response
    {
        $titres = $manager->getClassMetadata(Article::class)->getFieldNames();

        $articles = $repo->findAll();
        dump($articles);

        return $this->render('admin/admin_articles.html.twig',[
            'titres' => $titres,
            'articles' => $articles
        ]);
    }

    /**
     * @Route("/admin/article/new", name="admin_new_article")
     * @Route("/admin/{id}/edit-article", name="admin_edit_article")
     */
    public function adminForm(EntityManagerInterface $manager, Article $article = null, Request $request): Response
    {
        if(!$article)
        {
            $article = new Article;
        }

        $adminCreateForm = $this->createForm(ArticleType::class, $article);

        $adminCreateForm->handleRequest($request);

        if($adminCreateForm->isSubmitted() && $adminCreateForm->isValid())
        {
            if(!$article->getCreatedAt())
            {
                $article->setCreatedAt(new \DateTime());
            }

            $manager->persist($article);
            $manager->flush();

            $this->addFlash('success', "L'article a bien été enregistré");

            return $this->redirectToRoute('admin_articles');
        }

        return $this->render('admin/admin_create.html.twig',[
            'adminCreateForm' => $adminCreateForm->createView(),
            'editMode' => $article->getId()
        ]);
    }
}

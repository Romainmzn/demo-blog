<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Category;
use App\Form\ArticleType;
use App\Form\CategoryType;
use App\Repository\ArticleRepository;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

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
    public function adminFormArticle(EntityManagerInterface $manager, Article $article = null, Request $request): Response
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

    /**
     * @Route("admin/{id}/delete-article", name="admin_delete_article")
     */
    public function deleteArticle(Article $article, EntityManagerInterface $manager)
    {
        $manager->remove($article);
        $manager->flush();

        $this->addFlash('success', "L'article a bien été supprimé");

        return $this->redirectToRoute('admin_articles');
    }

    /**
     * @Route("admin/category", name="admin_categories")
     */
    public function adminFormCategory(CategoryRepository $repo, EntityManagerInterface $manager): Response
    {
        $titres = $manager->getClassMetadata(Category::class)->getFieldNames();

        $categories = $repo->findAll();
        dump($categories);

        return $this->render('admin/admin_categories.html.twig', [
            'titres' => $titres,
            'categories' => $categories
        ]);
    }

    /**
     * @Route("admin/category/new", name="admin_create_category")
     * @Route("admin/category/{id}/edit", name="admin_edit_category")
     */
    public function adminCategory(Request $request, Category $category = null, EntityManagerInterface $manager)
    {
        if(!$category)
        {
            $category = new Category;
        }

        dump($category);

        $categoryForm = $this->createForm(CategoryType::class, $category);
        
        $categoryForm->handleRequest($request);

        if($categoryForm->isSubmitted() && $categoryForm->isValid())
        {
            $manager->persist($category);
            $manager->flush();

            $this->addFlash('success', 'Catégorie enregistrée avec succès');

            return $this->redirectToRoute('admin_categories');
        }

        return $this->render('admin/admin_create_category.html.twig', [
            'categoryForm' => $categoryForm->createView(),
            'editMode' => $category->getTitle()
        ]);
    }

    /**
     * @Route("admin/category/{id}/delete", name="admin_delete_category")
     */
    public function deleteCategory(Category $category, EntityManagerInterface $manager)
    {
        if($category->getArticles()->isEmpty())
        {
            $manager->remove($category);
            $manager->flush();

            $this->addFlash('success', 'Catégorie supprimée avec succès');
        }
        else
        {
            $this->addFlash('danger', 'Impossible de supprimer la catégorie car des articles y sont associés.');
        }

        return $this->redirectToRoute('admin_categories');
    }
}

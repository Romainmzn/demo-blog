<?php

namespace App\DataFixtures;

use App\Entity\Article;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class ArticleFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        for($i = 1; $i <= 10; $i++)
        {
            // Pour chaque tour de boucle, on crée un objet Article vide
            $article = new Article;

            // On renseigne tous les setters de l'entité Article
            $article->setTitle("Titre de l'article n°$i")
                    ->setContent("<p>Contenu de l'article n°$i</p>")
                    ->setImage("https://picsum.photos/200/300?random=$i")
                    ->setCreatedAt(new \DateTime());

            // ObjectManager permet de manipuler les lignes dans la BDD (INSERT, UPDATE, DELETE)
            $manager->persist($article); // prépare la requête d'insertion en BDD
        }

        $manager->flush(); // libère l'insertion en BDD (exécute)
    }
}

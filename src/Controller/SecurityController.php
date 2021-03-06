<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    /**
     * @Route("/inscription", name="security_registration")
     */
    public function registration(Request $request, EntityManagerInterface $manager, UserPasswordEncoderInterface $encoder, AuthorizationCheckerInterface $authChecker): Response
    {

        if($authChecker->isGranted('ROLE_USER'))
        {
            return $this->redirectToRoute('blog');
        }

        $user = new User;

        dump($request);
        
        $formRegistration = $this->createForm(RegistrationType::class, $user);

        $formRegistration->handleRequest($request);

        if($formRegistration->isSubmitted() && $formRegistration->isValid())
        {

            // encorePassword() est une méthode issue de l'interface UserPasswordEncoreInterface permettant d'encoder le mot de passe
            // Pour encoder le mot de passe, il faut que notre User implémente l'interface UserInterface et nous devons déclarer obligatoirement 5 méthodes dans l'entité: getPassword(), getUsernam(), getSalt(), getRoles() et eraseCredentials()
            $hash = $encoder->encodePassword($user, $user->getPassword());

            $user->setPassword($hash);
            $user->setRoles(["ROLE_USER"]); // On définit un ROLE_USER à chaque nouvelle

            $manager->persist($user);
            $manager->flush();

            return $this->redirectToRoute('security_login');
        }

        return $this->render('security/registration.html.twig', [
            'formRegistration' => $formRegistration->createView()
        ]);
    }

    /**
     * @Route("/connexion", name="security_login")
     */
    public function login(AuthenticationUtils $authenticationUtils, AuthorizationCheckerInterface $authChecker): Response
    {

        if($authChecker->isGranted('ROLE_USER'))
        {
            return $this->redirectToRoute('blog');
        }

        // Récupération du message d'erreur en cas de mauvaise connexion
        $error = $authenticationUtils->getLastAuthenticationError();

        // Récupération du dernier username (email) saisi par l'internaute en cas d'erreur
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'error' => $error,
            'last_username' => $lastUsername
        ]);
    }

    /**
     * @Route("/deconnexion", name="security_logout")
     */
    public function logout()
    {
        // Cette méthode ne retourne rien, il nous suffit d'avoir une route pour se déconnecter (la route est appelée dans le fichier security.yaml)
    }
}

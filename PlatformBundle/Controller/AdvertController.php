<?php

//-- src/OC/PlatformBundle/Controller/AdvertController.php

namespace OC\PlatformBundle\Controller;

use OC\PlatformBundle\Entity\Advert;
use OC\PlatformBundle\Entity\Image;
use OC\PlatformBundle\Entity\Application;
use OC\PlatformBundle\Entity\AdvertSkill;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

//-- Les formulaires
use OC\PlatformBundle\Form\AdvertType;
use OC\PlatformBundle\Form\AdvertEditType;


class AdvertController extends Controller
{
    public function indexAction($page)
    {
        //-- Une page doit être sup à 1
        if ($page < 1){
            //-- Déclanche exception 
            //-- Affichige une page 404
            throw new NotFoundHttpException('Page "'. $page .'" inexistante');
        }
        
       // Ici je fixe le nombre d'annonces par page à 3
        // Mais bien sûr il faudrait utiliser un paramètre, et y accéder via $this->container->getParameter('nb_per_page')
        $nbPerPage = 3;

        // On récupère notre objet Paginator
        $listAdverts = $this->getDoctrine ()
                ->getManager ()
                ->getRepository ('OCPlatformBundle:Advert')
                ->getAdverts ($page, $nbPerPage)
        ;

        // On calcule le nombre total de pages grâce au count($listAdverts) qui retourne le nombre total d'annonces
        $nbPages = ceil (count ($listAdverts) / $nbPerPage);

        // Si la page n'existe pas, on retourne une 404
        if ($page > $nbPages)
        {
            throw $this->createNotFoundException("La page ".$page." n'existe pas.");
        }

        // On donne toutes les informations nécessaires à la vue
        return $this->render ('OCPlatformBundle:Advert:index.html.twig', array(
                    'listAdverts' => $listAdverts,
                    'nbPages' => $nbPages,
                    'page' => $page
        ));
    }
    
    /*-----------------------------------------------------
     * public function viewAction($id)
     */
    
    public function viewAction($id)
    {
        
       // On récupère l'EntityManager
        $em = $this->getDoctrine ()->getManager ();

        // Pour récupérer une annonce unique : on utilise find()
        $advert = $em->getRepository ('OCPlatformBundle:Advert')->find ($id);

        // On vérifie que l'annonce avec cet id existe bien
        if ($advert === null)
        {
            throw $this->createNotFoundException ("L'annonce d'id " . $id . " n'existe pas.");
        }

        // On récupère la liste des advertSkill pour l'annonce $advert
        $listAdvertSkills = $em->getRepository ('OCPlatformBundle:AdvertSkill')->findByAdvert ($advert);

        // Puis modifiez la ligne du render comme ceci, pour prendre en compte les variables :
        return $this->render ('OCPlatformBundle:Advert:view.html.twig', array(
                    'advert' => $advert,
                    'listAdvertSkills' => $listAdvertSkills,
        ));
    }

    /*-------------------------------------------------------
     * public function addAction(Request $request)
     */
    
     public function addAction(Request $request)
    {
         $advert = new Advert();
        
       
        // À partir du formBuilder, on génère le formulaire
         // À partir du formBuilder, on génère le formulaire

        //$form = $this->get('form.factory')->create(new AdvertType, $advert);
        $form = $this->createForm(new AdvertType, $advert);

        // On fait le lien Requête <-> Formulaire
        // À partir de maintenant, la variable $advert contient les valeurs entrées dans le formulaire par le visiteur
        $form->handleRequest ($request);

        // On vérifie que les valeurs entrées sont correctes
        // (Nous verrons la validation des objets en détail dans le prochain chapitre)
        if ($form->isValid ())
        {
            //-- recup de l'image
            $advert->getImage()->upload();
            // On l'enregistre notre objet $advert dans la base de données, par exemple
            $em = $this->getDoctrine ()->getManager ();
            $em->persist ($advert);
            $em->flush ();

            $request->getSession ()->getFlashBag ()->add ('notice', 'Annonce bien enregistrée.');

            // On redirige vers la page de visualisation de l'annonce nouvellement créée
            return $this->redirect ($this->generateUrl ('oc_platform_view', array('id' => $advert->getId ())));
        }

        // À ce stade, le formulaire n'est pas valide car :
        // - Soit la requête est de type GET, donc le visiteur vient d'arriver sur la page et veut voir le formulaire
        // - Soit la requête est de type POST, mais le formulaire contient des valeurs invalides, donc on l'affiche de nouveau
        return $this->render ('OCPlatformBundle:Advert:add.html.twig', array(
                    'form' => $form->createView (),
        ));
    }

    /*-------------------------------------------------------
     * public function editAction($id, Request $request)
     */
    
    public function editAction($id, Request $request)
    {
        
         // On récupère l'EntityManager
        $em = $this->getDoctrine ()->getManager ();

        // On récupère l'entité correspondant à l'id $id
        $advert = $em->getRepository ('OCPlatformBundle:Advert')->find ($id);

        // Si l'annonce n'existe pas, on affiche une erreur 404
        if ($advert == null)
        {
            throw $this->createNotFoundException ("L'annonce d'id " . $id . " n'existe pas.");
        }
        
        $form = $this->createForm(new AdvertEditType(), $advert);
        if ($form->handleRequest($request)->isValid()) {
            // Inutile de persister ici, Doctrine connait déjà notre annonce
            $em->flush();
            $request->getSession()->getFlashBag()->add('notice', 'Annonce bien modifiée.');
            return $this->redirect($this->generateUrl('oc_platform_view', array('id' => $advert->getId())));
        }

        // Ici, on s'occupera de la création et de la gestion du formulaire

        return $this->render('OCPlatformBundle:Advert:edit.html.twig', array(
                    'form' => $form->createView(),
                    'advert' => $advert // Je passe également l'annonce à la vue si jamais elle veut l'afficher
        ));
    }
    
    /*-----------------------------------------------------
     * public function deleteAction($id)
     * para : id
     */

    public function deleteAction($id, Request $request)
    {
       // On récupère l'EntityManager
        $em = $this->getDoctrine ()->getManager ();

        // On récupère l'entité correspondant à l'id $id
        $advert = $em->getRepository ('OCPlatformBundle:Advert')->find ($id);

        // Si l'annonce n'existe pas, on affiche une erreur 404
        if ($advert == null)
        {
            throw $this->createNotFoundException ("L'annonce d'id " . $id . " n'existe pas.");
        }

        if ($request->isMethod('POST'))
        {
            // Si la requête est en POST, on deletea l'article

            $request->getSession ()->getFlashBag ()->add ('info', 'Annonce bien supprimée.');

            // Puis on redirige vers l'accueil
            return $this->redirect ($this->generateUrl ('oc_platform_home'));
        }

        // Si la requête est en GET, on affiche une page de confirmation avant de delete
        return $this->render ('OCPlatformBundle:Advert:delete.html.twig', array(
                    'advert' => $advert
        ));
    }
    
    /*-----------------------------------------------------
     * public function menuAction()
     */

    public function menuAction($limit = 3)
    {
       $listAdverts = $this->getDoctrine ()
                ->getManager ()
                ->getRepository ('OCPlatformBundle:Advert')
                ->findBy (
                array(), // Pas de critère
                array('date' => 'desc'), // On trie par date décroissante
                $limit, // On sélectionne $limit annonces
                0                        // À partir du premier
        );

        return $this->render('OCPlatformBundle:Advert:menu.html.twig', array(
                    // Tout l'intérêt est ici : le contrôleur passe
                    // les variables nécessaires au template !
                    'listAdverts' => $listAdverts
        ));
    }//-- Fin menuAction()
    
    /*-----------------------------------------------------
     * public function testAction()
     */
    
    public function testAction()
    {
        $repository = $this
                ->getDoctrine()
                ->getManager()
                -getRepository(OCPlatformBundle::Advert);
        
        $listAdvert = $repository->myFindAll();
    }

}//-- Fin class

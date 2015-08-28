<?php

namespace CPAlex\DashboardBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use UJM\ExoBundle\Repository\PaperRepository;
use UJM\ExoBundle\Entity\Paper;
use Claroline\CoreBundle\Repository\UserRepository;
use Claroline\CoreBundle\Entity\User;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;

use Claroline\CoreBundle\Library\Resource\ResourceCollection;
use UJM\ExoBundle\Validator\Constraints\isNotEmpty;
use UJM\ExoBundle\Validator\Constraints\isValidMark;

class DashboardController extends Controller
{
    /**
     * @EXT\Route("/index", name="cpalex_dashboard_index")
     * @EXT\Template
     *
     * @return Response
     */
    public function indexAction()
    {
        throw new \Exception('hello');
    }

    /**
     * Lists all Paper entities.
     *
     * @access public
     *
     * @param integer $page for the pagination, page destination
     * @param boolean $all for use or not use the pagination
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */

    public function FilterAction($page=1)
    {
        //default values
        $userId=0;
        $exerciseId=0;
        $copiemanquante=0;
        $tentative=0;
        //case, form sent
        //On récupère le service request
        $request = $this->container->get('request');
        if ($request->isMethod('POST'))
        {
            $userId = $request->get('selectuser');
            $exerciseId = $request->get('selectexo');
        }

        $em=$this->getDoctrine()->getManager();
        $allpaper=$em->getRepository('UJMExoBundle:Paper')->findAll();
        $exercise=$em->getRepository('UJMExoBundle:Exercise')->findById($allpaper);
        $user=$em->getRepository('ClarolineCoreBundle:User')->findById($allpaper);
        $nbpaper=$em->getRepository('UJMExoBundle:Paper')->countPapers($exerciseId);
        $check=0;
        $usert = $this->container->get('security.token_storage')->getToken()->getUser();

        if($userId==0 && $exerciseId==0)  //Cas ou on choisie dans le menu déroulant tous les exo et tous les users
        {
            $paper=$em->getRepository('UJMExoBundle:Paper')->findAll();
        }
        elseif($userId !=0 && $exerciseId==0) //Cas ou on choisie tous les exo pour un utilisateurs particulié
        {
            $paper=$em->getRepository('UJMExoBundle:Paper')->getPaperUser($userId);
        }
        elseif($userId ==0 && $exerciseId!=0) // Cas ou on choisi tous les user pour un exercice particulié
        {
            $paper=$em->getRepository('UJMExoBundle:Paper')->getExerciseAllPapers($exerciseId);
            $check=1;
        }
        else    //Cas ou on choisie un utilisateur et un exercice particulié
        {

            $paper=$em->getRepository('UJMExoBundle:Paper')->getExerciseUserPapers($userId, $exerciseId, $finished = false);
            $tentative=$this->container->get('ujm.exercise_services')->getNbPaper($userId, $exerciseId, $finished = false);
            $check=2;


        }
        //Une fois le cas définie, on récupère les scores pour chaque copie afin de les exploiter dans la vue
        $tab=array();
        if($paper!=NULL){
        foreach ($paper as $p)
        {
            $arrayMarkPapers[$p->getId()] = $this->container->get('ujm.exercise_services')->getInfosPaper($p);

        }
}
        else {
            $arrayMarkPapers=NULL;
            $copiemanquante=1;
        }

        //essai classement

        foreach ($paper as $p)
        {
            $tab[$p->getId()] = $this->container->get('ujm.exercise_services')->getExercisePaperTotalScore($p);

        }

        // Partie pagination en test
        $nbUserPaper = count($paper);
        $max = 4; // Max per page
        $adapter = new ArrayAdapter($paper);
        $pagerfanta = new Pagerfanta($adapter);

        try {
            $paperst = $pagerfanta
                ->setMaxPerPage($max)
                ->setCurrentPage($page)
                ->getCurrentPageResults();
        } catch (\Pagerfanta\Exception\NotValidCurrentPageException $e) {
            throw $this->createNotFoundException("Cette page n'existe pas.");
        }

        return $this->render(
            'CPAlexDashboardBundle::vue.html.twig',
            array(

                'arrayMarkPapers'   => $arrayMarkPapers,
                'paper'             => $paper,
                'exercise'          => $exercise,
                'user'              => $user,
                'allpaper'          => $allpaper,
                //'check'             => $check,
                'pager'             => $pagerfanta,
                'nbUserPaper'       => $nbUserPaper,
                'paperst'           =>$paperst,
                'exoid'             => $exerciseId,
                'userid'            => $userId,
                'nbpaper'           => $nbpaper,
                'copiemanquante'    =>$copiemanquante,
                'tentative'         =>$tentative
            )
        );
    }

    public function FilterAjaxAction()
    {


        //default values
       $userId=0;
       $exerciseId=0;
        //Variables exploitées pour la vue

        //case, form sent
        //On récupère le service request
        $request = $this->container->get('request');
        if ($request->isMethod('POST')&& $request->isXmlHttpRequest())
        {
            $userId = $request->get('selectuser');
            $exerciseId = $request->get('selectexo');
        }

        $em=$this->getDoctrine()->getManager();
        $allpaper=$em->getRepository('UJMExoBundle:Paper')->findAll();
        $exercise=$em->getRepository('UJMExoBundle:Exercise')->findById($allpaper);
        $user=$em->getRepository('ClarolineCoreBundle:User')->findById($allpaper);
        $check=0;
        $nbpaper=0;
        $tentative=0;// Nombre de tenative de l'étudiant initialisé à zéro
        $nbcopie=0;// Nombre de copie initialisé à zéro;

        if($userId==0 && $exerciseId==0)  //Cas ou on choisie dans le menu déroulant tous les exo et tous les users
        {
            $paper=$em->getRepository('UJMExoBundle:Paper')->findAll();
        }
        elseif($userId !=0 && $exerciseId==0) //Cas ou on choisie tous les exo pour un utilisateurs particulié
        {
            $paper=$em->getRepository('UJMExoBundle:Paper')->getPaperUser($userId);
        }
        elseif($userId ==0 && $exerciseId!=0) // Cas ou on choisi tous les user pour un exercice particulié
        {
            $paper=$em->getRepository('UJMExoBundle:Paper')->getExerciseAllPapers($exerciseId);
            $check=1;
        }
        else    //Cas ou on choisie un utilisateur et un exercice particulié
        {
            $paper=$em->getRepository('UJMExoBundle:Paper')->getExerciseUserPapers($userId, $exerciseId, $finished = false);
            $tentative=$this->container->get('ujm.exercise_services')->getNbPaper($userId, $exerciseId, $finished = false);
        }

        $vars=array();
        foreach ($paper as $p)
        {
            $arrayMarkPapers[$p->getId()] = $this->container->get('ujm.exercise_services')->getInfosPaper($p);
            $nbcopie=count($paper);
           // $note[]=array("note"=>['scorePaper']);
            $vars[]=array(
                "userfirstname"=> $p->getUser()->getfirstname(),
                "userlastname"=> $p->getUser()->getLastname(),
                "exercise"=>    $p->getExercise()->getTitle(),
                "start"=> $p->getStart(),
                "end"=> $p->getEnd(),
                "score"=>$arrayMarkPapers[$p->getId()],
            );
        }

//$vars['paper']= $paper;

        $response = new JsonResponse(
            array(
                'data'=>$vars,
                'nbcopie'=>$nbcopie,
                'tentative'=>$tentative,
        ));
        return $response;

//        $nbUserPaper = count($paper);
//
//        $max = 10; // Max per page
//        $adapter = new ArrayAdapter($paper);
//        $pagerfanta = new Pagerfanta($adapter);
//
//        try {
//            $paperst = $pagerfanta
//                ->setMaxPerPage($max)
//                ->setCurrentPage($page)
//                ->getCurrentPageResults();
//        } catch (\Pagerfanta\Exception\NotValidCurrentPageException $e) {
//            throw $this->createNotFoundException("Cette page n'existe pas.");
//        }
    }
}

<?php

namespace Acme\TabBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Cookie;
use Acme\UserBundle\Enity\User;
use Acme\TabBundle\Entity\Link;
use Acme\TabBundle\Entity\Category;
// use Acme\TabBundle\Controller\ParseurRSS;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="index")
     * @Template()
     */
    public function indexAction()
    {
        $user = $this->container->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getEntityManager();
        // $cats = $em->getRepository('AcmeTabBundle:Category')->findAll();

        return $this->render('AcmeTabBundle:Default:index.html.twig',array(
        	'user' => $user,
        	// 'cats' => $cats
        	)
        );
    } 

	/**
     * Récupère le nom de domaine raccourci à partir d'une url
     *
     * @param string $url
     * @return string
     */
    public static function get_domain($url) {
    
    	$nowww = preg_replace('/www./i','',$url);
    	$domain = parse_url($nowww);
    
    	if(!empty($domain["host"]))
    		return $domain["host"];
    	else
    		return $domain["path"];
    }

     /**
     * @Route("/api", name="api")
     * @Template()
     */
    public function ajaxAction()
    {
        $user = $this->container->get('security.context')->getToken()->getUser();
		
		if(!$user)
			return new Response(json_encode(Array('status'=> 'Error',
			                                  	  'error' => 'Disconnected')));
			
		$request = $this->getRequest();
		
		if (!$request->isXmlHttpRequest())
			return new Response('666 The number of the beast.');
		
		$em = $this->getDoctrine()->getEntityManager();
		
		$module = $request->query->get('module');
		$action = $request->query->get('action');
				
		$actionInvalide = new Response(json_encode(Array('status'=>'error', 'error' => 'Not a valid Action')));
		
		switch($module) {
			default: 
				return new Response(json_encode(Array('status'=> 'Error',
				                                      'error' => 'Invalid Module')));

				case 'addLink':

				$name = $request->query->get('name');
				$linksend = $request->query->get('link');
				$catname = $request->query->get('category');

				if(!isset($name) or !isset($linksend))
					return new Response(json_encode(Array(
						'status'=> 'Error',
						'error' => 'Please fill everything.'
						)));

				if($af = $em->getRepository('AcmeTabBundle:Link')->findBy(array('user' => $user->getId(),'link'=>$linksend)) or $a = $em->getRepository('AcmeTabBundle:Link')->findBy(array('user' => $user->getId(),'name'=>$name)))
					return new Response(json_encode(Array(
						'status'=> 'Error',
						'error' => 'Don\'t duplicate RSS please. This link (his name or his URL) already exists.'
						)));


				$link = new Link();
				$link->setName($name);
				$link->setUser($user);
				$link->setLink($linksend);
				if ($cat = $em->getRepository('AcmeTabBundle:Category')->findOneByName($catname)) {
					$link->setCategory($cat);
				}else{
					$cat = new Category();
					$cat->setName($catname);
					$em->persist($cat);
					$em->flush();
					$cat = $em->getRepository('AcmeTabBundle:Category')->findOneByName($catname);
					$link->setCategory($cat);
				}
				$em->persist($link);
				$em->flush();
					
				return new Response(json_encode(Array('status'=> 'OK')));



				case 'getRss':

				$linksend = $request->query->get('link');
				$parseur = new ParseurRSS();
				$parsed = $parseur->parser($linksend, "RSS");



				case 'getAllRss':

				$parsed = array();
				$hour_ago = array();
				for ($i=0; $i < 10; $i++) { 
					$hour_ago[$i] = strtotime('-'.$i.' hour');
				}

				$af = $em->getRepository('AcmeTabBundle:Link')->findBy(array('user' => $user->getId()));

				foreach($af as $f){
					if($f->getLink() != ''){

					if($rss = @file_get_contents($f->getLink())){

					// $cat = $em->getRepository('AcmeTabBundle:Category')->findOneById($f->getCategory());

					$parseur = new RssController($rss);
					$favicon = 'http://www.google.com/s2/favicons?domain='.$this->get_domain($f->getLink());
				
					$parsed[]=array('id' => $f->getId(), 'category' => $f->getCategory(), 'name' => $f->getName(), 'link'=> $f->getLink(), 'rss' => $parseur->exportItems(),'favicon'=>$favicon);
				
					}}
				}

				return $this->render('AcmeTabBundle:Default:loop.html.twig',array(
        				'parsed' => $parsed,
        				'hour_ago' => $hour_ago,
        			));

				case 'deleteLink':

					$id = $request->query->get('id');

					if($target = $em->getRepository('AcmeTabBundle:Link')->find($id)){
						$em->remove($target);
						$em->flush();

						return new Response(json_encode(Array('status'=> 'OK')));

					}else{

						return new Response(json_encode(Array('status'=> 'KO')));
					}

        } break;
    }
}

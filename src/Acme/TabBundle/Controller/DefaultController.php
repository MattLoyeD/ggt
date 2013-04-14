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

        return $this->render('AcmeTabBundle:Default:index.html.twig',array(
        	'user' => $user,
        	)
        );
    } 

    /**
     * @Route("/help", name="index_first_connexion")
     * @Template()
     */
    public function indexNewUserAction()
    {
        $user = $this->container->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getEntityManager();
        return $this->render('AcmeTabBundle:Default:index.html.twig',array(
        	'user' => $user,
        	'flash' => 'first_connexion',
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
    	$nowww = preg_replace('/rss./i','',$nowww);
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
/*		
		if (!$request->isXmlHttpRequest())
			return new Response('666 The number of the beast.');*/
		
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

				case 'getRssTitle':

				$url = $request->query->get('url');
				$string = file_get_contents($url);
				if ('UTF-8' != mb_detect_encoding($string)) {
				    $string = mb_convert_encoding($string, 'HTML-ENTITIES', "UTF-8");
				}
			    $dom = new \DOMDocument();
			    // hack to preserve UTF-8 characters
			    $dom->preserveWhiteSpace = false;
			    $dom->encoding = 'UTF-8';
				$dom->loadXml($string);

				$xpath = new \DOMXPath($dom); 

				$query = '//channel/title'; 
				$query = $xpath->query($query); 
				// find first item 
				$title_xml = $query->item(0)->nodeValue; 

				return new Response($title_xml);

				case 'getRss':

				$linksend = $request->query->get('link');
				$stringurl = $em->getRepository('AcmeTabBundle:Link')->findOneById($linksend);
				//var_dump($stringurl);

				$string = file_get_contents($stringurl->getLink());

				if ('UTF-8' != mb_detect_encoding($string)) {
				    $string = mb_convert_encoding($string, 'HTML-ENTITIES', "UTF-8");
				}
				$array = new NewRssController;
				$a2 = $array->createArray($string);
				echo "<pre>";
				var_dump($a2);
				echo "</pre>";
				  /*  $dom = new \DOMDocument();
			    // hack to preserve UTF-8 characters
			    $dom->preserveWhiteSpace = false;
			    $dom->encoding = 'UTF-8';
				$dom->loadXml($string);

				$xpath = new \DOMXPath($dom); */


				//$query = '//channel/title'; 
				//$query = $xpath->query($query); 
				// find first item 
				//$title_xml = $query->item(0)->nodeValue; 

				/*$query1 = '//channel/item/title';
				$query2 = '//channel/item/link';
				$query3 = '//channel/item/pubDate';
				$query4 = '//channel/item/description';
				$q1 = $xpath->query($query1);
				echo $q1->nodeValue;
				$q2 = $xpath->query($query2);
				$q3 = $xpath->query($query3);
				$q4 = $xpath->query($query4);
				for ($i = 0; $i < 10; $i++) {
					$content[] = array(
						'title' => $query1->nodeValue , 
						'link' => $query2->nodeValue , 
						'pubDate' => $query3->nodeValue , 
						'description' => $query4->nodeValue 
						);
				}*/
				/*$elements = $xpath->query("//channel/item");
				foreach ($elements as $element) {
				    $nodes = $element->childNodes;

				    	# code...
					foreach ($nodes as $node) {
				    	for ($i=0; $i < count($nodes) ; $i++) { 
					    	  $content[$element->nodeName][$i][$node->nodeName] = $node->nodeValue;
					    	}
				    	}
				  }
				  echo "<pre>";
				  var_dump($content);
				  echo "</pre>";*/
			    return new Response(json_encode(array('content' => 'ok')));


				case 'getAllRss':


				date_default_timezone_set('Europe/Paris');
				$parsed = array();
				$hour_ago = array();
				for ($i=0; $i < 11; $i++) { 
					$hour_ago[$i] = strtotime('-'.$i.' hour');
				}

				$af = $em->getRepository('AcmeTabBundle:Link')->findBy(array('user' => $user->getId()));

				foreach($af as $f){
					if($f->getLink() != ''){

					if($rss = @file_get_contents($f->getLink())){

					// $rss = tidy_repair_string($rss,array('input-xml' => 1));
					// $cat = $em->getRepository('AcmeTabBundle:Category')->findOneById($f->getCategory());

					if($parseur = new RssController($rss)){
					$favicon = 'http://www.google.com/s2/favicons?domain='.$this->get_domain($f->getLink());
				
					$parsed[]=array('id' => $f->getId(), 'category' => $f->getCategory(), 'name' => $f->getName(), 'link'=> @$f->getLink(), 'rss' => $parseur->exportItems(),'favicon'=>$favicon);
				
					}}}
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

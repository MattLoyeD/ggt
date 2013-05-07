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
use Acme\TabBundle\Entity\RSSCache;
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


    public function getLasts ()
    {
    	$rssCached = $em->getRepository('AcmeTabBundle:RSSCache')->findBy(array('link' =>$id_rss),array('last_update' => 'DESC'));

		return array( 'content' => $em->getRepository('AcmeTabBundle:RSSCache')->findBy(array('last_update' => 'DESC')),'from_cache' => true );			
    }

    public function getRss($id_rss){

		// Preserve useless things
		if(empty($id_rss))
			return new Response(json_encode(array("error"=>"Nothing to analyse")));

		$em = $this->getDoctrine()->getEntityManager();
		
		if(!$stringurl = $em->getRepository('AcmeTabBundle:Link')->findOneById($id_rss))
			return new Response(json_encode(array("error"=>"Does not exist")));
		
		$rssCached = $em->getRepository('AcmeTabBundle:RSSCache')->findBy(array('link' =>$id_rss),array('last_update' => 'DESC'));

		if(isset($rssCached[0]))
			if(is_object($rssCached[0]))
			{
				if($rssCached[0]->getLastUpdate()->format('Y-m-d H:i:s') > date("Y-m-d H:i:s", strtotime("now + 3 minutes")))
					return array( 'content' => $em->getRepository('AcmeTabBundle:RSSCache')->findBy(array('link' =>$id_rss),array('last_update' => 'DESC')),'from_cache' => true );			
			}

		if(!$string = file_get_contents($stringurl->getLink()))
			return new Response(json_encode(array("error"=>'URL Unreachable')));


		// Hack for fucking encoding problems
		if ('UTF-8' != mb_detect_encoding($string)) {
		    $string = mb_convert_encoding($string, 'HTML-ENTITIES', "UTF-8");
		}
	
		// Let's go to the mallllll today
		$array = new NewRssController;
		$a2 = $array->createArray($string);

		for ($i=0; $i < count($a2['rss']['channel']['item']) ; $i++) { 
			
			// Verify before doing anything it exists
			if(!$link_exist = $em->getRepository('AcmeTabBundle:RSSCache')->findOneByUrl($a2['rss']['channel']['item'][$i]['link'])){
		
				$rssCache[$i] = new RSSCache;
				$rssCache[$i]->setLink($stringurl);

				$content = $a2['rss']['channel']['item'][$i];
				
				if(!is_array($content['title'])){
					$rssCache[$i]->setTitle($content['title']);
				}else{
					$rssCache[$i]->setTitle($content['title']['@cdata']);
				}				
				
				if(!is_array($content['description'])){
					$rssCache[$i]->setContent(substr(strip_tags($content['description']),0,1000));
				}else{
					$rssCache[$i]->setContent(substr(strip_tags($content['description']['@cdata']),0,1000));
				}				
				
				if(!is_array($content['link'])){
					$rssCache[$i]->setUrl($content['link']);
				}else{
					$rssCache[$i]->setUrl($content['link']['@cdata']);
				}				
				
				if(!is_array($content['pubDate'])){
					$date = date_create(date("Y-m-d H:i", strtotime($a2['rss']['channel']['item'][$i]['pubDate'])));
				}else{
					$date = date_create(date("Y-m-d H:i", strtotime($a2['rss']['channel']['item'][$i]['pubDate']['@cdata'])));
				}				
				$rssCache[$i]->setDate($date);
				
				if(isset($a2['rss']['channel']['item'][$i]['enclosure'])){
					if(!is_array($content['enclosure']['@attributes']['url'])){
						$rssCache[$i]->setImage($a2['rss']['channel']['item'][$i]['enclosure']['@attributes']['url']);
					}else{
						$rssCache[$i]->setImage($a2['rss']['channel']['item'][$i]['enclosure']['@attributes']['url']['@cdata']);
					}				
				}
								
				// Care w/ images bitch
				if(isset($a2['rss']['channel']['item'][$i]['enclosure']))
					$rssCache[$i]->setImage($a2['rss']['channel']['item'][$i]['enclosure']['@attributes']['url']);

				$em->persist($rssCache[$i]);
		
			}
		}

		if(isset($rssCache))
			$em->flush();
		
		$rssCached = $em->getRepository('AcmeTabBundle:RSSCache')->findBy(array('link' =>$id_rss),array('last_update' => 'DESC'));

		return array( 'content' => $rssCached, 'from_cache' => false	);			
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

				/*case 'getTest':
				$module = 'getRss';
				$link=1;
				$test = DefaultController::ajaxAction('module=getRss&link=1');
				var_dump($test);
				return new Response(print_r($test));*/

				case 'getRss':
				
				//echo $request->query->get('link');
				$render = DefaultController::getRss($request->query->get('link'));

				date_default_timezone_set('Europe/Paris');
				$parsed = array();
				$hour_ago = array();
				for ($i=0; $i < 11; $i++) { 
					$hour_ago[$i] = strtotime('-'.$i.' hour');
				}
				//return print_r($render);
			    return $this->render('AcmeTabBundle:Default:loop_v2.html.twig',
			    	array(
			    		'xml' => $render['content'],
			     		'from_cache' => $render['from_cache'],
			     		'hour_ago' => $hour_ago
			     	));

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
						if($rss = $this->getRss($f->getId())){

						$favicon = 'http://www.google.com/s2/favicons?domain='.$this->get_domain($f->getLink());
					
						$parsed[]=array(
							'id' => $f->getId(),
							'category' => $f->getCategory(), 
							'name' => $f->getName(), 
							'link'=> $f->getLink(), 
							'rss' => $rss,
							'favicon'=>$favicon
							);					
						}
					}
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

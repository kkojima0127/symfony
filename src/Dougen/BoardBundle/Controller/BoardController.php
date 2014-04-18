<?php

namespace Dougen\BoardBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use Dougen\BoardBundle\Entity\Thread;
use Dougen\BoardBundle\Entity\Post;

class BoardController extends Controller
{
    /**
     * @Route("/index", name="_index")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $threads = $this->getDoctrine()
            ->getRepository('DougenBoardBundle:Thread')
            ->findAll();

        $thread = new Thread();
        $thread->setTitle('サンプルスレッド');
        $form = $this->createFormBuilder($thread)
            ->add('title', 'text')
            ->getForm();

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $now = new \DateTime();
                $thread->setCreated($now);
                $thread->setModified($now);
                $em = $this->getDoctrine()->getEntityManager();
                $em->persist($thread);
                $em->flush();
                
                return $this->redirect($this->generateUrl('_index'));
            }
        }
        
        return array('form' => $form->createView(), 'threads' => $threads);
        /*
        if ($name == 'test') {
            $redirect =  $this->redirect($this->generateUrl('_write_board'));
            $foward   = $this->forward('DougenBoardBundle:Board:write', array('color' => 'green')); 
            $request = $this->getRequest();
            var_dump($request->query->get('test'));
        }
         */
        
        /*
        $post = new Post();
        $post->setTitle('Initial apply');
        $post->setContent('テストコンテンツ');
        $now = new \DateTime();
        $post->setCreated($now);
        $post->setModified($now);
        $em = $this->getDoctrine()->getEntityManager();
        $em->persist($post);
        $em->flush();
         */

        // アノテーションのテンプレートを使用した場合
        // renderを使用した場合
        // $this->render('DougenBoardBundle:Default:index.html.twig', ['name' => $name]);
        // return $this->render('DougenBoardBundle:Default:index.html.twig', ['name' => $name]);
        // Responseオブジェクトを利用した場合
        // $response = new Response('<html><body>Hello '.$name.'!</body></html>');
        // return $response;
    }

    /**
     * @Route("/thread/{thread_id}", name="_thread")
     * @Template()
     */
    public function threadAction($thread_id, Request $request)
    {
        $thread = $this->getDoctrine()
            ->getRepository('DougenBoardBundle:Thread')
            ->find($thread_id);
        $posts = $this->getDoctrine()
            ->getRepository('DougenBoardBundle:Post')
            ->findAll();

        $post = new Post();
        $form = $this->createFormBuilder($post)
            ->add('name', 'text')
            ->add('content', 'textarea')
            ->getForm();

        $form->handleRequest($request);
        if ($form->isValid()) {
            $now = new \DateTime();
            $post->setCreated($now);
            $post->setModified($now);
            $post->setThreadId($thread_id);
            $em = $this->getDoctrine()->getEntityManager();
            $em->persist($post);
            $em->flush();
            return $this->redirect($this->generateUrl('_thread', array('thread_id' => $thread_id)));
        }

        return array('form' => $form->createView(), 'thread' => $thread, 'posts' => $posts);
    }

    /**
     * @Route("/delete")
     * @Template()
     */
    public function deleteAction()
    {
        return array();
    }

    /**
     * @Route("/test")
     * @Template("DougenBoardBundle:Board:test2.html.twig")
     */
    public function testAction()
    {
        return array('messsage' => 'テンプレート指定');
    }
}

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
        // スレッド全件取得
        $threads = $this->getDoctrine()
            ->getRepository('DougenBoardBundle:Thread')
            ->findAll();

        // スレッド用フォーム作成
        $thread = new Thread();
        $form = $this->createFormBuilder($thread)
            ->add('title', 'text', ['label' => false])
            ->getForm();

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                // 新スレッド保存処理
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
    }

    /**
     * @Route("/thread/{thread_id}", name="_thread")
     * @Template()
     */
    public function threadAction($thread_id, Request $request)
    {
        // 対象スレッドの情報取得
        $thread = $this->getDoctrine()
            ->getRepository('DougenBoardBundle:Thread')
            ->find($thread_id);
        // 関連投稿全件取得
        $posts = $this->getDoctrine()
            ->getRepository('DougenBoardBundle:Post')
            ->findByThreadId($thread_id);

        // 投稿フォーム作成
        $post = new Post();
        $form = $this->createFormBuilder($post)
            ->add('name', 'text', ['max_length' => 30, 'label' => false])
            ->add('content', 'textarea', ['max_length' => 300, 'label' => false])
            ->getForm();

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                // 投稿保存処理
                $now = new \DateTime();
                $post->setCreated($now);
                $post->setModified($now);
                $post->setThreadId($thread_id);
                $em = $this->getDoctrine()->getEntityManager();
                $em->persist($post);
                $em->flush();
                return $this->redirect($this->generateUrl('_thread', array('thread_id' => $thread_id)));
            }
        }

        return array('form' => $form->createView(), 'thread' => $thread, 'posts' => $posts);
    }

    /**
     * @Route("delete/{post_id}/{thread_id}", name="_delete")
     * @Template()
     */
    public function deleteAction($post_id, $thread_id, Request $request)
    {
        if ($request->getMethod() == 'POST') {
            $em = $this->getDoctrine()->getEntityManager();
            $post = $em->getRepository('DougenBoardBundle:Post')->find($post_id);
            $em = $this->getDoctrine()->getEntityManager();
            $em->remove($post);
            $em->flush();
        }
        return $this->redirect($this->generateUrl('_thread', array('thread_id' => $thread_id)));
    }
}

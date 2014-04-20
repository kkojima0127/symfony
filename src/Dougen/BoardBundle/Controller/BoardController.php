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
            /* その他のメソッド */

            // 任意のカラム値に基づき1件のみ取得
            // ->findOneById($id)
            // ->findOneByName($name)

            // 任意のカラム値に基づく前件取得
            // ->findByPrice($price)

            // 複数条件
            // ->findBy(array('name' => 'hoge', 'status' => 1))
            // ->findOneBy(array('name' => 'hoge', 'status' => 1))


        

        // Threadオブジェクト生成
        $thread = new Thread();
        // Thread用フォーム作成
        $form = $this->createFormBuilder($thread)
            ->add('title', 'text', ['label' => false])
            ->getForm();

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            // バリデーションを通った場合
            if ($form->isValid()) {
                // DateTimeオブジェクトを生成して現在時をセット
                $now = new \DateTime();
                $thread->setCreated($now);
                $thread->setModified($now);
                // エンティティマネージャ生成
                $em = $this->getDoctrine()->getEntityManager();
                // persistメソッドでthreadオブジェクトをドクトリンで管理
                $em->persist($thread);
                // 保存
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
     * @Route("update/{post_id}/{thread_id}", name="_update")
     * @Template()
     */
     public function updateAction($post_id, $thread_id) {
         $em = $this->getDoctrine()->getEntityManager();
         // Doctrineからフェッチしているのでpostのデータは既にemに管理された状態になっている
         $post = $em->getRepository('DougenBoardBundle:Post')->findOneById($post_id);

         if (!$post) {
            throw $this->createNotFoundException('投稿内容が存在しません');
         }
         $post->setContent('new data');
         // 保存
         $em->flush();
         
     }

    /**
     * @Route("delete/{post_id}/{thread_id}", name="_delete")
     * @Template()
     */
    public function deleteAction($post_id, $thread_id, Request $request)
    {
        if ($request->getMethod() == 'POST') {
            $em = $this->getDoctrine()->getEntityManager();
            $post = $em->getRepository('DougenBoardBundle:Post')->findOneById($post_id);
            $em->remove($post);
            $em->flush();
        }
        return $this->redirect($this->generateUrl('_thread', array('thread_id' => $thread_id)));
    }

    /**
     * @Route("thread_search", name="_thread_search")
     * @Template()
     */
    public function threadSearchAction(Request $request)
    {
        // 投稿フォーム作成
        $thread = new Thread();
        $form = $this->createFormBuilder($thread)
            ->add('title', 'text', ['max_length' => 30, 'label' => false])
            ->getForm();

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            
            $em = $this->getDoctrine()->getEntityManager();
            $query = $em->createQuery(
                'SELECT t FROM DougenBoardBundle:Thread t WHERE t.title LIKE :keyword'
            )->setParameter('keyword', '%'.$thread->getTitle().'%');
            $threads = $query->getResult();
        }
        return array('form' => $form->createView(), 'threads' => $threads);
    }
}

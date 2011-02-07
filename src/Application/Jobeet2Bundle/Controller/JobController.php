<?php

namespace Application\Jobeet2Bundle\Controller;

use Application\Jobeet2Bundle\Entity\Job;
use Application\Jobeet2Bundle\Entity\User;
use Application\Jobeet2Bundle\Entity\Category;
use Application\Jobeet2Bundle\Form\JobForm;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\DoctrineBundle\Form\ValueTransformer\EntityToIDTransformer;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Doctrine\ORM\Events;
use Application\Jobeet2Bundle\Listener\JobEventListener;



class JobController extends Controller
{
    
    protected function getEm()
    {
        return $this->get('doctrine.orm.entity_manager');
    }    
 

    public function indexAction()
    {

        $categories = $this->getEm()->getRepository('Jobeet2Bundle:Category')->findAllJobsByCategory();
       
        return $this->render('Jobeet2Bundle:Job:index.twig.html',
                array('categories'=>$categories));

    }
    
    /**
     * list jobs by category
     */
    
    public function listAction(Category $category = null)
    {
        if (null !== $category) {            
            $jobs = $this->getEm()->getRepository('Jobeet2Bundle:Job')->findAllByCategory($category,true);            
        } else {
            $jobs = $this->getEm()->getRepository('Jobeet2Bundle:Job')->findAll(true);
        }
        
        //$jobs->setCurrentPageNumber($page);
        $jobs->setCurrentPageNumber(1);
        //$jobs->setItemCountPerPage($this->container->getParameter('forum.paginator.topics_per_page'));
        $jobs->setItemCountPerPage(4);
        $jobs->setPageRange(2);

        return $this->render('Jobeet2Bundle:Job:list.twig.html', array(
            'jobs'    => $jobs,
            'category'  => $category
        ));
        
    }
    
    /**
     * Show deatiled job info
     */

    public function showAction($slug)
    {

        $job = $this->getEm()->getRepository('Jobeet2Bundle:Job')->findOneBySlug($slug);
        
        if (!$job) {
            throw new NotFoundHttpException('The Job does not exist.');
        }
        return $this->render('Jobeet2Bundle:Job:show.twig.html',
            array('job'=>$job));
        
    }

    public function updateAction($id)
    {

        $em = $this->getEm();
        $job = $em->find("Jobeet2Bundle:Job",$id);
        
        if (!$job) {
            throw new NotFoundHttpException('The Job does not exist.');
        }


        // submmited data

        if ('POST' == $this->get('request')->getMethod()) {
            $form->bind($this->get('request')->request->get('job'));

            if ($form->isValid()) {
                // save $job object and redirect
                $em->flush();
                return $this->redirect($this->generateUrl('index'));

            }
        }

        return $this->render('Jobeet2Bundle:Job:update.twig.html',
                array('form'=>$form));       

    }

  
    public function newAction()
    {

        $em = $this->getEm();

        $categories = $this->getEm()->getRepository('Jobeet2Bundle:Category')->findAllIndexedById();
        $job = new Job();

        $categoryTransformer = new EntityToIDTransformer(array(
            'em' => $em,
            'className' => 'Jobeet2Bundle:Category',
        ));

        $form = new JobForm('job', $job, $this->get('validator'),
                array('categories' => $categories,'categoryTransformer' => $categoryTransformer));


        /*retrieve parameter from container
        $active_days = $this->container->getParameter('jobeet2.active_days');
        $job->setActiveDays($active_days);*/

        
        if ('POST' == $this->get('request')->getMethod()) {
            $form->bind($this->get('request')->request->get('job'));

            if ($form->isValid()) {
                // save $job object and redirect   
                $em->persist($job);
                $em->flush();

                return $this->redirect($this->generateUrl('index'));
            }

        }

        return $this->render('Jobeet2Bundle:Job:new.twig.html',
                array('form'=>$form));
        
    }


    public function deleteAction($id)
    {
        $em = $this->getEm();
        $job = $em->find("Jobeet2Bundle:Job",$id);

         if (!$this->job) {
            throw new NotFoundHttpException('The Job does not exist.');
        }

        $em->remove($job);
        $em->flush();

        return $this->redirect($this->generateUrl('index'));

    }

    public function editAction($id)
    {
               
    }
}

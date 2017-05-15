<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Person;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class BackController extends Controller
{
    /**
     * @Route("/", name="back_index")
     */
    public function indexAction()
    {
        $persons = $this->getDoctrine()
            ->getRepository('AppBundle:Person')
            ->findAll();

        return $this->render('back/index.html.twig', array('persons' => $persons));
    }

    /**
     * @Route("/add", name="back_add")
     */
    public function addAction(Request $request)
    {
        $person = new Person;

        $form = $this->createFormBuilder($person)
            ->add('lastname', TextType::class, array('attr' => array('class' => 'form-control', 'style' => 'margin-bottom:15px')))
            ->add('firstname', TextType::class, array('attr' => array('class' => 'form-control', 'style' => 'margin-bottom:15px')))
            ->add('age', IntegerType::class, array('attr' => array('class' => 'form-control', 'style' => 'margin-bottom:15px')))
            ->add('save', SubmitType::class, array('label' => 'add', 'attr' => array('class' => 'btn btn-primary', 'style' => 'margin-bottom:15px')))
            ->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $em = $this->getDoctrine()->getManager();
            $em->persist($person);
            $em->flush();

            $this->addFlash('notice', 'Person Added');

            return $this->redirectToRoute('back_index');
        }

        return $this->render('back/add.html.twig', array('form' => $form->createView()));
    }

    /**
     * @Route("/delete/{id}", name="back_delete")
     */
    public function deleteAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $person = $em->getRepository('AppBundle:Person')->find($id);

        $em->remove($person);
        $em->flush();

        $this->addFlash('error', 'Person Removed');
        
        return $this->redirectToRoute('back_index');
    }

    /**
     * @Route("/edit/{id}", name="back_edit")
     */
    public function editAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $person = $em->getRepository('AppBundle:Person')->find($id);

        $form = $this->createFormBuilder($person)
            ->add('lastname', TextType::class, array('attr' => array('value' => $person->getLastname(),'class' => 'form-control', 'style' => 'margin-bottom:15px')))
            ->add('firstname', TextType::class, array('attr' => array('value' => $person->getFirstname(),'class' => 'form-control', 'style' => 'margin-bottom:15px')))
            ->add('age', IntegerType::class, array('attr' => array('value' => $person->getAge(),'class' => 'form-control', 'style' => 'margin-bottom:15px')))
            ->add('save', SubmitType::class, array('label' => 'save', 'attr' => array('class' => 'btn btn-primary', 'style' => 'margin-bottom:15px')))
            ->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $em = $this->getDoctrine()->getManager();
            $em->flush();
            $this->addFlash('notice', 'Person Updated');

            return $this->redirectToRoute('back_index');
        }

        return $this->render('back/edit.html.twig', array('form' => $form->createView()));


    }


    /**
     * @Route("/persons", name="persons_list")
     * @Method({"GET"})
     */
    public function getPersonsAction(Request $request)
    {
        $persons = $this->getDoctrine()
            ->getRepository('AppBundle:Person')
            ->findAll();

        $json = [];
        foreach ($persons as $person) {
            $json[] = [
               'id' => $person->getId(),
               'lastname' => $person->getLastname(),
               'firstname' => $person->getFirstname(),
               'age' => $person->getAge()
            ];
        }

        return new JsonResponse($json);
    }
}

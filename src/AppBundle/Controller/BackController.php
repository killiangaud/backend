<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Person;
use AppBundle\Entity\Address;
use idlab_backend\AppBundle\Repository\PersonRepository;
use idlab_backend\AppBundle\Repository\AddressRepository;
use Doctrine\ORM\EntityRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
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
     * @Route("/addPerson", name="back_addPerson")
     */
    public function addPersonAction(Request $request)
    {
        $person = new Person;


        $form = $this->createFormBuilder($person)
            ->add('lastname', TextType::class)
            ->add('firstname', TextType::class)
            ->add('age', IntegerType::class)
            ->add('address', EntityType::class, 
                array('class' => 'AppBundle:Address', 
                'required'=> false, 
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('a')->orderBy('a.name', 'ASC');
                }))
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

        return $this->render('back/addPerson.html.twig', array('form' => $form->createView()));
    }

    /**
     * @Route("/addAddress", name="back_addAddress")
     */
    public function addAddressAction(Request $request)
    {
        $address = new Address;

        $form = $this->createFormBuilder($address)
            ->add('name', TextType::class)
            ->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $em = $this->getDoctrine()->getManager();
            $em->persist($address);
            $em->flush();

            $this->addFlash('notice', 'Address Added');

            return $this->redirectToRoute('back_index');
        }

        return $this->render('back/addAddress.html.twig', array('form' => $form->createView()));
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
            ->add('lastname', TextType::class)
            ->add('firstname', TextType::class)
            ->add('age', IntegerType::class)
            ->add('address', EntityType::class, 
                array('class' => 'AppBundle:Address', 
                'required'=> false, 
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('a')->orderBy('a.name', 'ASC');
                }))
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

            if ($person->getAddress() != null)
            {
                $address = $this->getDoctrine()
                    ->getRepository('AppBundle:Address')
                    ->find($person->getAddress());
                    
                $json[] = [
                   'id' => $person->getId(),
                   'lastname' => $person->getLastname(),
                   'firstname' => $person->getFirstname(),
                   'age' => $person->getAge(),
                   'address' => [$address->getId(), $address->getName()]
                ];
            }
            else
            {
                $json[] = [
                   'id' => $person->getId(),
                   'lastname' => $person->getLastname(),
                   'firstname' => $person->getFirstname(),
                   'age' => $person->getAge(),
                ];
            }
        }

        return new JsonResponse($json);
    }
}

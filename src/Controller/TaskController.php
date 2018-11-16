<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Task;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TaskController extends AbstractController
{
    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    public function __construct(ValidatorInterface $validator, SerializerInterface $serializer)
    {
        $this->setValidator($validator);
        $this->setSerializer($serializer);
    }
    /**
     * @Route("/task", name="task_list", methods={"GET"})
     */
    public function list()
    {
        $tasks = $this->getDoctrine()->getRepository(Task::class)
            ->findAll();
        return $this->json($tasks, Response::HTTP_OK, [], ['groups' => ['project_list']]);
    }

    /**
     * @Route("/task/create", name="tack_create", methods={"POST"})
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function create(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $content = $request->getContent();

        $cat = null;
        if(isset(json_decode($content, true)['category_id'])) {
            $cat = $em->getRepository(Category::class)->find(json_decode($content, true)['category_id']);
        }

        if(!isset(json_decode($content, true)['category_id']) || !$cat) {
            return $this->json([
                'code' => Response::HTTP_BAD_REQUEST,
                'message' => 'Pas de catégorie ou id incorrect'
            ], Response::HTTP_BAD_REQUEST);
        }

        $task = $this->getSerializer()->deserialize(
            $content,
            Task::class,
            'json'
        );

        $task->setCategory($cat);
        $task->setCreatedAt(new \DateTime());

        $errors = $this->getValidator()->validate($task);
        if(count($errors)) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        $em->persist($task);
        $em->flush();

        return $this->json([
            'code' => Response::HTTP_CREATED,
            'message' => 'La tache a bien été crée'
        ], Response::HTTP_CREATED);
    }

    public function edit($id) {

    }


    /**
     * @return ValidatorInterface
     */
    public function getValidator(): ValidatorInterface
    {
        return $this->validator;
    }

    /**
     * @param ValidatorInterface $validator
     */
    public function setValidator(ValidatorInterface $validator): void
    {
        $this->validator = $validator;
    }

    /**
     * @return SerializerInterface
     */
    public function getSerializer(): SerializerInterface
    {
        return $this->serializer;
    }

    /**
     * @param SerializerInterface $serializer
     */
    public function setSerializer(SerializerInterface $serializer): void
    {
        $this->serializer = $serializer;
    }
}
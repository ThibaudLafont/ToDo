<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Task;
use Doctrine\ORM\EntityManager;
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
     * @Route(
     *     "/task/{filter}",
     *     name="task_list",
     *     methods={"GET"},
     *     defaults={"filter"="all"},
     *     requirements={"filter":"all|todo|is-done"})
     *
     * @param string $filter
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function list(string $filter)
    {
        $rep = $this->getDoctrine()->getRepository(Task::class);

        $tasks = null;
        $groups = null;
        if($filter === 'all'){
            $tasks = $rep->findAll();
        } elseif($filter === 'todo') {
            $tasks = $rep->findToDoTasks();

        } elseif($filter === 'is-done') {
            $tasks = $rep->findDoneTasks();
        }

        return $this->json($tasks, Response::HTTP_OK, [], ['groups' => ['task_list']]);
    }

    /**
     * @Route("/task/{id}",
     *     name="task_show",
     *     methods={"GET"},
     *     requirements={"id"="\d+"})
     *
     * @param Task $task
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function show(Task $task)
    {
        return $this->json($task, Response::HTTP_OK, [], ['groups' => ['task_list']]);
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
        $content = json_decode($request->getContent(), true);

        $cat = $this->getOrCreateCategory($content['cat'], $em);

        if(!$cat) {
            return $this->json([
                'code' => Response::HTTP_BAD_REQUEST,
                'message' => 'Pas de catégorie renseignée ou id incorrect'
            ], Response::HTTP_BAD_REQUEST);
        }

        $task = $this->getSerializer()->deserialize(
            $request->getContent(),
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

    /**
     * @Route("/task/edit/{id}",
     *     name="task_edit",
     *     methods={"POST"},
     *     requirements={"id"="\d+"})
     *
     * @param Request $request
     * @param $id
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function edit(Request $request, $id) {
        $em = $this->getDoctrine()->getManager();
        $content = json_decode($request->getContent(), true);

        $task = $em->getRepository(Task::class)->find($id);
        if(!$task) {
            return $this->json([
                'code' => Response::HTTP_BAD_REQUEST,
                'message' => 'Pas de tache trouvée avec cet id'
            ], Response::HTTP_NOT_FOUND);
        }

        if(isset($content['cat'])) {
            $cat = $this->getOrCreateCategory($content['cat'], $em);

            if(!$cat) {
                return $this->json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'message' => 'Pas de catégorie renseignée ou id incorrect'
                ], Response::HTTP_BAD_REQUEST);
            }

            $task->setCategory($cat);
        }

        $task->hydrate($content);
        $errors = $this->getValidator()->validate($task);
        if(count($errors)) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }
        $em->flush();

        return $this->json([
            'code' => Response::HTTP_OK,
            'message' => 'La tache a bien été modifiée !',
            'tache' => $task
        ], Response::HTTP_OK, [], ['groups' => ['task_list']]);
    }

    /**
     * @Route("/task/delete/{id}",
     *     name="task_delete",
     *     methods={"GET"},
     *     requirements={"id"="\d+"})
     *
     * @param Task $task
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function delete(Task $task)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($task);
        $em->flush();

        return $this->json([
            'code' => Response::HTTP_ACCEPTED,
            'message' => 'Tache supprimée'
        ], Response::HTTP_ACCEPTED);
    }

    /**
     * @Route("/task/is-done/{id}",
     *     name="task_is_done",
     *     methods={"GET"},
     *     requirements={"id"="\d+"})
     *
     * @param Task $task
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function isDone(Task $task)
    {
        if(is_null($task->getDoneAt())) {
            $em = $this->getDoctrine()->getManager();
            $task->setDoneAt(new \DateTime());
            $em->flush();

            return $this->json([
                'code' => Response::HTTP_OK,
                'message' => 'Tache marquée comme terminée',
                'Tache' => $task
            ], Response::HTTP_OK, [], ['groups' => ['task_list']]);
        }

        return $this->json([
            'code' => Response::HTTP_BAD_REQUEST,
            'message' => 'Tache déjà terminée'
        ], Response::HTTP_BAD_REQUEST, [], ['groups' => ['task_list']]);
    }

    /**
     * @Route("/task/to-do/{id}",
     *     name="task_to_do",
     *     methods={"GET"},
     *     requirements={"id"="\d+"})
     *
     * @param Task $task
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function toDo(Task $task)
    {
        if(!is_null($task->getDoneAt())) {
            $em = $this->getDoctrine()->getManager();
            $task->setDoneAt(null);
            $em->flush();

            return $this->json([
                'code' => Response::HTTP_OK,
                'message' => 'Tache marquée comme en cours',
                'Tache' => $task
            ], Response::HTTP_OK, [], ['groups' => ['task_list']]);
        }

        return $this->json([
            'code' => Response::HTTP_BAD_REQUEST,
            'message' => 'Tache déjà en cours'
        ], Response::HTTP_BAD_REQUEST, [], ['groups' => ['task_list']]);
    }

    public function getOrCreateCategory(Array $catData, EntityManager $em)
    {
        $cat = null;
        if(isset($catData['id'])) {
            $cat = $em->getRepository(Category::class)->find($catData['id']);
        }

        if(isset($catData['name']) && !$cat) {
            $cat = new Category();
            $cat->setName($catData['name']);
            $em->persist($cat);
        }

        return $cat;
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
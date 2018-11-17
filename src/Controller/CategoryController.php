<?php

namespace App\Controller;

use App\Entity\Category;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CategoryController extends AbstractController
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
     *     "/category/{task}",
     *     name="category_list",
     *     methods={"GET"},
     *     defaults={"task"=""},
     *     requirements={"filter":"task"}
     * )
     *
     * @param string $task
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function list(string $task)
    {
        $context = empty($task) ? ['groups' => ['category_list']] : ['groups' => ['category_task_list']];
        $categories = $this->getDoctrine()->getRepository(Category::class)
            ->findAll();
        return $this->json($categories, Response::HTTP_OK, [], $context);
    }

    /**
     * @Route(
     *     "/category/{id}",
     *     name="category_show",
     *     requirements={"id"="\d+"},
     *     methods={"GET"}
     * )
     *
     * @param Category $category
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function show(Category $category)
    {
        return $this->json($category);
    }

    /**
     * @Route(
     *     "/category/create",
     *     name="category_create",
     *     methods={"POST"}
     * )
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function create(Request $request)
    {
        $cat = $this->getSerializer()->deserialize(
            $request->getContent(),
            Category::class,
            'json'
        );

        $errors = $this->getValidator()->validate($cat);
        if(count($errors)) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($cat);
        $em->flush();

        return $this->json([
            'code' => Response::HTTP_CREATED,
            'message' => 'La catégorie a bien été crée'
        ], Response::HTTP_CREATED);
    }

    /**
     * @Route("/category/edit/{id}",
     *     name="category_edit",
     *     requirements={"id"="\d+"},
     *     methods={"POST"})
     *
     * @param Request $request
     * @param $id
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function edit(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $cat = $em->getRepository(Category::class)
            ->find($id);

        if(is_null($cat)) {
            return $this->json([
                'code'=> Response::HTTP_NOT_FOUND,
                'message'=> 'Aucune catégorie ici'],
            Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        $cat->hydrate($data);

        $errors = $this->getValidator()->validate($cat);
        if(count($errors)) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }
        $em->flush();

        return $this->json([
            'code' => Response::HTTP_OK,
            'message' => 'La catégorie a bien été mise à jour',
            'Categorie' => $cat
        ], Response::HTTP_OK, [], ['groups' => ['category_list']]);

    }

    /**
     * @Route("/category/delete/{id}", name="category_delete", methods={"GET"})
     * @param Category $category
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function delete(Category $category)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($category);
        $em->flush();

        return $this->json([
            'code' => Response::HTTP_ACCEPTED,
            'message' => 'Catégorie supprimée'
        ], Response::HTTP_ACCEPTED);
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

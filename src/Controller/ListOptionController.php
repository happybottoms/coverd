<?php

namespace App\Controller;

use App\Entity\ListOption;
use App\Transformers\ListOptionTransformer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class ListOptionController extends BaseController
{
    protected $defaultEntityName = 'App\Entity\ListOption';

    /**
     * Returns the ListOption entity that this controller should work with
     *
     * @return ListOption
     */
    abstract protected function getListOptionEntityInstance();

    /**
     * Get a list of ListOptions
     *
     * @Route(path="", methods={"GET"})
     */
    public function index(Request $request): JsonResponse
    {
        $listOptions = $this->getRepository()->findAll();

        return $this->serialize($request, $listOptions);
    }

    /**
     * Get a single ListOption
     *
     * @Route(path="/{id<\d+>}", methods={"GET"})
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $listOption = $this->getListOption($id);

        return $this->serialize($request, $listOption);
    }

    /**
     * Save a new listOption
     *
     * @Route(path="", methods={"POST"})
     */
    public function store(Request $request, ValidatorInterface $validator): JsonResponse
    {
        $listOption = $this->getListOptionEntityInstance();
        $listOption->setName($request->get('name'));

        $params = $this->getParams($request);
        $listOption->applyChangesFromArray($params);

        $this->validate($listOption, $validator);

        $this->getEm()->persist($listOption);
        $this->getEm()->flush();

        return $this->serialize($request, $listOption);
    }

    /**
     * Whole or partial update of a listOption
     *
     * @Route(path="/{id<\d+>}", methods={"PATCH"})
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $params = $this->getParams($request);
        /** @var ListOption $listOption */
        $listOption = $this->getListOption($id);

        $listOption->applyChangesFromArray($params);

        $this->getEm()->persist($listOption);
        $this->getEm()->flush();

        return $this->serialize($request, $listOption);
    }

    /**
     * Delete a listOption
     *
     * @Route(path="/{id<\d+>}", methods={"DELETE"})
     */
    public function destroy(int $id): JsonResponse
    {
        $listOption = $this->getListOption($id);

        $listOption->setDeletedAt(new \DateTime());

        $this->getEm()->persist($listOption);
        $this->getEm()->flush();

        return $this->success(sprintf('ListOption "%s" deleted.', $listOption->getName()));
    }

    /**
     * @throws NotFoundHttpException
     */
    protected function getListOption(int $id): ListOption
    {
        /** @var ListOption $listOption */
        $listOption = $this->getRepository()->find($id);

        if ($listOption === null) {
            throw new NotFoundHttpException(sprintf('Unknown ListOption ID: %d', $id));
        }

        return $listOption;
    }

    protected function getDefaultTransformer()
    {
        return new ListOptionTransformer();
    }
}

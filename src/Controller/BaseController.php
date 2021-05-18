<?php

namespace App\Controller;

use App\Entity\CoreEntity;
use App\Serializer\ApiSerializer;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\TransformerAbstract;
use SamJ\FractalBundle\ContainerAwareManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Workflow\Registry;
use Symfony\Component\Workflow\Transition;

abstract class BaseController extends AbstractController
{
    /** @var string */
    protected $defaultEntityName;

    protected function getEm()
    {
        return $this->getDoctrine()->getManager();
    }

    /**
     * @param string|null $entityName
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getRepository($entityName = null)
    {
        if (!isset($entityName) && !isset($this->defaultEntityName)) {
            throw new \InvalidArgumentException(
                'Controller is missing a default entity name and none specified when fetching the repository'
            );
        }

        $entityName = $entityName ?: $this->defaultEntityName;

        return $this->getEm()->getRepository($entityName);
    }

    /**
     * @param $entities
     * @param TransformerAbstract $transformer
     * @param array $meta
     * @return JsonResponse
     */
    protected function serialize(Request $request, $entities, TransformerAbstract $transformer = null, $meta = [])
    {
        $transformer = $transformer ?: $this->getDefaultTransformer();

        if ($entities instanceof \Traversable || is_array($entities)) {
            $resource = new Collection($entities, $transformer, 'data');
        } else {
            $resource = new Item($entities, $transformer, 'data');
        }

        $resource->setMeta($meta);

        return new JsonResponse($this->fractal($request)->createData($resource)->toArray());
    }

    private function fractal(Request $request)
    {
        $params = $this->getParams($request);

        /** @var Manager $manager */
        $manager = new ContainerAwareManager();
        $manager->setSerializer(new ApiSerializer());
        if (key_exists('include', $params)) {
            $manager->parseIncludes($params['include']);
        }
        return $manager;
    }

    /**
     * Metadata only success response with message
     *
     * @param $message
     * @return JsonResponse
     */
    protected function success($message = null)
    {
        $meta = ['success' => true];

        if ($message) {
            $meta['message'] = $message;
        }

        return new JsonResponse($meta);
    }

    /**
     * Metadata only response with message
     *
     * @return JsonResponse
     */
    protected function meta(bool $success = true, ?string $message = null)
    {
        $meta = ['success' => $success];

        if ($message) {
            $meta['message'] = $message;
        }

        return new JsonResponse($meta);
    }

    /**
     * Gets the parameters from the request including any json in the content
     *
     * @param Request $request
     * @return array
     */
    protected function getParams(Request $request): array
    {
        // TODO: This needs to redone to check the type of request and only give back the right params for that method
        $params = $request->request->all();
        $params += $request->query->all();

        if ($request->getContentType() == 'json') {
            $params += json_decode($request->getContent(), true);
        }

        return $params;
    }

    /**
     * @param $entity
     * @param ValidatorInterface $validator
     *
     * @throws BadRequestHttpException
     */
    protected function validate($entity, ValidatorInterface $validator)
    {
        $errors = $validator->validate($entity);

        if (count($errors) > 0) {
            $errorsString = (string) $errors;

            throw new BadRequestHttpException($errorsString);
        }
    }

    protected function getEnabledTransitions(Registry $workflowRegistry, CoreEntity $entity): array
    {
        $workflow = $workflowRegistry->get($entity);
        $enabledTransitions = $workflow->getEnabledTransitions($entity);

        return array_map(static function (Transition $transition) use ($workflow) {
            $title = $workflow->getMetadataStore()->getTransitionMetadata($transition)['title'];
            return [
                'transition' => $transition->getName(),
                'title' => $title
            ];
        }, $enabledTransitions);
    }

    protected function getDefaultTransformer()
    {
        // Implement if tied to an entity
    }
}

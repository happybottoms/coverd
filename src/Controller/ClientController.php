<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\EAV\ClientDefinition;
use App\Entity\Orders\BulkDistributionLineItem;
use App\Entity\Partner;
use App\Entity\User;
use App\Entity\ValueObjects\Name;
use App\Exception\UserInterfaceException;
use App\Reports\ClientDistributionExcel;
use App\Repository\ClientRepository;
use App\Security\ClientVoter;
use App\Service\EavAttributeProcessor;
use App\Transformers\BulkDistributionLineItemTransformer;
use App\Transformers\ClientAttributeTransformer;
use App\Transformers\ClientTransformer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Workflow\Registry;
use Symfony\Component\Workflow\Transition;

/**
 * @Route(path="/api/clients")
 */
class ClientController extends BaseController
{
    protected $defaultEntityName = Client::class;

    /**
     * Get a list of Clients
     *
     * @Route(path="/", methods={"GET"})
     * @IsGranted({"ROLE_CLIENT_VIEW_ALL","ROLE_CLIENT_MANAGE_OWN"})
     *
     */
    public function index(Request $request): JsonResponse
    {
//        $this->checkViewPermissions($clients);

        $sort = $request->get('sort') ? explode('|', $request->get('sort')) : null;
        $page = (int) $request->get('page', 1);
        $limit = (int) $request->get('per_page', 10);

        $params = $this->buildFilterParams($request);

        $total = (int) $this->getRepository()->findAllCount($params);

        if ($limit === -1) {
            $limit = $total;
        }

        $clients = $this->getRepository()->findAllPaged(
            $page,
            $limit,
            $sort ? $sort[0] : null,
            $sort ? $sort[1] : null,
            $params
        );

        $meta = [
            'pagination' => [
                'total' => $total,
                'per_page' => $limit,
                'current_page' => $page,
                'last_page' => ceil($total / $limit),
                'next_page_url' => null,
                'prev_page_url' => null,
                'from' => (($page - 1) * $limit) + 1,
                'to' => min($page * $limit, $total),
            ]
        ];

        return $this->serialize($request, $clients, null, $meta);
    }

    /**
     * Limited Client search for partners to look for duplicates
     *
     * @param Request $request
     *
     * @Route("/search", methods={"GET"})
     * @IsGranted({"ROLE_CLIENT_EDIT_ALL","ROLE_CLIENT_MANAGE_OWN"})
     */
    public function search(Request $request): JsonResponse
    {
        $sort = $request->get('sort') ? explode('|', $request->get('sort')) : null;

        $params = $this->buildFilterParams($request, true);

        $clients = $this->getEm()
            ->getRepository(Client::class)
            ->findLimitedSearch(
                $params,
                $sort ? $sort[0] : null,
                $sort ? $sort[1] : null
            );

        return $this->serialize($request, $clients);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     *
     * @Route(path="/new-fields", methods={"GET"})
     */
    public function newClientAttributes(Request $request): JsonResponse
    {
        $fields = $this->getRepository(ClientDefinition::class)->findBy(['isDuplicateReference' => true]);

        $attributes = array_map(function ($definition) {
            return $definition->createAttribute();
        }, $fields);

        return $this->serialize($request, $attributes, new ClientAttributeTransformer());
    }

    /**
     * @param Request $request
     * @return JsonResponse
     *
     * @Route(path="/fields", methods={"GET"})
     */
    public function clientAttributes(Request $request): JsonResponse
    {
        $fields = $this->getRepository(ClientDefinition::class)->findAll();

        $attributes = array_map(function ($definition) {
            return $definition->createAttribute();
        }, $fields);

        return $this->serialize($request, $attributes, new ClientAttributeTransformer());
    }

    /**
     * @param Request $request
     * @return JsonResponse
     *
     * @Route(path="/new-check", methods={"POST"})
     * @IsGranted({"ROLE_CLIENT_EDIT_ALL","ROLE_CLIENT_MANAGE_OWN"})
     */
    public function newClientCheck(Request $request): JsonResponse
    {
        $params = $this->getParams($request);

        $paramBag = new ParameterBag();
        $paramBag->set('firstname', $params['firstName']);
        $paramBag->set('lastname', $params['lastName']);
        $paramBag->set('birthdate', new \DateTime($params['birthdate']));
        $paramBag->set('attributes', $params['attributes']);

        /** @var ClientRepository $repo */
        $repo = $this->getRepository(Client::class);
        $result = $repo->findDuplicates($paramBag);

        return $this->serialize($request, $result);
    }
    /**
     * Get a single Client
     *
     * @Route(path="/{publicId}", methods={"GET"})
     * @IsGranted({"ROLE_CLIENT_VIEW_ALL","ROLE_CLIENT_MANAGE_OWN"})
     *
     */
    public function show(Request $request, Registry $workflowRegistry, string $publicId): JsonResponse
    {
        $client = $this->getClientById($publicId);

        $this->denyAccessUnlessGranted(ClientVoter::VIEW, $client);

        $meta = [
            'enabledTransitions' => $this->getEnabledTransitions($workflowRegistry, $client),
        ];

        return $this->serialize($request, $client, null, $meta);
    }

    /**
     * Save a new Client
     *
     * @Route(path="", methods={"POST"})
     * @IsGranted({"ROLE_CLIENT_EDIT_ALL","ROLE_CLIENT_MANAGE_OWN"})
     *
     */
    public function store(
        Request $request,
        Registry $workflowRegistry,
        EavAttributeProcessor $eavProcessor
    ): JsonResponse {
        $params = $this->getParams($request);

        $client = new Client($workflowRegistry);

        if ($params['partner']['id']) {
            $newPartner = $this->getEm()->find(Partner::class, $params['partner']['id']);
            if (!$newPartner) {
                throw new \Exception('Invalid Partner ID provided');
            }
            $client->setPartner($newPartner);
        }

        $eavProcessor->processAttributeChanges($client, $params);

        $client->applyChangesFromArray($params);

//        $this->checkEditPermissions($client);

        $this->getEm()->persist($client);
        $this->getEm()->flush();

        return $this->serialize($request, $client);
    }

    /**
     * Whole or partial update of a client
     *
     * @Route(path="/{publicId}", methods={"PATCH"})
     * @IsGranted({"ROLE_CLIENT_EDIT_ALL","ROLE_CLIENT_MANAGE_OWN"})
     *
     */
    public function update(Request $request, EavAttributeProcessor $eavProcessor, string $publicId): JsonResponse
    {
        $params = $this->getParams($request);
        /** @var Client $client */
        $client = $this->getClientById($publicId);
        $this->denyAccessUnlessGranted(ClientVoter::EDIT, $client);

        if ($params['firstName'] && $params['lastName']) {
            $name = new Name($params['firstName'], $params['lastName']);
            $client->setName($name);
            unset($params['firstName'], $params['lastName']);
        }

        if (isset($params['partner']['id'])) {
            $newPartner = $this->getEm()->find(Partner::class, $params['partner']['id']);
            if (!$newPartner) {
                throw new \Exception('Invalid Partner ID provided');
            }
            $client->setPartner($newPartner);
        }

        $eavProcessor->processAttributeChanges($client, $params);

        $client->applyChangesFromArray($params);

        $this->getEm()->persist($client);
        $this->getEm()->flush();

        return $this->serialize($request, $client);
    }

    /**
     * Delete a client
     *
     * @Route(path="/{publicId}", methods={"DELETE"})
     * @IsGranted({"ROLE_ADMIN"})
     *
     */
    public function destroy(Request $request, string $publicId): JsonResponse
    {
        $client = $this->getClientById($publicId);
        $this->denyAccessUnlessGranted(ClientVoter::EDIT, $client);

        $this->getEm()->remove($client);

        $this->getEm()->flush();

        return $this->success(sprintf('Client "%s" deleted.', $client->getName()));
    }

    /**
     *
     * @Route("/{publicId}/transition", methods={"PATCH"})
     * @IsGranted({"ROLE_CLIENT_EDIT_ALL","ROLE_CLIENT_MANAGE_OWN"})
     *
     */
    public function transition(Request $request, Registry $workflowRegistry, string $publicId): JsonResponse
    {
        $client = $this->getClientById($publicId);
        $this->denyAccessUnlessGranted(ClientVoter::EDIT, $client);

        $params = $this->getParams($request);

        if ($params['transition']) {
            $workflowRegistry
                ->get($client)
                ->apply($client, $params['transition']);

            $this->getEm()->flush();
        }

        $meta = [
            'enabledTransitions' => $this->getEnabledTransitions($workflowRegistry, $client),
        ];

        return $this->serialize($request, $client, null, $meta);
    }

    /**
     * Get distribution history for client
     *
     * @Route(path="/{publicId}/history", methods={"GET"})
     * @IsGranted({"ROLE_CLIENT_VIEW_ALL","ROLE_CLIENT_MANAGE_OWN"})
     *
     */
    public function history(Request $request, string $publicId): JsonResponse
    {
        $client = $this->getClientById($publicId);
        $this->denyAccessUnlessGranted(ClientVoter::VIEW, $client);

        $distributionLines = $this->getEm()
            ->getRepository(BulkDistributionLineItem::class)
            ->getClientDistributionHistory($client);

        return $this->serialize($request, $distributionLines, new BulkDistributionLineItemTransformer());
    }

    /**
     * Get distribution history for client
     *
     * @Route(path="/{publicId}/history/export", methods={"GET"})
     * @IsGranted({"ROLE_CLIENT_VIEW_ALL","ROLE_CLIENT_MANAGE_OWN"})
     *
     */
    public function historyExport(Request $request, string $publicId): JsonResponse
    {
        $client = $this->getClientById($publicId);
        $this->denyAccessUnlessGranted(ClientVoter::VIEW, $client);

        $distributionLines = $this->getEm()
            ->getRepository(BulkDistributionLineItem::class)
            ->getClientDistributionHistory($client);

        $excel = new ClientDistributionExcel($distributionLines);

        $writer = $excel->buildExcel();
        // redirect output to client browser
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="ClientHistory.' . date('c') . '.xlsx"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit();
    }

    /**
     * Mark client as reviewed during the annual review period (or after)
     *
     * @param Request $request
     * @param Registry $workflowRegistry
     * @param string $publicId
     * @return JsonResponse
     *
     * @Route(path="/{publicId}/review", methods={"POST"})
     * @IsGranted({"ROLE_CLIENT_EDIT_ALL","ROLE_CLIENT_MANAGE_OWN"})
     */
    public function review(Request $request, Registry $workflowRegistry, string $publicId): JsonResponse
    {
        $client = $this->getClientById($publicId);
        $this->denyAccessUnlessGranted(ClientVoter::EDIT, $client);

        if ($client->canReview()) {
            $workflowRegistry->get($client)->apply($client, Client::TRANSITION_ACTIVATE);
            $client->setLastReviewedAt(new \DateTime());

            $this->getEm()->flush();
        }

        $meta = [
            'enabledTransitions' => $this->getEnabledTransitions($workflowRegistry, $client),
        ];

        return $this->serialize($request, $client, null, $meta);
    }

    /**
     * @Route(path="/{publicId}/transfer", methods={"POST"})
     * @IsGranted({"ROLE_CLIENT_EDIT_ALL","ROLE_CLIENT_MANAGE_OWN"})
     */
    public function transfer(Request $request, Registry $workflowRegistry, string $publicId): JsonResponse
    {
        $client = $this->getClientById($publicId);
        $this->denyAccessUnlessGranted(ClientVoter::TRANSFER, $client);

        /** @var User $user */
        $user = $this->getUser();

        if (!$user->getActivePartner()) {
            throw new UserInterfaceException("Current logged in user has no active partner or is an admin.");
        }

        $partner = $user->getActivePartner();

        $client->setPartner($partner);

        $workflowRegistry->get($client)->apply($client, Client::TRANSITION_ACTIVATE);

        $this->getEm()->flush();

        return $this->serialize($request, $client);
    }

    /**
     * @param Request $request
     * @param bool $viewAll
     * @return ParameterBag
     */
    protected function buildFilterParams(Request $request, bool $viewAll = false)
    {
        $params = new ParameterBag();

        if ($request->get('keyword')) {
            $params->set('keyword', $request->get('keyword'));
        }

        if ($request->get('partner')) {
            $partner = $this->getEm()->getRepository(Partner::class)->find($request->get('partner'));
            $params->set('partner', $partner);
        }

        if ($request->get('birthdate')) {
            $params->set('birthdate', new \DateTime($request->get('birthdate')));
        }

        /** @var User $user */
        $user = $this->getUser();

        // If the user isn't an admin,
        if (!$user->hasRole(Client::ROLE_VIEW_ALL) && !$viewAll) {
            $params->set('partner', $user->getActivePartner());
        }

        return $params;
    }

    protected function getDefaultTransformer(): ClientTransformer
    {
        return new ClientTransformer();
    }

    protected function getClientById(string $id): Client
    {
        /** @var Client $client */
        $client = $this->getRepository()->findOneByPublicId($id);

        if (!$client) {
            throw new NotFoundHttpException(sprintf('Unknown Client ID: %s', $id));
        }

        return $client;
    }

    /**
     * @param Registry $workflowRegistry
     * @param Client $client
     * @return String[]
     */
    protected function getEnabledTransitions(Registry $workflowRegistry, Client $client): array
    {
        $workflow = $workflowRegistry->get($client);
        $enabledTransitions = $workflow->getEnabledTransitions($client);

        return array_map(function (Transition $transition) use ($workflow) {
            $title = $workflow->getMetadataStore()->getTransitionMetadata($transition)['title'];
            return [
                'transition' => $transition->getName(),
                'title' => $title
            ];
        }, $enabledTransitions);
    }

    /**
     * Merge one or more Clients
     *
     * @Route(path="/merge", methods={"POST"})
     */
    public function merge(Request $request): JsonResponse
    {

        $request = $this->getParams($request);

        /** @var Client $target */
        $target = $this->getRepository()->findOneByPublicId($request['targetClient']);
        /** @var Client[] $sources */
        $sources = $this->getRepository()->findByPublicIds($request['sourceClients']);
        $context = $request['context'];

        foreach ($sources as $source) {
            $line_items = $this->getEm()
                ->getRepository(BulkDistributionLineItem::class)
                ->findBy(['client' => $source->getId()]);

            if ($line_items) {
                foreach ($line_items as $line_item) {
                    $line_item->setClient($target);
                }
            }

            $source->setMergedTo($target->getId());

            if (in_array('deactivate', $context)) {
                $source->setStatus(Client::STATUS_INACTIVE);
            }
        }

        $this->getEm()->flush();

        return $this->success();
    }
}

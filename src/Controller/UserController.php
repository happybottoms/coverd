<?php

namespace App\Controller;

use App\Entity\Group;
use App\Entity\Partner;
use App\Entity\PartnerUser;
use App\Entity\User;
use App\Entity\ValueObjects\Name;
use App\Security\UserVoter;
use App\Transformers\UserTransformer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class UserController
 * @package App\Controller
 *
 * @Route(path="/api/users")
 */
class UserController extends BaseController
{
    protected $defaultEntityName = User::class;

    /**
     * Get a list of Users
     *
     * @Route(path="", methods={"GET"})
     * @IsGranted({"ROLE_USER_VIEW"})
     *
     */
    public function index(Request $request): JsonResponse
    {
        $users = $this->getRepository()->findAll();

        return $this->serialize($request, $users);
    }

    /**
     * Get a list of Users by partner
     *
     * @Route(path="/partner/{partnerId<\d+>}", methods={"GET"})
     * @IsGranted({
     *     "ROLE_USER_VIEW",
     *     "ROLE_PARTNER_VIEW_ALL",
     *     "ROLE_PARTNER_MANAGE_OWN",
     * })
     *
     */
    public function partnerIndex(Request $request, string $partnerId): JsonResponse
    {
        $partner = $this->getRepository(Partner::class)->find($partnerId);
        $users = $this->getRepository()->findByPartner($partner);

        return $this->serialize($request, $users);
    }


    /**
     * Get a single User
     *
     * @Route(path="/{id<\d+>}", methods={"GET"})
     * @IsGranted({"ROLE_USER_VIEW"})
     *
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $user = $this->getUserById($id);

        $this->denyAccessUnlessGranted(UserVoter::VIEW, $user);

        return $this->serialize($request, $user);
    }

    /**
     * Save a new user
     *
     * @Route(path="", methods={"POST"})
     * @IsGranted({"ROLE_USER_EDIT"})
     *
     */
    public function store(Request $request): JsonResponse
    {
        $params = $this->getParams($request);
        $user = new User($params["email"]);

        if ($params['groups']) {
            $groups = array_map(function (array $group): Group {
                return $this->getEm()->getReference(Group::class, $group['id']);
            }, $params['groups']);

            $user->setGroups($groups);
        }

        if ($params['partners']) {
            $partners = array_map(function (array $partner): Partner {
                return $this->getEm()->getReference(Partner::class, $partner['id']);
            }, $params['partners']);

            $user->setPartners($partners);
        }

        $name = new Name(
            $params["name"]["firstName"],
            $params["name"]["lastName"]
        );

        $user->setName($name);
        $user->setPlainTextPassword($params['plainTextPassword']);

        $this->denyAccessUnlessGranted(UserVoter::EDIT, $user);

        $this->getEm()->persist($user);
        $this->getEm()->flush();

        return $this->serialize($request, $user);
    }

    /**
     * Whole or partial update of a user
     *
     * @Route(path="/{id<\d+>}", methods={"PATCH"})
     * @IsGranted({"ROLE_USER_EDIT"})
     *
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $params = $this->getParams($request);
        /** @var User $user */
        $user = $this->getUserById($id);

        $this->denyAccessUnlessGranted(UserVoter::EDIT, $user);

        if ($params['groups']) {
            $groups = array_map(function ($group) {
                return $this->getEm()->getReference(Group::class, $group['id']);
            }, $params['groups']);

            $user->setGroups($groups);
        }

        if ($params['partners']) {
            $partners = array_map(function ($partner) {
                return $this->getEm()->getReference(Partner::class, $partner['id']);
            }, $params['partners']);

            $user->setPartners($partners);
        }

        if ($params['name']) {
            $name = new Name($params['name']['firstName'], $params['name']['lastName']);
            $user->setName($name);
            unset($params['name']);
        }

        $user->applyChangesFromArray($params);

        $this->getEm()->persist($user);
        $this->getEm()->flush();

        return $this->serialize($request, $user);
    }

    /**
     * Delete a user
     *
     * @Route(path="/{id<\d+>}", methods={"DELETE"})
     * @IsGranted({"ROLE_USER_EDIT"})
     *
     */
    public function destroy(Request $request, string $id)
    {
        $user = $this->getUserById($id);

        $this->denyAccessUnlessGranted(UserVoter::EDIT, $user);

        $this->getEm()->remove($user);

        $this->getEm()->flush();

        return $this->success(sprintf('User "%s" deleted.', $user->getUsername()));
    }

    /**
     * Set the user's active partner
     *
     * @Route(path="/active-partner", methods={"POST"})
     * @IsGranted({"ROLE_USER_EDIT","ROLE_PARTNER_MANAGE_OWN"})
     *
     */
    public function setActivePartner(Request $request, SessionInterface $session)
    {
        $params = $this->getParams($request);
        $partnerId = (int) $params['active_partner'];
        $partner = $this->getRepository(Partner::class)->find($partnerId);
        /** @var User $user */
        $user = $this->getUser();

        if (!$user->isAssignedToPartner($partner)) {
            return $this->meta(false, "Invalid partner for this user");
        }

        $user->setActivePartner($partner);
        $this->getEm()->flush();

        return $this->meta(true, sprintf("Changed active partner to %s", $partner->getTitle()));
    }

    protected function getDefaultTransformer()
    {
        return new UserTransformer();
    }

    protected function getUserById($id): User
    {
        /** @var User $user */
        $user = $this->getRepository()->find($id);

        if (!$user) {
            throw new NotFoundHttpException(sprintf('Unknown User ID: %d', $id));
        }

        return $user;
    }
}

<?php

namespace App\Security;

use App\Entity\Order;
use App\Entity\Orders\MerchandiseOrder;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class MerchandiseOrderVoter extends Voter
{
    public const EDIT = 'EDIT';
    public const VIEW = 'VIEW';

    protected function supports($attribute, $subject)
    {
        if (!in_array($attribute, [self::VIEW, self::EDIT])) {
            return false;
        }

        if (!$subject instanceof MerchandiseOrder) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            // the user must be logged in; if not, deny access
            return false;
        }

        // you know $subject is a Post object, thanks to `supports()`
        /** @var MerchandiseOrder $order */
        $order = $subject;

        switch ($attribute) {
            case self::VIEW:
                return $this->canView($order, $user);
            case self::EDIT:
                return $this->canEdit($order, $user);
            default:
                throw new \LogicException('This code should not be reached!');
        }
    }

    private function canView(MerchandiseOrder $order, User $user): bool
    {
        // if they can edit, they can view
        if ($this->canEdit($order, $user)) {
            return true;
        }

        // If they have the view all role, they can view
        if ($user->hasRole(Order::ROLE_VIEW_ALL)) {
            return true;
        }

        // If they have the view role of merchandise order, they can view
        if ($user->hasRole(MerchandiseOrder::ROLE_VIEW)) {
            return true;
        }

        return false;
    }

    private function canEdit(MerchandiseOrder $order, User $user): bool
    {
        // Admin can do all the things
        if ($user->isAdmin()) {
            return true;
        }

        // If they have the edit all role, they can edit
        if ($user->hasRole(Order::ROLE_EDIT_ALL)) {
            return true;
        }

        // If they have the edit roll of merchandise order, they can edit
        if ($user->hasRole(MerchandiseOrder::ROLE_EDIT)) {
            return true;
        }

        return false;
    }
}

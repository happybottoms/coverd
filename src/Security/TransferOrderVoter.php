<?php

namespace App\Security;

use App\Entity\Orders\TransferOrder;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class TransferOrderVoter extends Voter
{
    public const VIEW = 'VIEW';
    public const EDIT = 'EDIT';

    protected function supports($attribute, $subject)
    {
        if (!in_array($attribute, [self::VIEW, self::EDIT])) {
            return false;
        }

        if (!$subject instanceof TransferOrder) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        switch ($attribute) {
            case self::VIEW:
                return $this->canView($subject, $user);
            case self::EDIT:
                return $this->canEdit($subject, $user);
            default:
                throw new \LogicException('This code should not be reached!');
        }
    }

    private function canView(TransferOrder $transferOrder, User $user)
    {
        if ($this->canEdit($transferOrder, $user)) {
            return true;
        }

        if ($user->hasRole(TransferOrder::ROLE_VIEW)) {
            return true;
        }

        return false;
    }

    private function canEdit(TransferOrder $transferOrder, User $user)
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->hasRole(TransferOrder::ROLE_EDIT)) {
            return true;
        }

        return false;
    }
}

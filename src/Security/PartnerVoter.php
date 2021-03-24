<?php

namespace App\Security;

use App\Entity\Partner;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class PartnerVoter extends Voter
{
    public const EDIT = 'EDIT';
    public const VIEW = 'VIEW';

    /**
     * @param string $attribute
     * @param mixed $subject
     * @return bool
     */
    protected function supports($attribute, $subject)
    {
        if (!in_array($attribute, [self::VIEW, self::EDIT])) {
            return false;
        }

        if (!$subject instanceof Partner) {
            return false;
        }

        return true;
    }

    /**
     * @param string $attribute
     * @param mixed $subject
     * @param TokenInterface $token
     * @return bool
     */
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

    private function canView(Partner $partner, User $user): bool
    {
        if ($this->canEdit($partner, $user)) {
            return true;
        }

        if ($user->hasRole(Partner::ROLE_VIEW_ALL)) {
            return true;
        }

        return false;
    }

    private function canEdit(Partner $partner, User $user): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->hasRole(Partner::ROLE_EDIT_ALL)) {
            return true;
        }

        $activePartner = $user->getActivePartner();

        return $user->hasRole(Partner::ROLE_MANAGE_OWN)
            && $activePartner
            && $partner->getId() === $activePartner->getId();
    }
}

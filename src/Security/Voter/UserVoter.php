<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class UserVoter extends Voter
{
    public const ONLY_NOT_CONNECTED = 'only_not_connected';
    public const CONNECTED = 'connected';
    public const ONLY_CONNECTED_NOT_CONFIRMED = 'only_connected_not_confirmed';
    public const ONLY_CONNECTED_CONFIRMED = 'only_connected_confirmed';


    protected function supports(string $attribute, $subject): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [self::ONLY_NOT_CONNECTED, self::CONNECTED, self::ONLY_CONNECTED_CONFIRMED]);
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case self::ONLY_NOT_CONNECTED:
                if (!$user instanceof UserInterface) {
                    return true;
                }
                break;
            case self::CONNECTED:
                if ($user instanceof UserInterface) {
                    return true;
                }
                break;
            case self::ONLY_CONNECTED_NOT_CONFIRMED:
                if ($user instanceof UserInterface) {
                    if (!$user->isConfirmed()) return true;
                }
                break;
            case self::ONLY_CONNECTED_CONFIRMED:
                if ($user instanceof UserInterface) {
                    if ($user->isConfirmed()) return true;
                }
                break;
        }

        return false;
    }
}

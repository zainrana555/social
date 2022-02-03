<?php
// src/Security/PostVoter.php
namespace App\Security;

use App\Entity\Post;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;

class PostVoter extends Voter
{
    // these strings are just invented: you can use anything
    const VIEW = 'view';
    const EDIT = 'edit';
    const DELETE = 'delete';

    /**
     * @var Security
     */
    private Security $security;

    /**
     * @param Security $security
     */
    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    /**
     * @param string $attribute
     * @param $subject
     * @return bool
     */
    protected function supports(string $attribute, $subject): bool
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [self::VIEW, self::EDIT, self::DELETE])) {
            return false;
        }

        // only vote on `Post` objects
        if (!$subject instanceof Post) {
            return false;
        }

        return true;
    }

    /**
     * @param string $attribute
     * @param $subject
     * @param TokenInterface $token
     * @return bool
     */
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            // the user must be logged in; if not, deny access
            return false;
        }

        // ROLE_ADMIN can do anything! The power!
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        // you know $subject is a Post object, thanks to `supports()`
        /** @var Post $post */
        $post = $subject;

        switch ($attribute) {
            case self::VIEW:
                return $this->canView($post, $user);
            case self::EDIT:
                return $this->canEdit($post, $user);
            case self::DELETE:
                return $this->canDelete($post, $user);
        }

        throw new \LogicException('This code should not be reached!');
    }

    /**
     * @param Post $post
     * @param User $user
     * @return bool
     */
    private function canView(Post $post, User $user): bool
    {
        if ($this->security->isGranted('ROLE_MODERATOR') || $this->security->isGranted('ROLE_POST_VIEW')) {
            return true;
        }

        // if they can edit, they can view
        if ($this->canEdit($post, $user)) {
            return true;
        }

        // the Post object could have, for example, a method `isPrivate()`
        // return !$post->isPrivate();
        return false;
    }

    /**
     * @param Post $post
     * @param User $user
     * @return bool
     */
    private function canEdit(Post $post, User $user): bool
    {
        if ($this->security->isGranted('ROLE_EDITOR') || $this->security->isGranted('ROLE_POST_EDIT')) {
            return true;
        }

        // if they can delete, they can edit
        if ($this->canDelete($post, $user)) {
            return true;
        }

        // this assumes that the Post object has a `getUser()` method
        return $user === $post->getUser();
    }

    /**
     * @param Post $post
     * @param User $user
     * @return bool
     */
    private function canDelete(Post $post, User $user): bool
    {
        if ($this->security->isGranted('ROLE_EDITOR') || $this->security->isGranted('ROLE_POST_DELETE')) {
            return true;
        }

        // this assumes that the Post object has a `getUser()` method
        return $user === $post->getUser();
    }
}
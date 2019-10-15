<?php

namespace Cms\BlockBundle\Model\Voter;

use Cms\BlockBundle\Model\Type\BlockTypeInterface;
use Cms\BlockBundle\Service\BlockFactoryInterface;
use Cms\BlockBundle\Service\BlockVoterAttributes;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;

trait BlockVoterTrait
{
    /**
     * @var BlockTypeInterface
     */
    protected $block;

    /**
     * @var AccessDecisionManagerInterface
     */
    private $decisionManager;

    /**
     * @var BlockFactoryInterface
     */
    private $blockFactory;

    /**
     * AbstractBaseVoter constructor.
     * @param AccessDecisionManagerInterface $decisionManager
     * @param BlockFactoryInterface $blockFactory
     */
    public function __construct(AccessDecisionManagerInterface $decisionManager, BlockFactoryInterface $blockFactory)
    {
        $this->decisionManager = $decisionManager;
        $this->blockFactory = $blockFactory;
    }

    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject)
    {
        if (!in_array($attribute, static::getAvailableAttributes())) {
            return false;
        }

        if (null === $subject || get_class($this) !== $this->blockFactory->getVoter($subject)) {
            return false;
        }


        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        if ($subject instanceof BlockTypeInterface) {
            $this->setBlock($subject);
        }


        switch($attribute) {
            case BlockVoterAttributes::VIEW :
                return $this->canView();
            case BlockVoterAttributes::CREATE :
                return $this->canCreate();
            case BlockVoterAttributes::EDIT :
                return $this->canEdit();
            case BlockVoterAttributes::SORTABLE :
                return $this->canSortable();
            case BlockVoterAttributes::DELETE :
                return $this->canDelete();
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    static public function getAvailableAttributes(): array
    {
        return BlockVoterAttributes::getAvailableAttributes();
    }

    /**
     * check if current user can view block
     *
     * @return bool
     */
    public function canView(): bool
    {
        return true;
    }

    /**
     * check if current user can create block
     *
     * @return bool
     */
    public function canCreate(): bool
    {
        return true;
    }

    /**
     * check if current user can edit block
     *
     * @return bool
     */
    public function canEdit(): bool
    {
        return true;
    }

    /**
     * check if current user can sortable block
     *
     * @return bool
     */
    public function canSortable(): bool
    {
        return true;
    }

    /**
     * check if current user can delete block
     *
     * @return bool
     */
    public function canDelete(): bool
    {
        return true;
    }

    /**
     * @return BlockTypeInterface
     */
    public function getBlock(): BlockTypeInterface
    {
        return $this->block;
    }

    /**
     * @param BlockTypeInterface $block
     * @return mixed
     */
    public function setBlock(BlockTypeInterface $block)
    {
        $this->block = $block;
        return $this;
    }
}
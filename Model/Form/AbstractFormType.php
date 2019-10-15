<?php

namespace Cms\BlockBundle\Model\Form;

use Cms\BlockBundle\Form\BlockParentType;

use Cms\BlockBundle\Model\Type\BlockTypeInterface;
use Symfony\Component\Form\AbstractType as BaseFormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractFormType extends BaseFormType implements BlockFormTypeInterface
{
    /**
     * @var BlockTypeInterface|null
     */
    protected $block = null;

    /**
     * @var string
     */
    protected $dataClass = null;

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' =>  $this->getDataClass(),
            'from_collection' => false,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        // add default data
        if (!$builder->getData() && $builder->getDataClass()) {
            $dataClass = $builder->getDataClass();
            $builder->setData(new $dataClass);
        }
    }

    /**
     * @inheritdoc
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        parent::buildView($view, $form, $options);

        $view->vars = array_replace($view->vars, [
            'block_name' => $this->getBlock()->getName(),
            'from_collection' => $options['from_collection'] ?? false,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        $blockName = $this->getBlock()->getName();
        return 'block' . ($blockName ? '_' . $blockName : '');
    }

    /**
     * @inheritdoc
     */
    public function getParent()
    {
        return BlockParentType::class;
    }

    /**
     * @return BlockTypeInterface|null
     */
    public function getBlock(): ?BlockTypeInterface
    {
        return $this->block;
    }

    /**
     * @inheritdoc
     */
    public function setBlock(?BlockTypeInterface $block): BlockFormTypeInterface
    {
        $this->block = $block;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getDataClass():? string
    {
        return $this->dataClass;
    }

    /**
     * @inheritdoc
     */
    public function setDataClass(string $dataClass = null): BlockFormTypeInterface
    {
        $this->dataClass = $dataClass;
        return $this;
    }
}
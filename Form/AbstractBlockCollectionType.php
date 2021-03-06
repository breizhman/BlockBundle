<?php

namespace Cms\BlockBundle\Form;

use Cms\BlockBundle\Collection\BlockCollection;
use Cms\BlockBundle\Model\Entity\BlockEntityInterface;

use Cms\BlockBundle\Service\BlockFormsInterface;
use Cms\BlockBundle\Service\Entity\BlockEntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;


use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class AbstractBlockCollectionType
 * @package Cms\BlockBundle\Form
 */
abstract class AbstractBlockCollectionType extends AbstractType implements DataTransformerInterface
{
    /**
     * @var BlockEntityManagerInterface
     */
    protected $blockEntityManager = null;

    /**
     * @var BlockFormsInterface|null
     */
    protected $blockForms = null;

    /**
     * AbstractBlockCollectionType constructor.
     *
     * @param BlockEntityManagerInterface $blockEntityManager
     * @param BlockFormsInterface|null    $blockForms
     */
    public function __construct(BlockEntityManagerInterface $blockEntityManager, BlockFormsInterface $blockForms = null)
    {
        $this->blockEntityManager = $blockEntityManager;
        $this->blockForms = $blockForms;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $forms = $loadOptions = [];
        if (isset($options['entries'])) {
            if (isset($options['entries']['type']) && $options['entries']['type'] === 'entity') {
                $loadOptions = ['entity' => $options['entries']];
            } else {
                $forms = (array) $options['entries'];
            }
        }

        if (empty($forms) && $this->blockForms instanceof BlockFormsInterface) {
            $forms = $this->blockForms->load($loadOptions);
        }

        foreach ($forms as $name => $formType) {

            $builder
                ->add($name, CollectionType::class, array_replace([
                    'entry_type' => $formType,
                    'entry_options' => [
                        'label' => sprintf('block.%s.title', $name),
                        'required' => false,
                        'from_collection' => true,
                        'attr' => [
                            'data-block-type' => $name,
                        ],
                    ],
                    'label' => false,
                    'allow_add' => true,
                    'allow_delete' => true,
                    'prototype' => true,
                ], ($options['collection_options'] ?? [])))
            ;
        }

        $builder->add('block_order', HiddenType::class);

        $builder->addViewTransformer($this);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'data_class' => null,
            'entries' => [],
            'collection_options' => [],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function transform($data)
    {
        $transformData = [
            'block_order' => [],
        ];

        if ($data) {
            /** @var BlockEntityInterface $block */
            foreach ($data as $block) {
                if ($block instanceof BlockEntityInterface) {
                    $transformData[$block->getBlockType()][] = $block;
                    $transformData['block_order'][] = json_encode([
                        'name' => $block->getBlockType(),
                        'pos' => (count($transformData[$block->getBlockType()]) - 1),
                    ]);
                }
            }
        }

        $transformData['block_order'] = json_encode($transformData['block_order']);

        return $transformData;
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($data)
    {
        $reverseData = [];
        if (isset($data['block_order'])) {
            foreach (json_decode($data['block_order'], true) as $order) {
                // get block by name and pos
                $order = is_string($order) ? json_decode($order, true) : $order;
                if (isset($order['name'], $order['pos'], $data[$order['name']][$order['pos']])) {
                    $reverseData[] = $data[$order['name']][$order['pos']];
                }
            }
        }

        return new BlockCollection($this->blockEntityManager, $reverseData);
    }

    public function getParent()
    {
        return FormType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'block_collection';
    }
}
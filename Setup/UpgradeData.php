<?php
/**
 * Astound
 * NOTICE OF LICENSE
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to codemaster@astoundcommerce.com so we can send you a copy immediately.
 *
 * @category  Affirm
 * @package   Astound_Affirm
 * @copyright Copyright (c) 2016 Astound, Inc. (http://www.astoundcommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Astound\Affirm\Setup;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * Upgrade data
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * EAV setup factory
     *
     * @var EavSetupFactory
     */
    protected $eavSetupFactory;

    /**
     * Init
     *
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
            EavSetupFactory $eavSetupFactory
    )
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        ;
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        if (version_compare($context->getVersion(), '0.4.2', '<')) {
            /**
             * Add attributes to the product eav/attribute
             */
            $eavSetup->addAttribute(
                    \Magento\Catalog\Model\Product::ENTITY,
                    'affirm_product_mfp',
                    [
                            'type' => 'varchar',
                            'backend' => '',
                            'frontend' => '',
                            'label' => 'Multiple Financing Program value',
                            'input' => 'text',
                            'class' => '',
                            'source' => '',
                            'global' => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_WEBSITE,
                            'group' => 'General',
                            'visible' => 1,
                            'required' => 0,
                            'user_defined' => 0,
                            'searchable' => 0,
                            'filterable' => 0,
                            'comparable' => 0,
                            'visible_on_front' => 0,
                            'used_in_product_listing' => 0,
                            'unique' => 0,
                            'apply_to' => '',
                    ]
            );

            /**
             * Add attributes to the category eav/attribute
             */
            $eavSetup->addAttribute(
                    \Magento\Catalog\Model\Category::ENTITY,
                    'affirm_category_mfp',
                    [
                            'type' => 'varchar',
                            'label' => 'Multiple Financing Program value',
                            'input' => 'text',
                            'required' => 0,
                            'sort_order' => 100,
                            'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_WEBSITE,
                            'group' => 'General Information',
                            'is_used_in_grid' => 0,
                            'is_visible_in_grid' => 0,
                            'is_filterable_in_grid' => 0,
                    ]
            );
        }

        if (version_compare($context->getVersion(), '0.4.3', '<')) {
            /**
             * Add attributes to the product eav/attribute
             */
            $eavSetup->addAttribute(
                    \Magento\Catalog\Model\Product::ENTITY,
                    'affirm_product_mfp_type',
                    [
                            'type' => 'int',
                            'backend' => '',
                            'frontend' => '',
                            'label' => 'Multiple Financing Program type',
                            'input' => 'select',
                            'class' => '',
                            'source' => 'Astound\Affirm\Model\Entity\Attribute\Source\FinancingProgramType',
                            'global' => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_WEBSITE,
                            'group' => 'General',
                            'visible' => 1,
                            'required' => 0,
                            'user_defined' => 0,
                            'searchable' => 0,
                            'filterable' => 0,
                            'comparable' => 0,
                            'visible_on_front' => 0,
                            'used_in_product_listing' => 0,
                            'unique' => 0,
                            'apply_to' => '',
                    ]
            );
            $eavSetup->addAttribute(
                    \Magento\Catalog\Model\Product::ENTITY,
                    'affirm_product_mfp_priority',
                    [
                            'type' => 'int',
                            'backend' => '',
                            'frontend' => '',
                            'label' => 'Multiple Financing Program priority',
                            'input' => 'text',
                            'class' => 'validate-number',
                            'global' => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_WEBSITE,
                            'group' => 'General',
                            'visible' => 1,
                            'required' => 0,
                            'user_defined' => 0,
                            'searchable' => 0,
                            'filterable' => 0,
                            'comparable' => 0,
                            'visible_on_front' => 0,
                            'used_in_product_listing' => 0,
                            'unique' => 0,
                            'apply_to' => '',
                    ]
            );

            /**
             * Add attributes to the category eav/attribute
             */
            $eavSetup->addAttribute(
                    \Magento\Catalog\Model\Category::ENTITY,
                    'affirm_category_mfp_type',
                    [
                            'type' => 'int',
                            'label' => 'Multiple Financing Program type',
                            'input' => 'select',
                            'required' => 0,
                            'sort_order' => 101,
                            'source' => 'Astound\Affirm\Model\Entity\Attribute\Source\FinancingProgramType',
                            'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_WEBSITE,
                            'group' => 'General Information',
                            'is_used_in_grid' => 0,
                            'is_visible_in_grid' => 0,
                            'is_filterable_in_grid' => 0,
                    ]
            );
            $eavSetup->addAttribute(
                    \Magento\Catalog\Model\Category::ENTITY,
                    'affirm_category_mfp_priority',
                    [
                            'type' => 'int',
                            'label' => 'Multiple Financing Program priority',
                            'input' => 'text',
                            'class' => 'validate-number',
                            'required' => 0,
                            'sort_order' => 102,
                            'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_WEBSITE,
                            'group' => 'General Information',
                            'is_used_in_grid' => 0,
                            'is_visible_in_grid' => 0,
                            'is_filterable_in_grid' => 0,
                    ]
            );
        }

        if (version_compare($context->getVersion(), '1.0.2', '<')) {
            $eavSetup->addAttribute(
                    \Magento\Catalog\Model\Product::ENTITY,
                    'affirm_product_mfp_start_date',
                    [
                            'type' => 'datetime',
                            'backend' => '',
                            'frontend' => '',
                            'label' => 'Start date for time based Financing Program value',
                            'input' => 'date',
                            'class' => '',
                            'global' => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_WEBSITE,
                            'group' => 'General',
                            'visible' => 1,
                            'required' => 0,
                            'user_defined' => 0,
                            'searchable' => 0,
                            'filterable' => 0,
                            'comparable' => 0,
                            'visible_on_front' => 0,
                            'used_in_product_listing' => 1,
                            'unique' => 0,
                            'apply_to' => '',
                    ]
            );

            $eavSetup->addAttribute(
                    \Magento\Catalog\Model\Product::ENTITY,
                    'affirm_product_mfp_end_date',
                    [
                            'type' => 'datetime',
                            'backend' => '',
                            'frontend' => '',
                            'label' => 'End date for time based Financing Program value',
                            'input' => 'date',
                            'class' => '',
                            'global' => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_WEBSITE,
                            'group' => 'General',
                            'visible' => 1,
                            'required' => 0,
                            'user_defined' => 0,
                            'searchable' => 0,
                            'filterable' => 0,
                            'comparable' => 0,
                            'visible_on_front' => 0,
                            'used_in_product_listing' => 1,
                            'unique' => 0,
                            'apply_to' => '',
                    ]
            );

            $eavSetup->addAttribute(
                    \Magento\Catalog\Model\Category::ENTITY,
                    'affirm_category_mfp_start_date',
                    [
                            'type' => 'datetime',
                            'label' => 'Start date for time based Financing Program value',
                            'input' => 'date',
                            'class' => '',
                            'required' => 0,
                            'sort_order' => 103,
                            'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_WEBSITE,
                            'group' => 'General Information',
                            'is_used_in_grid' => 0,
                            'is_visible_in_grid' => 0,
                            'is_filterable_in_grid' => 0,
                            'used_in_product_listing' => 1,
                    ]
            );

            $eavSetup->addAttribute(
                    \Magento\Catalog\Model\Category::ENTITY,
                    'affirm_category_mfp_end_date',
                    [
                            'type' => 'datetime',
                            'label' => 'End date for time based Financing Program value',
                            'input' => 'date',
                            'class' => '',
                            'required' => 0,
                            'sort_order' => 104,
                            'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_WEBSITE,
                            'group' => 'General Information',
                            'is_used_in_grid' => 0,
                            'is_visible_in_grid' => 0,
                            'is_filterable_in_grid' => 0,
                            'used_in_product_listing' => 1,
                    ]
            );
        }

        if (version_compare($context->getVersion(), '1.0.3', '<')) {

            /**
             * Add attributes to the category eav/attribute
             */
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Category::ENTITY,
                'affirm_category_promo_id',
                [
                    'type' => 'varchar',
                    'label' => 'Affirm Promo ID',
                    'input' => 'text',
                    'required' => 0,
                    'sort_order' => 105,
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_WEBSITE,
                    'group' => 'General Information',
                    'is_used_in_grid' => 0,
                    'is_visible_in_grid' => 0,
                    'is_filterable_in_grid' => 0,
                ]
            );

            /**
             * Add attributes to the product eav/attribute
             */
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'affirm_product_promo_id',
                [
                    'type' => 'varchar',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'Affirm Promo ID',
                    'input' => 'text',
                    'class' => '',
                    'source' => '',
                    'global' => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_WEBSITE,
                    'group' => 'General',
                    'visible' => 1,
                    'required' => 0,
                    'user_defined' => 0,
                    'searchable' => 0,
                    'filterable' => 0,
                    'comparable' => 0,
                    'visible_on_front' => 0,
                    'used_in_product_listing' => 1,
                    'unique' => 0,
                    'apply_to' => '',
                ]
            );
        }
    }
}

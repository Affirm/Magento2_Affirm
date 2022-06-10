<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Astound\Affirm\Setup\Patch\Data;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;


/**
* Patch is mechanism, that allows to do atomic upgrade data changes
*/
class AddProductCategoryAttributes implements
    DataPatchInterface,
    PatchRevertableInterface
{
    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    private $moduleDataSetup;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * Do Upgrade
     *
     * @return void
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

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

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * Do revert.
     * @return void
     */
    public function revert()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, 'affirm_product_mfp');
		$eavSetup->removeAttribute(\Magento\Catalog\Model\Category::ENTITY, 'affirm_category_mfp');
		$eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, 'affirm_product_mfp_type');
		$eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, 'affirm_product_mfp_priority');
		$eavSetup->removeAttribute(\Magento\Catalog\Model\Category::ENTITY, 'affirm_category_mfp_type');
		$eavSetup->removeAttribute(\Magento\Catalog\Model\Category::ENTITY, 'affirm_category_mfp_priority');
		$eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, 'affirm_product_mfp_start_date');
		$eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, 'affirm_product_mfp_end_date');
		$eavSetup->removeAttribute(\Magento\Catalog\Model\Category::ENTITY, 'affirm_category_mfp_start_date');
		$eavSetup->removeAttribute(\Magento\Catalog\Model\Category::ENTITY, 'affirm_category_mfp_end_date');
		$eavSetup->removeAttribute(\Magento\Catalog\Model\Category::ENTITY, 'affirm_category_promo_id');
		$eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, 'affirm_product_promo_id');

        $this->moduleDataSetup->getConnection()->endSetup();
    }


    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }
}

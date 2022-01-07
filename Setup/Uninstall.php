<?php
/**
 * @category  Affirm
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Astound\Affirm\Setup;

use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Customer\Model\Customer;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Category;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\UninstallInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\SchemaSetupInterface;


/**
 * Uninstall
 */
class Uninstall implements UninstallInterface
{
    /**
     * EAV setup factory
     *
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * Customer setup factory
     *
     * @var CustomerSetupFactory
     */
    protected $customerSetupFactory;

    /**
     * Init
     *
     * @param EavSetupFactory $eavSetupFactory
     * @param CustomerSetupFactory $customerSetupFactory
     * @param ModuleDataSetupInterface $setup
     */
    public function __construct(
        EavSetupFactory $eavSetupFactory,
        CustomerSetupFactory $customerSetupFactory,
		ModuleDataSetupInterface $setup
    ) {
		$this->eavSetupFactory = $eavSetupFactory;
        $this->customerSetupFactory = $customerSetupFactory;
		$this->setup = $setup;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function uninstall(SchemaSetupInterface $schemaSetup, ModuleContextInterface $context)
    {
        /** @var CustomerSetup $customerSetup */
        $attributeCode = 'affirm_customer_mfp';
        $customerSetup = $this->customerSetupFactory->create(['setup' => $this->setup]);
		$customerSetup->removeAttribute(Customer::ENTITY, $attributeCode);

        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->setup]);
		$eavSetup->removeAttribute(Product::ENTITY, 'affirm_product_mfp');
		$eavSetup->removeAttribute(Category::ENTITY, 'affirm_category_mfp');
		$eavSetup->removeAttribute(Product::ENTITY, 'affirm_product_mfp_type');
		$eavSetup->removeAttribute(Product::ENTITY, 'affirm_product_mfp_priority');
		$eavSetup->removeAttribute(Category::ENTITY, 'affirm_category_mfp_type');
		$eavSetup->removeAttribute(Category::ENTITY, 'affirm_category_mfp_priority');
		$eavSetup->removeAttribute(Product::ENTITY, 'affirm_product_mfp_start_date');
		$eavSetup->removeAttribute(Product::ENTITY, 'affirm_product_mfp_end_date');
		$eavSetup->removeAttribute(Category::ENTITY, 'affirm_category_mfp_start_date');
		$eavSetup->removeAttribute(Category::ENTITY, 'affirm_category_mfp_end_date');
		$eavSetup->removeAttribute(Category::ENTITY, 'affirm_category_promo_id');
		$eavSetup->removeAttribute(Product::ENTITY, 'affirm_product_promo_id');

		// Drop tables
		$schemaSetup->startSetup();
		$this->dropTable($schemaSetup, 'astound_affirm_rule');
		$this->dropTable($schemaSetup, 'astound_affirm_attribute');
		$schemaSetup->endSetup();

    }

	 /**
     * @param SchemaSetupInterface $schemaSetup
     * @param string $tableName
     */
    private function dropTable(SchemaSetupInterface $schemaSetup, $tableName)
    {
        $connection = $schemaSetup->getConnection();
        $connection->dropTable($schemaSetup->getTable($tableName));
    }
}

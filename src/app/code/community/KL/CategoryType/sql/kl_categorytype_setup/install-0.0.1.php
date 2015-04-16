<?php
    /* @var $installer Mage_Catalog_Model_Resource_Eav_Mysql4_Setup */
    $installer = $this;

    /* @var $category Mage_Catalog_Model_Category */
    $category = Mage::getModel('catalog/category');

    $entityTypeId     = $installer->getEntityTypeId(Mage_Catalog_Model_Category::ENTITY);
    $attributeSetId   = $installer->getDefaultAttributeSetId($entityTypeId);
    $attributeGroupId = $installer->getDefaultAttributeGroupId($entityTypeId, $attributeSetId);

    $installer->addAttribute(
        $entityTypeId,
        'type',
        array(
            'default'      => 'standard',
            'global'       => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
            'group'        => 'Display Settings',
            'input'        => 'select',
            'label'        => 'Type',
            'required'     => false,
            'sort_order'   => 25,
            'source'       => 'categorytype/eav_entity_attribute_source_type',
            'type'         => 'text',
            'user_defined' => true
        )
    );

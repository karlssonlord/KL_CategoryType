<?php
class KL_CategoryType_Model_Eav_Entity_Attribute_Source_Type extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    /**
     * Retrieve all options array
     *
     * @return array
     */
    public function getAllOptions()
    {
        $types = (array) Mage::getConfig()->getNode('default/categorytype/types');

        foreach($types as $key => $value) {
            $this->_options[] = array(
                'label' => $value->name,
                'value' => $key
            );
        }

        return $this->_options;
    }
}
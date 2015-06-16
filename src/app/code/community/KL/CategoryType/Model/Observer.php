<?php
class KL_CategoryType_Model_Observer
{
    /**
     * Set template
     *
     * @param array  $haystack
     * @param string $template
     *
     * @return array|bool
     */   
    protected function _setTemplate($haystack, $template) {
        if (array_key_exists('reference', $haystack)) {
            if (array_key_exists('_attribute', $haystack['reference'])) {
                if (array_key_exists('name', $haystack['reference']['_attribute']) && $haystack['reference']['_attribute']['name'] == 'category.products') {
                    if (array_key_exists('_value', $haystack['reference'])) {
                        if (array_key_exists('action', $haystack['reference']['_value'])) {
                            if (array_key_exists('_attribute', $haystack['reference']['_value']['action'])) {
                                if (array_key_exists('method', $haystack['reference']['_value']['action']['_attribute']) && $haystack['reference']['_value']['action']['_attribute']['method'] == 'setTemplate') {
                                    $haystack['reference']['_value']['action']['_value']['template'] = $template;

                                    return $haystack;
                                }
                            }
                        }
                    }
                }
            }
        }

        foreach ($haystack as $key => $element) {
            if (is_array($element)) {
                $result = $this->_setTemplate($element, $template);

                if (is_array($result)) {
                    $haystack[$key] = $result;

                    return $haystack;
                }
            }
        }

        return false;
    }

    /**
     * Skip declaration
     *
     * This method removes the first row, a stupid way to just remove
     * the XML declaration that Mage_Xml_Generator::arrayToXml() adds.
     *
     * @param string $xml
     *
     * @return string
     */
    protected function _skipDeclaration($xml)
    {
        return preg_replace('/^.+\n/', '', $xml);
    }

    /**
     * Parse
     *
     * @param string $xml
     *
     * @return array
     */
    protected function _parse($xml)
    {
        $xmlParser = new Mage_Xml_Parser();
        $xmlParser->loadXML($xml);

        return $xmlParser->xmlToArray();
    }

    /**
     * Generate
     *
     * @param array $array
     *
     * @return string
     */
    protected function _generate($array)
    {
        $xmlGenerator = new Mage_Xml_Generator();
        $xml = $xmlGenerator->arrayToXml($array);
        $string = $this->_skipDeclaration($xml);

        return $string;
    }

    /**
     * Add tmp root node
     *
     * @param string $xml
     *
     * @return string
     */
    protected function _addTmpRootNode($xml)
    {
        return sprintf('<tmp_root>%s</tmp_root>', $xml);
    }

    /**
     * Remove tmp root node
     *
     * @param string $xml
     *
     * @return string
     */
    protected function _removeTmpRootNode($xml)
    {
        return preg_replace('/\<\/?tmp_root>/', '', $xml);
    }

    /**
     * Set custom layout update
     *
     * @param Varien_Event_Observer $observer
     *
     * @return KL_CategoryType_Model_Observer
     */
    public function setCustomLayoutUpdate(Varien_Event_Observer $observer)
    {
        /**
         * Use Magento registry to make sure we don't end up in
         * an infinite loop since we're trying to save an object
         * when it's saved
         */
        if (Mage::registry('kl_categorytype')) {
            return $this;
        }

        Mage::register('kl_categorytype', true);

        $category = $observer->getEvent()->getCategory();

        /**
         * Get the category attribute type that is set up by
         * this module and defaults to "standard"
         */
        $categoryType = $category->getType();

        /**
         * Get the custom layout handle attribute of the category,
         * this is where we will override which template to use
         * depending on the category type
         */
        $customLayoutUpdate = trim($category->getCustomLayoutUpdate());

        /**
         * Add a temporary root node to make sure there's only one
         * root node in the attribute (the input field in admin lacks
         * any form of validation)
         */
        $tmpCustomLayoutUpdate = $this->_addTmpRootNode($customLayoutUpdate);

        $xmlAsArray = $this->_parse($tmpCustomLayoutUpdate);

        /**
         * The standard categories don't need any template to be set
         * via the custom layout updates but if the XML node already
         * exists we chose to update instead of removing it
         */
        if ($categoryType === 'standard') {
            if ($customLayoutUpdate) {
                $modifiedValue = $this->_setTemplate($xmlAsArray, sprintf('catalog/category/view.phtml', $categoryType));

                $xml = $this->_generate($modifiedValue);
            }
        } else {
            /**
             * This is the layout update that will be used if no
             * custom layout update is already specified
             */
            $xml = sprintf('<reference name="category.products"><action method="setTemplate"><template>catalog/category/view/%s.phtml</template></action></reference>', $categoryType);

            /**
             * If a custom layout is specified...
             */
            if ($customLayoutUpdate) {
                /**
                 * ... and if we successfully can identify and edit an
                 * existing template update...
                 */
                $modifiedValue = $this->_setTemplate($xmlAsArray, sprintf('catalog/category/view/%s.phtml', $categoryType));

                if (is_array($modifiedValue)) {
                    $xml = $this->_generate($modifiedValue);

                /**
                 * ... elsewise just append the node we should have
                 * added if no custom layout update existed...
                 */
                } else {
                    $xml = $customLayoutUpdate . $xml;
                }
            }
        }

        /**
         * If the $xml variable has been set we should have
         * some data to save to the custom layout update attribute
         */
        if (isset($xml)) {
            $customLayoutUpdate = $this->_removeTmpRootNode($xml);

            /**
             * Finally, save the value to the category
             */
            $category->setCustomLayoutUpdate($customLayoutUpdate);
        }

        return $this;
    }
}

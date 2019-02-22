<?php
namespace sky\slack;

use yii\helpers\ArrayHelper;

/**
 * @property StoreCollection $textStore
 * @property StoreCollection $fieldsStore
 */
class AttachmentCollection extends \sky\slack\StoreCollection
{
    protected $_text;
    
    protected $_fields;
    
    public $color = '#656565';
    
    public function getTextStore()
    {
        if (!$this->_text instanceof StoreCollection) {
            $this->_text = new StoreCollection();
        }
        return $this->_text;
    }
    
    public function addText($text)
    {
        $this->textStore->addValue($text);
        return $this;
    }
    
    public function getTextAll()
    {
        return implode("\n", $this->getTextStore()->getValue());
    }
    
    public function getFieldsStore()
    {
        if (!$this->_fields instanceof StoreCollection) {
            $this->_fields = new StoreCollection();
        }
        return $this->_fields;
    }
    
    public function addField($data)
    {
        $data = array_merge([
            'short' => true,
            'title' => 'No Title',
            'value' => null,
        ], $data);
        $this->fieldsStore->addValue($data);
        return $this;
    }
    
    public function addFields($data)
    {
        foreach ($data as $field) {
            $this->addField($field);
        }
        return $this;
    }
    
    public function getFields()
    {
        return $this->fieldsStore->getValue();
    }
    
    public function toArray()
    {
        return [
            'text' => $this->getTextAll(),
            'fields' => $this->getFieldsStore()->getValue(),
        ];
    }
}
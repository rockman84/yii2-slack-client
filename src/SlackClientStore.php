<?php
namespace sky\slack;

use yii\helpers\ArrayHelper;
use sky\slack\StoreCollection;

/**
 * @property StoreCollection $attachStore
 */
class SlackClientStore extends \sky\slack\SlackClient
{
    protected $_attachStore;
    
    public function getAttachStore()
    {
        if (!$this->_attachStore) {
            $this->_attachStore = new StoreCollection();
        }
        return $this->_attachStore;
    }
    
    public function getAttachment($key = 'default')
    {
        $collection = $this->attachStore->getValue($key);
        if ($collection instanceof AttachmentCollection) {
            return $collection;
        }
        $attachment = new AttachmentCollection();
        $this->attachStore->addValue($attachment, $key);
        return $attachment;
    }
    
    public function addText($text, $key = 'default')
    {
        $this->getAttachment($key)->addText($text);
        return $this;
    }
    
    public function addField($field, $key = 'default')
    {
        $this->getAttachment($key)->addField($field);
        return $this;
    }
    
    public function getAttachments()
    {
        $data = [];
        foreach ($this->getAttachStore()->getValue() as $attach) {
            $data[] = [
                'color' => $attach->color,
                'text' => $attach->getTextAll(),
                'fields' => $attach->getFields(),
            ];
        }
        return $data;
    }
    
    public function sendCollection($text = null) {
        if (!$this->getAttachments()) {
            return false;
        }
        $result = $this->send([
            'text' => $text,
            'attachments' => $this->getAttachments(),
        ]);
        $this->_attachStore = [];
        return $result;
    }
}
<?php

class ResourceMentionData extends SubscriptionData {
    const NEW_MENTIONS = "new_mentions";
    const OLD_MENTIONS = "old_mentions";

    public function __construct($json_data){
        parent::__construct($json_data);
        if(!isset($this->data[self::NEW_MENTIONS])) $this->data[self::NEW_MENTIONS] = Array();
        if(!isset($this->data[self::OLD_MENTIONS])) $this->data[self::OLD_MENTIONS] = Array();
    }

    public function setNewData($data){
        $this->addNewMention($data);
        return true;
    }

    public function addNewMention($mentionid){
        $this->data[self::NEW_MENTIONS][] = $mentionid;
    }

    public function getMentions(){
        $new_mentions = $this->data[self::NEW_MENTIONS];
        if(!empty($new_mentions)) return $new_mentions;
        else return $this->data[self::OLD_MENTIONS];
    }

    public function resetData(){
        $new_mentions = $this->data[self::NEW_MENTIONS];
        if(!empty($new_mentions)) {
            $old_mentions = $new_mentions;
        } else {
            $old_mentions = $this->data[self::OLD_MENTIONS];
            $old_mentions = is_null($old_mentions) ? Array() : $old_mentions;
        }

        $this->data = Array(
            self::OLD_MENTIONS => $old_mentions,
            self::NEW_MENTIONS => Array(),
        );
    }

    public function getNewData(){
        return $this->getMentions();
    }

    public function initData($subscription){
        $subscription->resetNewDataScicrunch();
        $subscription->resetNewDataEmail();
    }
}

?>

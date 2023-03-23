<?php

class IDSchema extends AbstractSchema
{
    function compile(){
        $id = $this->id;
        if (!empty($id)) {
            $this->data["@id"] = $id;
        }
    }
}

?>

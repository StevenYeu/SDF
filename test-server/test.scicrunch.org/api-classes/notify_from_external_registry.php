<?php

class APIStratNotifyFromExternalRegistry{
    private $request;

    public function __construct($request){
        $this->request = $request;
    }

    public function execute(){
        $nifid = $this->request['uri_args'][3];
        $sources_holder = new Sources();
        $sources = $sources_holder->getAllSources();
        if(isset($sources[$nifid])){
            $source = $sources[$nifid];
            $text_message = "The data source " . $source->getTitle() . " (" . $source->nif . ") has notified us that new data is available.";
            $message = \helper\buildEmailMessage(Array($text_message));
            $to = "nif-curators@mail.neuinfo.org";
            $subject = "New data from " . $source->getTitle();
            \helper\sendEmail($to, $message, $text_message, $subject);
            return null;
        }else{
            throw new Exception("invalid view id");
        }
    }
}

?>

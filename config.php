<?php
    // describe the form - supported field are in the list
    $emptyForm = <<<EOD
    {"form": [
        {
            "type": "message",
            "name": "note",
            "description": "Connect to ws to import messages"
        },
        {
            "name": "url",
            "description": "url",
            "type": "string",
            "required": true
        },
        {
            "name": "user",
            "description": "user",
            "type": "string",
            "required": true
        },
        {
            "name": "password",
            "description": "password",
            "type": "string",
            "required": true
        },
        {
            "name": "cf",
            "description": "Codice fiscale",
            "type": "string",
            "required": false
        },
        {
            "name": "stato",
            "description": "Stato",
            "type": "string",
            "required": true
        }
  ]}
  EOD
?>

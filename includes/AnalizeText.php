<?php

class AnalizeText
{
    function detectInput($document){

        if (!isset($document['user_channel'])) {
            throw new Exception('Document user-channel is not set!');
        }
        if (!isset($document['customer_channel'])) {
            throw new Exception('Document customer_channel is not set!');
        }


    }
}

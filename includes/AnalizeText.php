<?php

class AnalizeText
{

    protected function normalizeValues($input)
    {
      $output = preg_replace('/\w{1,}:/', '', $input);
      return (float) $output;
    }

    function detectInput($document)
    {

        if (!isset($document['user_channel'])) {
            throw new Exception('Document user-channel is not set!');
        }
        if (!isset($document['customer_channel'])) {
            throw new Exception('Document customer_channel is not set!');
        }

        // remove whitespace form text from text <
        $silence_text_user = preg_replace('/\s/', '', $document['user_channel']);
        $silence_text_customer = preg_replace('/\s/', '', $document['customer_channel']);

        // Get silence values <
        // Get start values
        preg_match_all('/start:\d{1,}\D\d{1,}/', $silence_text_user, $output_user_silence_start);
        $output_user_silence_start = array_pop($output_user_silence_start);

        preg_match_all('/start:\d{1,}\D\d{1,}/', $silence_text_customer, $output_customer_silence_start);
        $output_customer_silence_start = array_pop($output_customer_silence_start);

        // Get end values <
        preg_match_all('/end:\d{1,}\D\d{1,}/', $silence_text_user, $output_user_silence_end);
        $output_user_silence_end = array_pop($output_user_silence_end);

        preg_match_all('/end:\d{1,}\D\d{1,}/', $silence_text_customer, $output_customer_silence_end);
        $output_customer_silence_end = array_pop($output_customer_silence_end);

        // Get duration values <
        preg_match_all('/duration:\d{1,}\D\d{1,}/', $silence_text_user, $output_user_silence_duration);
        $output_user_silence_duration = array_pop($output_user_silence_duration);

        preg_match_all('/duration:\d{1,}\D\d{1,}/', $silence_text_customer, $output_customer_silence_duration);
        $output_customer_silence_duration = array_pop($output_customer_silence_duration);


        // Combined array user <
        //$combined_values_user = array();
        $num_items = count($output_user_silence_start);
        for ($i = 0; $i < $num_items; ++$i) {
            $combined_values_user[] = array(
                'start' => $this->normalizeValues($output_user_silence_start[$i]),
                'end' => $this->normalizeValues($output_user_silence_end[$i]),
                'duration' => $this->normalizeValues($output_user_silence_duration[$i])
            );
        }

        
        // Combined array customer <
       // $combined_arr_customer = array();
        $num_items = count($output_customer_silence_start);
        for ($i = 0; $i < $num_items; ++$i) {
            $combined_arr_customer[] = array(
                'start' => $this->normalizeValues($output_customer_silence_start[$i]),
                'end' => $this->normalizeValues($output_customer_silence_end[$i]),
                'duration' => $this->normalizeValues($output_customer_silence_duration[$i])
            );
        }



    }
}

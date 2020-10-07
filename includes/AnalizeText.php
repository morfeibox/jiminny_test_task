<?php

class AnalizeText
{

    /**
     * @param $input
     * @return float
     */
    protected function normalizeValues($input): float
    {
        $output = preg_replace('/\w{1,}:/', '', $input);
        return (float) $output;
    }

    /**
     * @param $input
     * @return array
     */
    protected function invertToActiveSpeech($input): array
    {

        $num_items = count($input);
        for ($i = 0; $i < $num_items; ++$i) {
            if ($i == 0) {
                $output[] = [0, $input[$i]['start']];
            }
            $output[] = [$input[$i]['end'], $input[$i + 1]['start'] ?? null];

        }
        return $output;
    }

    /**
     * @param $silence_start
     * @param $silence_end
     * @param $silence_duration
     * @return array
     */
    protected function combineDataValues($silence_start, $silence_end, $silence_duration): array
    {
        $combined_values = array();
        $num_items = count($silence_start);
        for ($i = 0; $i < $num_items; ++$i) {
            $combined_values[] = [
                'start' => $this->normalizeValues($silence_start[$i] ?? null),
                'end' => $this->normalizeValues($silence_end[$i] ?? null),
                'duration' => $this->normalizeValues($silence_duration[$i] ?? null)
            ];
        }
        return $combined_values;
    }


    protected function getlongestMonolouge($input)
    {
        return max(array_map(function ($v) {
            if (!is_null($v[1]) && !is_null($v[0])) {
                return ($v[1] - $v[0]);
            }
        }, $input));
    }

    /**
     * @param $combined_values_user
     * @param $combined_values_customer
     * @return float
     */
    protected function getCallDuration($combined_values_user, $combined_values_customer): float
    {
        // Aim to determine entire call duration <
        $i_user = count($combined_values_user);
        $i_customer = count($combined_values_customer);

        // Get the biggest value if elements exist else get the available value <
        $call_duration = ($combined_values_user[$i_user - 1]['end'] ?? 0) > ($combined_values_customer[$i_customer]['end'] ?? 0) ?
            $combined_values_user[$i_user - 1]['end'] : $combined_values_customer[$i_customer - 1]['end'];

        return $call_duration;

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
        preg_match_all('/start:\d{1,}\D{0,1}\d{1,}/', $silence_text_user, $user_silence_start);
        $user_silence_start = array_pop($user_silence_start);

        preg_match_all('/start:\d{1,}\D{0,1}\d{1,}/', $silence_text_customer, $customer_silence_start);
        $customer_silence_start = array_pop($customer_silence_start);

        // Get end values <
        preg_match_all('/end:\d{1,}\D{0,1}\d{1,}/', $silence_text_user, $user_silence_end);
        $user_silence_end = array_pop($user_silence_end);

        preg_match_all('/end:\d{1,}\D{0,1}\d{1,}/', $silence_text_customer, $customer_silence_end);
        $customer_silence_end = array_pop($customer_silence_end);

        // Get duration values <
        preg_match_all('/duration:\d{1,}\D{0,1}\d{1,}/', $silence_text_user, $user_silence_duration);
        $user_silence_duration = array_pop($user_silence_duration);

        preg_match_all('/duration:\d{1,}\D{0,1}\d{1,}/', $silence_text_customer, $customer_silence_duration);
        $customer_silence_duration = array_pop($customer_silence_duration);


        // Combined array vlues <
        $combined_values_user = $this->combineDataValues($user_silence_start, $user_silence_end, $user_silence_duration);
        $combined_values_customer = $this->combineDataValues($customer_silence_start, $customer_silence_end, $customer_silence_duration);

        // Get active speech arrays <
        $user_active['user'] = $this->invertToActiveSpeech($combined_values_user);
        $customer_active['customer'] = $this->invertToActiveSpeech($combined_values_customer);


        // Get Longest Monologues <
        $user_active['longest_monologue'] = $this->getlongestMonolouge($user_active['user']);
        $customer_active['longest_monologue'] = $this->getlongestMonolouge($customer_active['customer']);

        // Get User Talk Percentage
        $user_talk_percentage = round(($user_active['longest_monologue'] / $this->getCallDuration($combined_values_user, $combined_values_customer) * 100), 2);

    }
}

<?php declare(strict_types=1);

class AnalizeText
{
    /**
     * @param $input
     * @return float
     */
    private function normalizeValues($input): float
    {
        $output = preg_replace('/\w{1,}:/', '', $input);
        return (float) $output;
    }

    /**
     * @param array $input
     * @return array
     */
    private function invertToActiveSpeech(array $input): array
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
     * @param array $silence_start
     * @param array $silence_end
     * @param array $silence_duration
     * @return array
     */
    protected function combineDataValues(array $silence_start, array $silence_end, array $silence_duration): array
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

    /**
     * @param array $input
     * @return float
     */
    private function getLongestMonologue(array $input): float
    {
        return max(array_map(function ($v) {
            if (!is_null($v[1]) && !is_null($v[0])) {
                return round($v[1] - $v[0], 2);
            }
        }, $input));
    }

    /**
     * @param array $combined_values_user
     * @param array $combined_values_customer
     * @return float
     */
    protected function getCallDuration(array $combined_values_user, array $combined_values_customer): float
    {
        // Aim to determine entire call duration <
        $i_user = count($combined_values_user);
        $i_customer = count($combined_values_customer);

        // Get the biggest value if elements exist else get the available value <
        $call_duration = ($combined_values_user[$i_user - 1]['end'] ?? 0) > ($combined_values_customer[$i_customer]['end'] ?? 0) ?
            $combined_values_user[$i_user - 1]['end'] : $combined_values_customer[$i_customer - 1]['end'];

        return $call_duration;
    }

    /**
     * @return array
     * @throws Exception
     */
    public function getFfmpegFilteredData(): array
    {

        $user_string = file_get_contents("https://raw.githubusercontent.com/jiminny/join-the-team/master/assets/user-channel.txt");
        $customer_string = file_get_contents("https://raw.githubusercontent.com/jiminny/join-the-team/master/assets/customer-channel.txt");

        if ($user_string === false) {
            throw new Exception("Could not read the user-channel.txt");
        }
        if ($customer_string === false) {
            throw new Exception("Could not read the customer-channel.txt");
        }

        $ffmpeg_data = array(
            'user_channel' => $user_string,
            'customer_channel' => $customer_string
        );

        return $ffmpeg_data;
    }

    /**
     * @param $req
     * @return array
     * @throws Exception
     */
    function detectInput($req): array
    {
        if (!isset($req['user_channel'])) {
            throw new Exception('Document user-channel is not set!');
        }
        if (!isset($req['customer_channel'])) {
            throw new Exception('Document customer_channel is not set!');
        }

        // remove whitespace form text <
        $silence_text_user = preg_replace('/\s/', '', $req['user_channel']);
        $silence_text_customer = preg_replace('/\s/', '', $req['customer_channel']);

        // Get silence values <
        $needle_start = '/start:\d{1,}\D{0,1}\d{1,}/';
        $needle_end = '/end:\d{1,}\D{0,1}\d{1,}/';
        $needle_duration = '/duration:\d{1,}\D{0,1}\d{1,}/';

        // Get start values
        preg_match_all($needle_start, $silence_text_user, $user_silence_start);
        $user_silence_start = array_pop($user_silence_start);
        preg_match_all($needle_start, $silence_text_customer, $customer_silence_start);
        $customer_silence_start = array_pop($customer_silence_start);

        // Get end values <
        preg_match_all($needle_end, $silence_text_user, $user_silence_end);
        $user_silence_end = array_pop($user_silence_end);
        preg_match_all($needle_end, $silence_text_customer, $customer_silence_end);
        $customer_silence_end = array_pop($customer_silence_end);

        // Get duration values <
        preg_match_all($needle_duration, $silence_text_user, $user_silence_duration);
        $user_silence_duration = array_pop($user_silence_duration);
        preg_match_all($needle_duration, $silence_text_customer, $customer_silence_duration);
        $customer_silence_duration = array_pop($customer_silence_duration);

        // Combined array vlues <
        $combined_values_user = $this->combineDataValues($user_silence_start, $user_silence_end, $user_silence_duration);
        $combined_values_customer = $this->combineDataValues($customer_silence_start, $customer_silence_end, $customer_silence_duration);

        // Get active speech arrays <
        $user_active['active_speech'] = $this->invertToActiveSpeech($combined_values_user);
        $customer_active['active_speech'] = $this->invertToActiveSpeech($combined_values_customer);

        // Get Longest Monologues <
        $user_active['longest_monologue'] = $this->getLongestMonologue($user_active['active_speech']);
        $customer_active['longest_monologue'] = $this->getLongestMonologue($customer_active['active_speech']);

        // Get User Talk Percentage <
        $user_talk_percentage = round(($user_active['longest_monologue'] / $this->getCallDuration($combined_values_user, $combined_values_customer) * 100), 2);

        $res = [
            "longest_user_monologue" => $user_active['longest_monologue'],
            "longest_customer_monologue" => $customer_active['longest_monologue'],
            "user_talk_percentage" => $user_talk_percentage,
            "user" => $user_active['active_speech'],
            "customer" => $customer_active['active_speech']

        ];
        return $res;
    }
}

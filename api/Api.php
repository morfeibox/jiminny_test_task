<?php
include './includes/AnalizeText.php';

class API
{

    function select()
    {
        $analyzer = new AnalizeText();
        $ffmpeg_data = $analyzer->getFfmpegFilteredData();
        $res = $analyzer->detectInput($ffmpeg_data);

        if (is_array($res)) {
            return json_encode($res);
        } else {
            $e = ["error" => "There is no extracted data!"];
            return json_encode($e);
        }

    }

}

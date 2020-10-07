<?php declare(strict_types=1);
include './includes/AnalizeText.php';

class API
{

    function select()
    {
        $analyzer = new AnalizeText();

        try {
            $ffmpeg_data = $analyzer->getFfmpegFilteredData();
        } catch (Exception $e) {
            echo 'Caught exception: ',  $e->getMessage(), "\n";
        }

        try {
            $res = $analyzer->detectInput($ffmpeg_data);
        } catch (Exception $e) {
            echo 'Caught exception: ',  $e->getMessage(), "\n";
        }

        if (is_array($res)) {
            return json_encode($res);
        } else {
            $e = ["error" => "There is no extracted data!"];
            return json_encode($e);
        }

    }

}

<?php declare(strict_types=1);

require './includes/AnalizeText.php';

use PHPUnit\Framework\TestCase;


class AnalizeTextTest extends TestCase

{
    // Test if correctly invert the values from silence to active speech <
    public function testInvertToActiveSpeechReturnCorrectArray(): void
    {
        $analyzer = new AnalizeText();

        $input = [
            ['start' => 3.504, "end" => 6.656, "duration" => 3.152],
            ['start' => 14, "end" => 19.712, "duration" => 5.712],
            ['start' => 20.144, "end" => 27.264, "duration" => 7.12],
            ['start' => 36.528, "end" => 41.728, "duration" => 5.2],

        ];
        $output = [[0, 3.504], [6.656, 14], [19.712, 20.144], [27.264, 36.528], [41.728, null]];

        $this->assertEquals($output, $analyzer->invertToActiveSpeech($input));

    }

    // Test if the longest monologue is extracted correctly <
    public function testGetLongestMonologueReturnCorrectInteger(): void
    {
        $analyzer = new AnalizeText();

        $input = [[3, 56.34], [19, 23.22], [35, 12], [33, 100]];

        $output = 67;

        $this->assertEquals($output, $analyzer->getlongestMonologue($input));
    }

    public function testNormalizeValuesReturnFloat(){

        $analyzer = new AnalizeText();

        $input = 'end:3.456';
        $output = 3.456;

        $this->assertEquals($output, $analyzer->normalizeValues($input));

    }

}

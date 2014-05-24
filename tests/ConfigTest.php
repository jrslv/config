<?php

require_once '../src/Config.php';

class ConfigTest extends \PHPUnit_Framework_TestCase {

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Cannot find path
     */
    public function testEmptyPath() {
        $config = new Config('');
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Cannot find path
     */
    public function testIncorrectPath() {
        $config = new Config('foo.json');
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Unsupported format: TXT
     */
    public function testIncorrectFormat() {
        $config = new Config('data/config.txt');
    }

    public function testCorrectJson() {
        $config = new Config('data/config.json');

        $this->assertEquals('MIT', $config->get('license'));
        $this->assertEquals('MIT', $config['license']);
        $this->assertTrue(isset($config['license']));

        $this->assertEquals(['PHP' => true, "JavaScript" => false], $config->get('languages'));
        $this->assertTrue($config->get('languages.PHP'));

        $this->assertEquals(["Jupiter","Saturn","Neptun"], $config->get('planets'));
        $this->assertEquals("Jupiter", $config->get('planets.0'));
        $this->assertEquals("Saturn", $config->get('planets.1'));
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage JSON parse error
     */
    public function testIncorrectJson() {
        $config = new Config('data/config.bad.json');
        $this->assertEquals('MIT', $config->get('license'));
    }

    public function testCorrectIni() {
        $config = new Config('data/config.ini');

        $this->assertEquals('MIT', $config['license']);
        $this->assertTrue(isset($config['license']));

        $this->assertEquals(['PHP' => '1', "JavaScript" => '0'], $config->get('languages'));
        $this->assertEquals('1', $config->get('languages.PHP'));

        $this->assertEquals(["Jupiter","Saturn","Neptun"], $config->get('planets'));
        $this->assertEquals("Jupiter", $config->get('planets.0'));
        $this->assertEquals("Saturn", $config->get('planets.1'));
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage INI parse error
     */
    public function testIncorrectIni() {
        $config = new Config('data/config.bad.ini');
    }

    /**
     * @dataProvider correctDelimitersProvider
     */
    public function testChangeCorrectDelimiter($delimiter) {
        $config = new Config('data/config.json');
        $config->setDelimiter($delimiter);
        $this->assertEquals(['PHP' => true, "JavaScript" => false], $config->get('languages'));
    }

    /**
     * @dataProvider incorrectDelimitersProvider
     * @expectedException \Exception
     * @expectedExceptionMesage Delimiter must be a non-empty string
     */
    public function testChangeIncorrectDelimiter($delimiter) {
        $config = new Config('data/config.json');
        $config->setDelimiter($delimiter);
    }

    public function incorrectDelimitersProvider() {
        return [
            [''],
            [array()],
            ['--'],
            ['..'],
            [0],
            [1]
        ];
    }

    public function correctDelimitersProvider() {
        return [
            ['.'],
            ['-'],
            ['_'],
            ['/'],
            ['\\']
        ];
    }

}

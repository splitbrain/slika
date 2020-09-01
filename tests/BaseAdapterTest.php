<?php
/** @noinspection PhpUnhandledExceptionInspection */

namespace splitbrain\slika\tests;

use splitbrain\slika\Adapter;
use splitbrain\slika\Slika;

abstract class BaseAdapterTest extends TestCase
{

    /**
     * @param string $file
     * @return Adapter
     */
    abstract protected function getAdapter($file);

    public function testResize()
    {
        $orig = __DIR__ . '/landscape.png';
        $dest = $this->artefact('png');

        $this->assertSize($orig, 1000, 500);

        // half width bounding box
        ($this->getAdapter($orig))
            ->resize(500, 500)
            ->save($dest);

        $this->assertSize($dest, 500, 250);
        $this->assertColor($dest, 'top', 'blue');
        $this->assertColor($dest, 'right', 'red');
        $this->assertColor($dest, 'bottom', 'green');
        $this->assertColor($dest, 'left', 'yellow');
        $this->assertAlpha($dest, 'center', 100);
    }

    public function testCrop()
    {
        $orig = __DIR__ . '/landscape.png';
        $dest = $this->artefact('png');

        $this->assertSize($orig, 1000, 500);

        ($this->getAdapter($orig))
            ->crop(500, 500)
            ->save($dest);

        $this->assertSize($dest, 500, 500);
        $this->assertColor($dest, 'top', 'blue');
        $this->assertColor($dest, 'bottom', 'green');
        $this->assertAlpha($dest, 'center', 100);
    }

    public function testRotate()
    {
        $orig = __DIR__ . '/landscape.png';
        $dest = $this->artefact('png');

        $this->assertSize($orig, 1000, 500);

        ($this->getAdapter($orig))
            ->rotate(Slika::ROTATE_CW)
            ->save($dest);

        $this->assertSize($dest, 500, 1000);
        $this->assertColor($dest, 'top', 'yellow');
        $this->assertColor($dest, 'right', 'blue');
        $this->assertColor($dest, 'bottom', 'red');
        $this->assertColor($dest, 'left', 'green');
        $this->assertAlpha($dest, 'center', 100);
    }

    public function testCombined()
    {
        $orig = __DIR__ . '/landscape.png';
        $dest = $this->artefact('png');

        $this->assertSize($orig, 1000, 500);

        ($this->getAdapter($orig))
            ->crop(500, 500)
            ->rotate(Slika::ROTATE_CW)
            ->save($dest);

        $this->assertSize($dest, 500, 500);
        $this->assertColor($dest, 'right', 'blue');
        $this->assertColor($dest, 'left', 'green');
        $this->assertAlpha($dest, 'center', 100);
    }

    public function provideConversion()
    {
        return [
            ['gif'], ['png'], ['jpeg'], ['webp']
        ];
    }

    /**
     * @param string $out output format
     * @dataProvider provideConversion
     * @throws \Exception
     */
    public function testConversion($out)
    {
        $orig = __DIR__ . '/landscape.png';
        $dest = $this->artefact($out);

        ($this->getAdapter($orig))->save($dest, $out);
        $this->assertColor($dest, 'top', 'blue');
        $this->assertColor($dest, 'right', 'red');
        $this->assertColor($dest, 'bottom', 'green');
        $this->assertColor($dest, 'left', 'yellow');
    }

    public function provideAutoRotation()
    {
        $range = range(0, 8, 1);
        yield [array_shift($range)];
    }

    /**
     * @param int $rotation The rotation to test
     * @dataProvider provideAutoRotation
     * @throws \Exception
     */
    public function testAutoRotation($rotation)
    {
        $orig = __DIR__ . "/landscape_$rotation.jpg";
        $dest = $this->artefact('jpg');

        ($this->getAdapter($orig))->autorotate()->save($dest, 'jpeg');
        $this->assertColor($dest, 'top', 'blue');
        $this->assertColor($dest, 'right', 'red');
        $this->assertColor($dest, 'bottom', 'green');
        $this->assertColor($dest, 'left', 'yellow');
    }
}

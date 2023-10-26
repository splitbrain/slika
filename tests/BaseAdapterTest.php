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

    public function testResizePercent()
    {
        $orig = __DIR__ . '/landscape.png';
        $dest = $this->artefact('png');

        $this->assertSize($orig, 1000, 500);

        // half width bounding box
        ($this->getAdapter($orig))
            ->resize('50%', '50%')
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
            ->crop(250, 250)
            ->save($dest);

        $this->assertSize($dest, 250, 250);
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

    /**
     * @see https://github.com/splitbrain/slika/issues/5
     * @return void
     */
    public function testIssue5()
    {
        $orig = __DIR__ . '/issue5.gif';
        $dest = $this->artefact('gif');

        $this->assertSize($orig, 320, 240);
        ($this->getAdapter($orig))
            ->resize(160, 120)
            ->save($dest);
        $this->assertSize($dest, 160, 120);
    }

    public function testGifTransparency()
    {
        $orig = __DIR__ . '/transparency.gif';
        $dest = $this->artefact('gif');

        $this->assertSize($orig, 150, 150);
        ($this->getAdapter($orig))
            ->resize(100, 100)
            ->save($dest);

        $this->assertSize($dest, 100, 100);
        $this->assertColor($dest, 'center', 'red');
        $this->assertAlpha($dest, 'center', 0);
        $this->assertAlpha($dest, 'top', 127);
    }
}

<?php
/** @noinspection PhpUnhandledExceptionInspection */

namespace splitbrain\slika\tests;

use splitbrain\slika\GdAdapter;
use splitbrain\slika\Slika;

class GdAdapterTest extends TestCase
{


    public function testResize()
    {
        $orig = __DIR__ . '/landscape.png';
        $dest = $this->tempdir . '/test.png';

        $this->assertSize($orig, 1000, 500);

        // half width bounding box
        (new GdAdapter($orig))
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
        $dest = $this->tempdir . '/test.png';

        $this->assertSize($orig, 1000, 500);

        (new GdAdapter($orig))
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
        $dest = $this->tempdir . '/test.png';

        $this->assertSize($orig, 1000, 500);

        (new GdAdapter($orig))
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
        $dest = $this->tempdir . '/test.png';

        $this->assertSize($orig, 1000, 500);

        (new GdAdapter($orig))
            ->crop(500, 500)
            ->rotate(Slika::ROTATE_CW)
            ->save($dest);

        $this->assertSize($dest, 500, 500);
        $this->assertColor($dest, 'right', 'blue');
        $this->assertColor($dest, 'left', 'green');
        $this->assertAlpha($dest, 'center', 100);
    }
}

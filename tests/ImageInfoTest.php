<?php
/** @noinspection PhpUnhandledExceptionInspection */

namespace splitbrain\slika\tests;

use splitbrain\slika\Exception;
use splitbrain\slika\GdAdapter;
use splitbrain\slika\ImageInfo;

class ImageInfoTest extends TestCase
{
    public function testMissingFile()
    {
        $this->expectException(Exception::class);
        new ImageInfo(__DIR__ . '/does-not-exist.jpg');
    }

    public function testNonImageFile()
    {
        $this->expectException(Exception::class);
        // a PHP source file is readable but not an image
        new ImageInfo(__FILE__);
    }

    public function testRawDimensionsPng()
    {
        $info = new ImageInfo(__DIR__ . '/landscape.png');
        $this->assertSame(1000, $info->getRawWidth());
        $this->assertSame(500, $info->getRawHeight());
        $this->assertSame('png', $info->getExtension());
        $this->assertSame(1, $info->getOrientation());
        // current tracked dims equal raw dims until a chain method is called
        $this->assertSame([1000, 500], $info->getDimensions());
    }

    public function testRawDimensionsJpegPortraitOnDisk()
    {
        // landscape_6.jpg is stored as 500x1000 on disk (portrait bytes)
        // with EXIF orientation 6 telling viewers to rotate CW -> landscape
        $info = new ImageInfo(__DIR__ . '/landscape_6.jpg');
        $this->assertSame(500, $info->getRawWidth());
        $this->assertSame(1000, $info->getRawHeight());
        $this->assertSame('jpeg', $info->getExtension());
        $this->assertSame(6, $info->getOrientation());
    }

    public function provideOrientations()
    {
        // (fixture suffix, expected orientation, disk w, disk h)
        return [
            [1, 1, 1000, 500],
            [2, 2, 1000, 500],
            [3, 3, 1000, 500],
            [4, 4, 1000, 500],
            [5, 5, 500, 1000],
            [6, 6, 500, 1000],
            [7, 7, 500, 1000],
            [8, 8, 500, 1000],
        ];
    }

    /**
     * @dataProvider provideOrientations
     */
    public function testOrientationExifPath($suffix, $expected)
    {
        $info = new ImageInfo(__DIR__ . "/landscape_$suffix.jpg");
        $this->assertSame($expected, $info->getOrientation());
    }

    /**
     * @dataProvider provideOrientations
     */
    public function testOrientationRawBytesPath($suffix, $expected)
    {
        // exercise the fallback directly so it's covered even on systems
        // with the exif extension present
        $got = ImageInfo::readExifOrientationFromBytes(__DIR__ . "/landscape_$suffix.jpg");
        $this->assertSame($expected, $got);
    }

    public function testOrientationNonJpegIsOne()
    {
        $info = new ImageInfo(__DIR__ . '/landscape.png');
        $this->assertSame(1, $info->getOrientation());
    }

    /**
     * @dataProvider provideOrientations
     */
    public function testAutorotateDimensions($suffix, $orientation, $diskW, $diskH)
    {
        $info = new ImageInfo(__DIR__ . "/landscape_$suffix.jpg");
        $info->autorotate();

        if (in_array($orientation, [5, 6, 7, 8])) {
            $this->assertSame([$diskH, $diskW], $info->getDimensions(), "orientation $orientation should swap");
        } else {
            $this->assertSame([$diskW, $diskH], $info->getDimensions(), "orientation $orientation should not swap");
        }

        // raw getters are unaffected by chain ops
        $this->assertSame($diskW, $info->getRawWidth());
        $this->assertSame($diskH, $info->getRawHeight());
    }

    public function testAutorotateNoopOnNonJpeg()
    {
        $info = new ImageInfo(__DIR__ . '/landscape.png');
        $info->autorotate();
        $this->assertSame([1000, 500], $info->getDimensions());
    }

    public function testResize()
    {
        $info = (new ImageInfo(__DIR__ . '/landscape.png'))->resize(500, 500);
        $this->assertSame([500, 250], $info->getDimensions());
    }

    public function testResizePercent()
    {
        $info = (new ImageInfo(__DIR__ . '/landscape.png'))->resize('50%', '50%');
        $this->assertSame([500, 250], $info->getDimensions());
    }

    public function testResizeOneDimWidth()
    {
        $info = (new ImageInfo(__DIR__ . '/landscape.png'))->resize(400, 0);
        $this->assertSame([400, 200], $info->getDimensions());
    }

    public function testResizeOneDimHeight()
    {
        $info = (new ImageInfo(__DIR__ . '/landscape.png'))->resize(0, 200);
        $this->assertSame([400, 200], $info->getDimensions());
    }

    public function testResizeZero()
    {
        $this->expectException(Exception::class);
        (new ImageInfo(__DIR__ . '/landscape.png'))->resize(0, 0);
    }

    public function testResizeNoUpscale()
    {
        $info = (new ImageInfo(__DIR__ . '/landscape.png'))->resize(2000, 2000, false);
        $this->assertSame([1000, 500], $info->getDimensions());
    }

    public function testResizeUpscaleAllowed()
    {
        $info = (new ImageInfo(__DIR__ . '/landscape.png'))->resize(2000, 2000);
        $this->assertSame([2000, 1000], $info->getDimensions());
    }

    public function testCrop()
    {
        $info = (new ImageInfo(__DIR__ . '/landscape.png'))->crop(250, 250);
        $this->assertSame([250, 250], $info->getDimensions());
    }

    public function testCropNoUpscaleFits()
    {
        $info = (new ImageInfo(__DIR__ . '/landscape.png'))->crop(2000, 2000, false);
        $this->assertSame([1000, 500], $info->getDimensions());
    }

    public function testCropNoUpscalePartial()
    {
        $info = (new ImageInfo(__DIR__ . '/landscape.png'))->crop(2000, 400, false);
        $this->assertSame([1000, 400], $info->getDimensions());
    }

    public function testCropUpscaleAllowed()
    {
        $info = (new ImageInfo(__DIR__ . '/landscape.png'))->crop(2000, 2000);
        $this->assertSame([2000, 2000], $info->getDimensions());
    }

    public function testCropOneDimWidth()
    {
        $info = (new ImageInfo(__DIR__ . '/landscape.png'))->crop(250, 0);
        $this->assertSame([250, 250], $info->getDimensions());
    }

    public function testCropOneDimHeight()
    {
        $info = (new ImageInfo(__DIR__ . '/landscape.png'))->crop(0, 250);
        $this->assertSame([250, 250], $info->getDimensions());
    }

    public function testCropZero()
    {
        $this->expectException(Exception::class);
        (new ImageInfo(__DIR__ . '/landscape.png'))->crop(0, 0);
    }

    public function testRotateInvalid()
    {
        $this->expectException(Exception::class);
        (new ImageInfo(__DIR__ . '/landscape.png'))->rotate(42);
    }

    public function testRotateSwap()
    {
        $info = (new ImageInfo(__DIR__ . '/landscape.png'))->rotate(6);
        $this->assertSame([500, 1000], $info->getDimensions());
    }

    public function testRotateNoop()
    {
        $info = (new ImageInfo(__DIR__ . '/landscape.png'))->rotate(3);
        // orientation 3 is a 180° rotation — no dimension swap
        $this->assertSame([1000, 500], $info->getDimensions());
    }

    /**
     * Round-trip: ImageInfo's dimension prediction must match the actual
     * dimensions of the file produced by running the same chain on a real
     * GdAdapter. This is the core integrity check for callers that use
     * the predicted dims in contexts where they must equal reality (e.g.
     * <img width=... height=...> attributes).
     *
     * @dataProvider provideOrientations
     */
    public function testRoundTripAutorotateResize($suffix)
    {
        $orig = __DIR__ . "/landscape_$suffix.jpg";
        $dest = $this->artefact("orientation_$suffix.jpg");

        $predicted = (new ImageInfo($orig))
            ->autorotate()
            ->resize(500, 500)
            ->getDimensions();

        (new GdAdapter($orig))
            ->autorotate()
            ->resize(500, 500)
            ->save($dest, 'jpeg');

        $actual = getimagesize($dest);
        $this->assertSame([$actual[0], $actual[1]], $predicted, "round-trip mismatch for orientation $suffix");
    }

    /**
     * @dataProvider provideOrientations
     */
    public function testRoundTripAutorotateCrop($suffix)
    {
        $orig = __DIR__ . "/landscape_$suffix.jpg";
        $dest = $this->artefact("crop_$suffix.jpg");

        $predicted = (new ImageInfo($orig))
            ->autorotate()
            ->crop(250, 250)
            ->getDimensions();

        (new GdAdapter($orig))
            ->autorotate()
            ->crop(250, 250)
            ->save($dest, 'jpeg');

        $actual = getimagesize($dest);
        $this->assertSame([$actual[0], $actual[1]], $predicted, "crop round-trip mismatch for orientation $suffix");
    }

    public function testBoundingBoxStaticBothGiven()
    {
        list($w, $h) = ImageInfo::boundingBox(1000, 500, 500, 500);
        $this->assertSame([500, 250], [(int)$w, (int)$h]);
    }

    public function testBoundingBoxStaticPortrait()
    {
        list($w, $h) = ImageInfo::boundingBox(500, 1000, 500, 500);
        $this->assertSame([250, 500], [(int)$w, (int)$h]);
    }

    public function testBoundingBoxStaticOneDim()
    {
        list($w, $h) = ImageInfo::boundingBox(1000, 500, 0, 100);
        $this->assertSame([200, 100], [(int)$w, (int)$h]);

        list($w, $h) = ImageInfo::boundingBox(1000, 500, 400, 0);
        $this->assertSame([400, 200], [(int)$w, (int)$h]);
    }

    public function testCleanDimensionStatic()
    {
        $this->assertSame(100, ImageInfo::cleanDimension(100, 1000));
        $this->assertSame(100, ImageInfo::cleanDimension('100', 1000));
        $this->assertEquals(500, ImageInfo::cleanDimension('50%', 1000));
        $this->assertSame(0, ImageInfo::cleanDimension(0, 1000));
    }
}

<?php
/** @noinspection PhpUnhandledExceptionInspection */

namespace splitbrain\slika\tests;

use splitbrain\slika\Exception;
use splitbrain\slika\ImageMagickAdapter;

class ImageMagickAdapterTest extends BaseAdapterTest
{
    /** @inheritDoc */
    protected function getAdapter($file)
    {
        return new ImageMagickAdapter($file);
    }

    /**
     * Read the command line arguments the given adapter assembled
     *
     * @param ImageMagickAdapter $adapter
     * @return array
     * @throws \ReflectionException
     */
    protected function getArgs($adapter)
    {
        $property = new \ReflectionProperty(ImageMagickAdapter::class, 'args');
        $property->setAccessible(true);
        return $property->getValue($adapter);
    }

    public function testDefaultLimits()
    {
        $orig = __DIR__ . '/landscape.png';
        $args = $this->getArgs(new ImageMagickAdapter($orig));

        $this->assertSame(
            ['-limit', 'memory', '256MiB', '-limit', 'map', '512MiB', '-limit', 'disk', '1GiB'],
            array_slice($args, 1, 9)
        );

        // limits are ignored by ImageMagick unless they precede the input file
        $this->assertLessThan(array_search($orig, $args), max(array_keys($args, '-limit')));
    }

    public function testOverriddenLimits()
    {
        $orig = __DIR__ . '/landscape.png';
        $options = ['imlimits' => ['memory' => '1GiB', 'time' => 30]];
        $args = $this->getArgs(new ImageMagickAdapter($orig, $options));

        // the overridden limit is applied, unmentioned defaults are kept, new limits are added
        $this->assertSame(
            ['-limit', 'memory', '1GiB', '-limit', 'map', '512MiB', '-limit', 'disk', '1GiB', '-limit', 'time', 30],
            array_slice($args, 1, 12)
        );
    }

    public function testDisabledLimits()
    {
        $orig = __DIR__ . '/landscape.png';
        $options = ['imlimits' => ['memory' => null, 'map' => null, 'disk' => null]];
        $args = $this->getArgs(new ImageMagickAdapter($orig, $options));

        $this->assertNotContains('-limit', $args);
    }

    public function testExceededLimitThrows()
    {
        $orig = __DIR__ . '/landscape.png';
        $options = ['imlimits' => ['width' => 100, 'height' => 100]];

        $this->expectException(Exception::class);
        (new ImageMagickAdapter($orig, $options))->resize(50, 50)->save($this->artefact('png'));
    }
}

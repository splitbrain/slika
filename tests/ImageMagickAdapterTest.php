<?php
/** @noinspection PhpUnhandledExceptionInspection */

namespace splitbrain\slika\tests;

use splitbrain\slika\ImageMagickAdapter;

class ImageMagickAdapterTest extends BaseAdapterTest
{
    /** @inheritDoc */
    protected function getAdapter($file)
    {
        return new ImageMagickAdapter($file);
    }
}

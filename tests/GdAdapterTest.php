<?php
/** @noinspection PhpUnhandledExceptionInspection */

namespace splitbrain\slika\tests;

use splitbrain\slika\GdAdapter;

class GdAdapterTest extends BaseAdapterTest
{
    /** @inheritDoc */
    protected function getAdapter($file)
    {
        return new GdAdapter($file);
    }
}

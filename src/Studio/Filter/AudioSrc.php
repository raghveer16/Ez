<?php

namespace EzAd\Studio\Filter;

class AudioSrc extends MovieSrc
{
    public function getName()
    {
        return 'amovie';
    }

    public function getStreams()
    {
        return $this->get('streams', 'da');
    }
}

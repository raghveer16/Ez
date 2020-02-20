<?php

namespace EzAd\Studio\Filter;

class MovieSrc extends AbstractFilter
{
    public static function create($filename, $in = '', $out = '')
    {
        $self = new static($in, $out);
        $self->setFilename($filename);
        return $self;
    }

    public function getName()
    {
        return 'movie';
    }

    public function setFilename($filename)
    {
        return $this->set('filename', $filename);
    }

    public function setFormatName($fmtName)
    {
        return $this->set('format_name', $fmtName);
    }

    public function setSeekPoint($seek)
    {
        return $this->set('seek_point', $seek);
    }

    public function setStreams($streams)
    {
        return $this->set('streams', $streams);
    }

    public function setLoop($loop)
    {
        return $this->set('loop', $loop);
    }

    public function getFilename()
    {
        return $this->get('filename');
    }

    public function getFormatName()
    {
        return $this->get('format_name');
    }

    public function getSeekPoint()
    {
        return $this->get('seek_point', 0);
    }

    public function getStreams()
    {
        return $this->get('streams', 'dv+da');
    }

    public function getLoop()
    {
        return $this->get('loop', 1);
    }
}

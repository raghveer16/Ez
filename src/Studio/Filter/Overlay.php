<?php

namespace EzAd\Studio\Filter;

class Overlay extends AbstractFilter
{
    const EOF_ACTION_REPEAT = 'repeat';
    const EOF_ACTION_ENDALL = 'endall';
    const EOF_ACTION_PASS   = 'pass';

    const EVAL_INIT  = 'init';
    const EVAL_FRAME = 'frame';

    const FORMAT_YUV420 = 'yuv420';
    const FORMAT_YUV422 = 'yuv422';
    const FORMAT_YUV444 = 'yuv444';
    const FORMAT_RGB    = 'rgb';

    public function getName()
    {
        return 'overlay';
    }

    public function setX($x)
    {
        return $this->set('x', $x);
    }

    public function setY($y)
    {
        return $this->set('y', $y);
    }

    public function setXY($x, $y)
    {
        return $this->setX($x)->setY($y);
    }

    public function setEofAction($action)
    {
        return $this->set('eof_action', $action);
    }

    public function setEval($eval)
    {
        return $this->set('eval', $eval);
    }

    public function setShortest($shortest)
    {
        return $this->set('shortest', $shortest ? 1 : 0);
    }

    public function setFormat($format)
    {
        return $this->set('format', $format);
    }

    public function setRepeatLast($last)
    {
        return $this->set('repeatlast', $last ? 1 : 0);
    }

    public function getX()
    {
        return $this->get('x', 0);
    }

    public function getY()
    {
        return $this->get('y', 0);
    }

    public function getEofAction()
    {
        return $this->get('eof_action', self::EOF_ACTION_REPEAT);
    }

    public function getEval()
    {
        return $this->get('eval', self::EVAL_FRAME);
    }

    public function getShortest()
    {
        return $this->get('shortest', 0);
    }

    public function getFormat()
    {
        return $this->get('format', self::FORMAT_YUV420);
    }

    public function getRepeatLast()
    {
        return $this->get('repeatlast', 1);
    }
}

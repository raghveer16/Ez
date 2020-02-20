<?php

namespace EzAd\Studio\Filter;

class Volume extends AbstractFilter
{
    const PREC_FIXED = 'fixed';
    const PREC_FLOAT = 'float';
    const PREC_DOUBLE = 'double';

    const RG_DROP = 'drop';
    const RG_IGNORE = 'ignore';
    const RG_TRACK = 'track';
    const RG_ALBUM = 'album';

    const EVAL_ONCE = 'once';
    const EVAL_FRAME = 'frame';

    public function getName()
    {
        return 'volume';
    }

    // volume, precision, replaygain, replaygain_preamp, eval
    // commands: volume, replaygain_noclip

    public function setVolume($volume)
    {
        return $this->set('volume', $volume);
    }

    public function setPrecision($precision)
    {
        return $this->set('precision', $precision);
    }

    public function setReplayGain($rg)
    {
        return $this->set('replaygain', $rg);
    }

    public function setReplayGainPreamp($preamp)
    {
        return $this->set('replaygain_preamp', $preamp);
    }

    public function setEval($eval)
    {
        return $this->set('eval', $eval);
    }

    public function setReplayGainNoclip($noclip)
    {
        return $this->set('replaygain_noclip', $noclip);
    }

    public function getVolume()
    {
        return $this->get('volume', 1.0);
    }

    public function getPrecision()
    {
        return $this->get('precision', self::PREC_FLOAT);
    }

    public function getReplayGain()
    {
        return $this->get('replaygain', self::RG_DROP);
    }

    public function getReplayGainPreamp()
    {
        return $this->get('replaygain_preamp', 0.0);
    }

    public function getEval()
    {
        return $this->get('eval', self::EVAL_ONCE);
    }

    public function getReplayGainNoclip()
    {
        return $this->get('replaygain_noclip', 1);
    }
}

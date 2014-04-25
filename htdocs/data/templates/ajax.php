<?if (!empty($d['callback']) && !is_null($d['data'])) {?>
<?=sprintf('%s(%s)', $d['callback'], json_encode($this->plugins['iconv']->iconv($d['data'])));?>
<?} else {?>
<?=json_encode($this->plugins['iconv']->iconv($d['data']));?>
<?}?>
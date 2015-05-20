<?php
/**
 * @author  oke.ugwu
 */

namespace CmeKernel\Enums;

use Garoevans\PhpEnum\Enum;

abstract class CmeEnum extends Enum
{
  public function getValue()
  {
    $cl     = $this->getConstList();
    $return = $this->getDefault();
    foreach($cl as $c => $v)
    {
      if((string)$this == $v)
      {
        $return = $v;
      }
    }
    return $return;
  }

  public static function getPossibleValues()
  {
    return array_values((new static)->getConstList());
  }
}

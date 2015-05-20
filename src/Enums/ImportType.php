<?php

/**
 * @author  oke.ugwu
 */
namespace CmeKernel\Enums;

/**
 * Class ImportType
 * @package CmeKernel\Enums
 *
 * @method static API
 * @method static CSV
 * @method static UNKNOWN
 */
class ImportType extends CmeEnum
{
  const __default = self::UNKNOWN;
  const API       = "api";
  const CSV       = "csv";
  const UNKNOWN   = "Unknown";
}

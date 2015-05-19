<?php
/**
 * @author  oke.ugwu
 */

namespace CmeKernel\Core;

use CmeKernel\Data\BrandData;
use CmeKernel\Data\CampaignData;
use CmeKernel\Data\TemplateData;

class CmeTemplate
{
  private $_tableName = "templates";

  /**
   * @param $id
   *
   * @return bool| BrandData
   */
  public function get($id)
  {
    $template = CmeDatabase::conn()
      ->table($this->_tableName)
      ->where(['id' => $id])
      ->get();

    $data = false;
    if($template)
    {
      $data = CmeDatabase::hydrate(new TemplateData(), head($template));
    }
    return $data;
  }

  /**
   * @param TemplateData $data
   *
   * @return bool|int $id
   */
  public function create(TemplateData $data)
  {
    $data->id      = null;
    $data->created = time();
    $id            = CmeDatabase::conn()
      ->table($this->_tableName)
      ->insertGetId(
        CmeDatabase::dataToArray($data)
      );

    return $id;
  }

  /**
   * @param TemplateData $data
   *
   * @return bool
   */
  public function update(TemplateData $data)
  {
    CmeDatabase::conn()->table($this->_tableName)
      ->where('id', '=', $data->id)
      ->update(CmeDatabase::dataToArray($data));

    return true;
  }

  /**
   * @param int $id
   *
   * @return bool
   */
  public function delete($id)
  {
    $data            = new TemplateData();
    $data->deletedAt = time();
    CmeDatabase::conn()->table($this->_tableName)
      ->where('id', '=', $id)
      ->update(CmeDatabase::dataToArray($data));

    return true;
  }
}

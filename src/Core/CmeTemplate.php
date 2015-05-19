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

  public function exists($id)
  {
    $result = CmeDatabase::conn()->select(
      "SELECT id FROM " . $this->_tableName . " WHERE id = " . $id
    );
    return ($result)? true : false;
  }

  /**
   * @param $id
   *
   * @return bool| TemplateData
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
   * @param bool $includeDeleted
   *
   * @return TemplateData[];
   */
  public function all($includeDeleted = false)
  {
    $return = [];
    if($includeDeleted)
    {
      $result = CmeDatabase::conn()->table($this->_tableName)->get();
    }
    else
    {
      $result = CmeDatabase::conn()->table($this->_tableName)->whereNull(
        'deleted_at'
      )->get();
    }

    foreach($result as $row)
    {
      $return[] = CmeDatabase::hydrate(new TemplateData(), $row);
    }

    return $return;
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

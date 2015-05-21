<?php
/**
 * @author  oke.ugwu
 */

namespace CmeKernel\Core;

use CmeData\BrandData;
use CmeData\CampaignData;

class CmeBrand
{
  private $_tableName = "brands";

  public function exists($id)
  {
    $result = CmeDatabase::conn()->select(
      "SELECT id FROM " . $this->_tableName . " WHERE id = " . $id
    );
    return ($result) ? true : false;
  }

  /**
   * @param $id
   *
   * @return bool| BrandData
   */
  public function get($id)
  {
    $brand = CmeDatabase::conn()
      ->table($this->_tableName)
      ->where(['id' => $id])
      ->get();

    $data = false;
    if($brand)
    {
      $data = BrandData::hydrate(head($brand));
    }
    return $data;
  }

  /**
   * @param bool $includeDeleted
   *
   * @return BrandData[];
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
        'brand_deleted_at'
      )->get();
    }

    foreach($result as $row)
    {
      $return[] = BrandData::hydrate($row);
    }

    return $return;
  }

  public function getColumns()
  {
    return CmeDatabase::schema()->getColumnListing(
      $this->_tableName
    );
  }

  /**
   * @param BrandData $data
   *
   * @return bool|int $id
   */
  public function create(BrandData $data)
  {
    $data->id           = null;
    $data->brandCreated = time();
    $id                 = CmeDatabase::conn()
      ->table($this->_tableName)
      ->insertGetId(
        $data->toArray()
      );

    return $id;
  }

  /**
   * @param BrandData $data
   *
   * @return bool
   */
  public function update(BrandData $data)
  {
    CmeDatabase::conn()->table($this->_tableName)
      ->where('id', '=', $data->id)
      ->update($data->toArray());

    return true;
  }

  /**
   * @param int $id
   *
   * @return bool
   */
  public function delete($id)
  {
    $data                 = new BrandData();
    $data->brandDeletedAt = time();
    CmeDatabase::conn()->table($this->_tableName)
      ->where('id', '=', $id)
      ->update($data->toArray());

    return true;
  }


  /**
   * @param int $id - Brand ID
   *
   * @return CampaignData[]
   */
  public function campaigns($id)
  {
    $campaigns = CmeDatabase::conn()->table('campaigns')
      ->where(['brand_id' => $id])->get();

    $return = [];
    foreach($campaigns as $campaign)
    {
      $return[] = CampaignData::hydrate($campaign);
    }

    return $return;
  }
}

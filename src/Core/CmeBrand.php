<?php
/**
 * @author  oke.ugwu
 */

namespace CmeKernel\Core;

use CmeKernel\Data\BrandData;
use CmeKernel\Data\CampaignData;

class CmeBrand
{
  private $_tableName = "brands";

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
      $data = CmeDatabase::hydrate(new BrandData(), head($brand));
    }
    return $data;
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
        CmeDatabase::dataToArray($data)
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
    $data                 = new BrandData();
    $data->brandDeletedAt = time();
    CmeDatabase::conn()->table($this->_tableName)
      ->where('id', '=', $id)
      ->update(CmeDatabase::dataToArray($data));

    return true;
  }


  /**
   * @param int $id - Brand ID
   *
   * @return CmeCampaign[]
   */
  public function campaigns($id)
  {
    $campaigns = CmeDatabase::conn()->table('campaigns')
      ->where(['brand_id' => $id])->get();

    $return = [];
    foreach($campaigns as $campaign)
    {
      $return[] = CmeDatabase::hydrate(new CampaignData(), $campaign);
    }

    return $return;
  }
}

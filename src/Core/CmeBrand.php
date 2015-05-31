<?php
/**
 * @author  oke.ugwu
 */

namespace CmeKernel\Core;

use CmeData\BrandData;
use CmeData\CampaignData;
use CmeKernel\Exceptions\InvalidDataException;

class CmeBrand
{
  private $_tableName = "brands";

  /**
   * @param $id
   *
   * @return bool
   * @throws \Exception
   */
  public function exists($id)
  {
    if((int)$id > 0)
    {
      $result = CmeDatabase::conn()->select(
        "SELECT id FROM " . $this->_tableName . " WHERE id = " . $id
      );
      return ($result) ? true : false;
    }
    else
    {
      throw new \Exception("Invalid Brand ID");
    }
  }

  /**
   * @param $id
   *
   * @return bool|BrandData
   * @throws \Exception
   */
  public function get($id)
  {
    if((int)$id > 0)
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
    else
    {
      throw new \Exception("Invalid Brand ID");
    }
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

  /**
   * @return array
   */
  public function getColumns()
  {
    return CmeDatabase::schema()->getColumnListing(
      $this->_tableName
    );
  }

  /**
   * @param BrandData $data
   *
   * @return int $brandId
   * @throws \Exception
   * @throws InvalidDataException
   */
  public function create(BrandData $data)
  {
    $data->id           = null;
    $data->brandCreated = time();
    if($data->validate())
    {
      $id = CmeDatabase::conn()
        ->table($this->_tableName)
        ->insertGetId(
          $data->toArray()
        );

      return $id;
    }
    else
    {
      throw new InvalidDataException();
    }
  }

  /**
   * @param BrandData $data
   *
   * @return bool
   * @throws \Exception
   * @throws InvalidDataException
   */
  public function update(BrandData $data)
  {
    if($data->validate())
    {
      CmeDatabase::conn()->table($this->_tableName)
        ->where('id', '=', $data->id)
        ->update($data->toArray());
      return true;
    }
    else
    {
      throw new InvalidDataException();
    }
  }

  /**
   * @param int $id
   *
   * @return bool
   * @throws \Exception
   */
  public function delete($id)
  {
    if((int)$id > 0)
    {
      $data                 = new BrandData();
      $data->brandDeletedAt = time();
      CmeDatabase::conn()->table($this->_tableName)
        ->where('id', '=', $id)
        ->update($data->toArray());

      return true;
    }
    else
    {
      throw new \Exception("Invalid Brand ID");
    }
  }


  /**
   * @param int $brandId
   *
   * @return CampaignData[]
   * @throws \Exception
   */
  public function campaigns($brandId)
  {
    if((int)$brandId > 0)
    {
      $campaigns = CmeDatabase::conn()->table('campaigns')
        ->where(['brand_id' => $brandId])->get();

      $return = [];
      foreach($campaigns as $campaign)
      {
        $campaign               = CampaignData::hydrate($campaign);
        $campaign->list         = CmeKernel::EmailList()->get(
          $campaign->listId
        );
        $campaign->brand        = CmeKernel::Brand()->get($campaign->brandId);
        $campaign->smtpProvider = CmeKernel::SmtpProvider()->get(
          $campaign->smtpProviderId
        );
        $return[]               = $campaign;
      }

      return $return;
    }
    else
    {
      throw new \Exception("Invalid Brand ID");
    }
  }
}

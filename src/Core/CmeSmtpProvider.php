<?php
/**
 * @author  oke.ugwu
 */

namespace CmeKernel\Core;

use CmeData\CampaignData;
use CmeData\SmtpProviderData;
use Illuminate\Encryption\Encrypter;

class CmeSmtpProvider
{
  private $_tableName = "smtp_providers";

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
   * @return bool| SmtpProviderData
   */
  public function get($id)
  {
    $provider = CmeDatabase::conn()
      ->table($this->_tableName)
      ->where(['id' => $id])
      ->get();

    $data = false;
    if($provider)
    {
      $data = SmtpProviderData::hydrate(head($provider));
    }
    return $data;
  }

  /**
   * @param bool $includeDeleted
   *
   * @return SmtpProviderData[];
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
      $return[] = SmtpProviderData::hydrate($row);
    }

    return $return;
  }

  /**
   * @param SmtpProviderData $data
   * @param string           $encryptionKey
   *
   * @return int
   * @throws \Exception
   */
  public function create(SmtpProviderData $data, $encryptionKey)
  {
    if($encryptionKey)
    {
      $encrypter      = new Encrypter($encryptionKey);
      $data->username = $encrypter->encrypt($data->username);
      $data->password = $encrypter->encrypt($data->password);
      $id             = CmeDatabase::conn()
        ->table($this->_tableName)
        ->insertGetId(
          $data->toArray()
        );

      return $id;
    }
    else
    {
      throw new \Exception(
        "Encryption key is required to store SMTP Provider information"
      );
    }
  }

  /**
   * @param SmtpProviderData $data
   * @param string           $encryptionKey
   *
   * @return bool
   * @throws \Exception
   */
  public function update(SmtpProviderData $data, $encryptionKey)
  {
    if($encryptionKey)
    {
      $encrypter      = new Encrypter($encryptionKey);
      $data->username = $encrypter->encrypt($data->username);
      if($data->password == "")
      {
        $data->password = null;
      }
      else
      {
        $data->password = $encrypter->encrypt($data->password);
      }

      CmeDatabase::conn()->table($this->_tableName)
        ->where('id', '=', $data->id)
        ->update($data->toArray());

      return true;
    }
    else
    {
      throw new \Exception(
        "Encryption key is required to store SMTP Provider information"
      );
    }
  }

  /**
   * @param int $id
   *
   * @return bool
   */
  public function delete($id)
  {
    $data            = new SmtpProviderData();
    $data->deletedAt = time();
    CmeDatabase::conn()->table($this->_tableName)
      ->where('id', '=', $id)
      ->update($data->toArray());

    return true;
  }

  public function setAsDefault($id)
  {
    //reset all
    CmeDatabase::conn()->table('smtp_providers')
      ->update(['default' => 0]);

    //set smtp provide with matching $id as default
    CmeDatabase::conn()->table('smtp_providers')
      ->where('id', '=', $id)
      ->update(['default' => 1]);

    return true;
  }


  /**
   * @param int $smtpProviderId - SMTP Provider ID
   *
   * @return CampaignData[]
   */
  public function campaigns($smtpProviderId)
  {
    $campaigns = CmeDatabase::conn()->table('campaigns')
      ->where(['smtp_provider_id' => $smtpProviderId])->get();

    $return = [];
    foreach($campaigns as $campaign)
    {
      $return[] = CampaignData::hydrate($campaign);
    }

    return $return;
  }
}

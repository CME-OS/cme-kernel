<?php
/**
 * @author  oke.ugwu
 */

namespace CmeKernel\Core;

use CmeKernel\Data\CampaignData;
use CmeKernel\Data\SmtpProviderData;
use Illuminate\Encryption\Encrypter;

class CmeSmtpProvider
{
  private $_tableName = "smtp_providers";

  /**
   * @param $id
   *
   * @return bool| BrandData
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
      $data = CmeDatabase::hydrate(new SmtpProviderData(), head($provider));
    }
    return $data;
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
          CmeDatabase::dataToArray($data)
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
        ->update(CmeDatabase::dataToArray($data));

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
      ->update(CmeDatabase::dataToArray($data));

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
   * @param int $id - SMTP Provider ID
   *
   * @return CmeCampaign[]
   */
  public function campaigns($id)
  {
    $campaigns = CmeDatabase::conn()->table('campaigns')
      ->where(['smtp_provider_id' => $id])->get();

    $return = [];
    foreach($campaigns as $campaign)
    {
      $return[] = CmeDatabase::hydrate(new CampaignData(), $campaign);
    }

    return $return;
  }
}

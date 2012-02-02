<?php

/**
 * This file is part of the OpenPNE package.
 * (c) OpenPNE Project (http://www.openpne.jp/)
 *
 * For the full copyright and license information, please view the LICENSE
 * file and the NOTICE file that were distributed with this source code.
 */

/**
 * opAuthAdapterLDAP will handle credential for LDAP DIT.
 *
 * @package    OpenPNE
 * @subpackage lib
 * @author     Shouta Kashiwagi <kashiwagi@tejimaya.com>
 */

class opAuthAdapterLDAP extends opAuthAdapter
{
  protected $authModuleName = 'opAuthLDAP';

  /**
   * Returns true if the current state is a beginning of register.
   *
   * @return bool returns true if the current state is a beginning of register, false otherwise
   */
  public function isRegisterBegin($member_id = null)
  {
    opActivateBehavior::disable();
    $member = Doctrine::getTable('Member')->find((int)$member_id);
    opActivateBehavior::enable();

    if (!$member)
    {
      return false;
    }


    if (!$member->getIsActive())
    {
      return true;
    }
    else
    {
      return false;
    }
  }

  /**
   * Returns true if the current state is a end of register.
   *
   * @return bool returns true if the current state is a end of register, false otherwise
   */
  public function isRegisterFinish($member_id = null)
  {
    opActivateBehavior::disable();
    $data = Doctrine::getTable('Member')->find((int)$member_id);
    opActivateBehavior::enable();

    if (!$data || !$data->getName() || !$data->getProfiles())
    {
      return false;
    }

    if ($data->getIsActive())
    {
      return false;
    }
    else
    {
      return true;
    }
  }

  public function authenticate()
  {
    $result = parent::authenticate();

    if ($this->getAuthForm()->isValid() && !$result)
    {
      $username = $this->getAuthForm()->getValue('id');
      $entry = $this->getAuthForm()->getValue('ldap_entry');

      $member = new Member();
      $member->is_active = 1;
      $member->name = $username;
      $member->save();
      $member->setConfig('ldap', $username);

      $emailAttr = opConfig::get('opauthldapplugin_ldap_mail', '');
      if ('' !== $emailAttr)
      {
        $email = $entry[$emailAttr][0];
        $member->setConfig('pc_address', $email);
      }

      $result = $member->getId();
    }

    return $result;
  }
}


<?php

/**
 * opAuthValidatorLDAP
 *
 * @package    OpenPNE
 * @subpackage validator
 * @author     Shouta Kashiwagi <kashiwagi@tejimaya.com>
 */

class opAuthValidatorLDAP extends sfValidatorSchema
{
  /**
   * Constructor.
   *
   * @param array  $options   An array of options
   * @param array  $messages  An array of error messages
   *
   * @see sfValidatorSchema
   */
  public function __construct($options = array(), $messages = array())
  {
    parent::__construct(null, $options, $messages);
  }

  /**
   * Configures this validator.
   *
   * Available options:
   *
   *  * config_name: The configuration name of MemberConfig
   *
   * @see sfValidatorBase
   */
  protected function configure($options = array(), $messages = array())
  {
    $this->setMessage('invalid', 'ID is not a valid.');
  }

  /**
   * @see sfValidatorBase
   */
  protected function doClean($values)
  {
    $username = $values['id'];
    $password = $values['password'];

    $options = array();
    $serverNum = sfConfig::get('app_auth_ldap_server_num', 3);
    for ($i = 1; $i <= $serverNum; ++$i)
    {
      $options[] = array(
        'host' => opConfig::get('opauthldapplugin_server'.$i.'_ldap_host', null),
        'port' => opConfig::get('opauthldapplugin_server'.$i.'_ldap_port', 0),
        'bindRequiresDn' => true,
        'baseDn' => opConfig::get('opauthldapplugin_server'.$i.'_ldap_basedn', null),
      );
    }
    $adapter = new Zend_Auth_Adapter_Ldap($options, $username, $password);
    $result = $adapter->authenticate();
    if (!$result->isValid())
    {
      throw new sfValidatorError($this, 'invalid');
    }

    $ldap = $adapter->getLdap();
    $entry = $ldap->getEntry($ldap->getBoundUser());

    // reject member
    $rejectAttrType = opConfig::get('opauthldapplugin_ldap_rejectattrtype', '');
    if ('' !== $rejectAttrType)
    {
      $attrs = $entry[$rejectAttrType];
      $rejectAttrs = preg_split('/,/', opConfig::get('opauthldapplugin_ldap_rejectattr', ''));
      if (count(array_intersect($attrs, $rejectAttrs)))
      {
        throw new sfValidatorError($this, 'invalid');
      }
    }

    $memberConfig = Doctrine::getTable('MemberConfig')->retrieveByNameAndValue('ldap', $username);
    if ($memberConfig)
    {
      $values['member'] = $memberConfig->getMember();
    }
    $values['ldap_entry'] = $entry;

    return $values;
  }
}

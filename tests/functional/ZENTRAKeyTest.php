<?php

/**
 * ---------------------------------------------------------------------
 *
 * ZENTRA - Gestionnaire Libre de Parc Informatique
 *
 * http://zentra-project.org
 *
 * @copyright 2015-2026 Teclib' and contributors.
 * @licence   https://www.gnu.org/licenses/gpl-3.0.html
 *
 * ---------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of ZENTRA.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * ---------------------------------------------------------------------
 */

namespace tests\units;

use Zentra\Plugin\Hooks;
use Zentra\Tests\DbTestCase;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Log\LogLevel;

class ZENTRAKeyTest extends DbTestCase
{
    public static function getExpectedKeyPathProvider()
    {
        return [
            ['0.90.5', null],
            ['9.3.5', null],
            ['9.4.0', null],
            ['9.4.5', null],
            ['9.4.6', ZENTRA_CONFIG_DIR . '/zentra.key'],
            ['9.4.9', ZENTRA_CONFIG_DIR . '/zentra.key'],
            ['9.5.0-dev', ZENTRA_CONFIG_DIR . '/zentracrypt.key'],
            ['9.5.0', ZENTRA_CONFIG_DIR . '/zentracrypt.key'],
            ['9.5.3', ZENTRA_CONFIG_DIR . '/zentracrypt.key'],
            ['9.6.1', ZENTRA_CONFIG_DIR . '/zentracrypt.key'],
            ['15.3.0', ZENTRA_CONFIG_DIR . '/zentracrypt.key'],
        ];
    }

    #[DataProvider('getExpectedKeyPathProvider')]
    public function testGetExpectedKeyPath($zentra_version, $expected_path)
    {
        $zentrakey = new \ZENTRAKey();
        $this->assertEquals($expected_path, $zentrakey->getExpectedKeyPath($zentra_version));
    }

    public function testKeyExists()
    {
        $structure = vfsStream::setup('zentra', null, ['config' => []]);
        $zentrakey = new \ZENTRAKey(vfsStream::url('zentra/config'));

        $this->assertFalse($zentrakey->keyExists());

        vfsStream::create(['zentracrypt.key' => 'keyfilecontents'], $structure->getChild('config'));
        $this->assertTrue($zentrakey->keyExists());
    }

    public static function legacyEncryptedProvider()
    {
        // basic string, default key
        yield [
            'encrypted' => 'G6y/xA==',
            'decrypted' => 'test',
            'key'       => null,
        ];

        // string with special chars, default key
        yield [
            'encrypted' => 'IYx+rrgV1IqUtqSD1repTQ==',
            'decrypted' => 'zE2^oS1!mC6"dD6&',
            'key'       => null,
        ];

        // basic string, simple custom key
        yield [
            'encrypted' => '7cjo5w==',
            'decrypted' => 'test',
            'key'       => 'custom_k3y',
        ];

        // string with special chars, complex custom  key
        yield [
            'encrypted' => 'n7iLkqvGhVeXsoFVwqWEVg==',
            'decrypted' => 'zE2^oS1!mC6"dD6&',
            'key'       => 'sY4<sT6*oK3^aN0%',
        ];
    }

    #[DataProvider('legacyEncryptedProvider')]
    public function testDecryptUsingLegacyKey(string $encrypted, string $decrypted, ?string $key)
    {
        $zentrakey = new \ZENTRAKey();
        $this->assertEquals($decrypted, $zentrakey->decryptUsingLegacyKey($encrypted, $key));
    }

    public function testGetWithoutKey()
    {
        vfsStream::setup('zentra', null, ['config' => []]);
        $zentrakey = new \ZENTRAKey(vfsStream::url('zentra/config'));

        $zentrakey->get();
        $this->hasPhpLogRecordThatContains(
            'You must create a security key, see security:change_key command.',
            LogLevel::WARNING
        );
    }

    public function testGetUnreadableKey()
    {
        $structure = vfsStream::setup('zentra', null, ['config' => ['zentracrypt.key' => 'unreadable file']]);
        $structure->getChild('config/zentracrypt.key')->chmod(0o222);

        $zentrakey = new \ZENTRAKey(vfsStream::url('zentra/config'));

        $zentrakey->get();
        $this->hasPhpLogRecordThatContains(
            'Unable to get security key file contents.',
            LogLevel::WARNING
        );
    }

    public function testGetInvalidKey()
    {
        vfsStream::setup('zentra', null, ['config' => ['zentracrypt.key' => 'not a valid key']]);

        $zentrakey = new \ZENTRAKey(vfsStream::url('zentra/config'));

        $zentrakey->get();
        $this->hasPhpLogRecordThatContains(
            'Invalid security key file contents.',
            LogLevel::WARNING
        );
    }

    public function testGet()
    {
        $valid_key = 'abcdefghijklmnopqrstuvwxyz123456';
        vfsStream::setup('zentra', null, ['config' => ['zentracrypt.key' => $valid_key]]);

        $zentrakey = new \ZENTRAKey(vfsStream::url('zentra/config'));

        $key = $zentrakey->get();

        $this->assertEquals($valid_key, $key);
    }

    public function testGetLegacyKeyDefault()
    {
        vfsStream::setup('zentra', null, ['config' => []]);

        $zentrakey = new \ZENTRAKey(vfsStream::url('zentra/config'));

        $key = $zentrakey->getLegacyKey();

        $this->assertEquals("ZENTRA£i'snarss'ç", $key);
    }

    public function testGetLegacyKeyCustom()
    {
        vfsStream::setup('zentra', null, ['config' => ['zentra.key' => 'mylegacykey']]);

        $zentrakey = new \ZENTRAKey(vfsStream::url('zentra/config'));

        $key = $zentrakey->getLegacyKey();

        $this->assertEquals('mylegacykey', $key);
    }

    public function testGetLegacyKeyUnreadable()
    {
        $structure = vfsStream::setup('zentra', null, ['config' => ['zentra.key' => 'unreadable file']]);
        $structure->getChild('config/zentra.key')->chmod(0o222);

        $zentrakey = new \ZENTRAKey(vfsStream::url('zentra/config'));

        $zentrakey->getLegacyKey();
        $this->hasPhpLogRecordThatContains(
            'Unable to get security legacy key file contents.',
            LogLevel::WARNING
        );
    }

    public function testGenerateWithoutPreviousKey()
    {
        vfsStream::setup('zentra', null, ['config' => []]);

        $zentrakey = new \ZENTRAKey(vfsStream::url('zentra/config'));

        $success = $zentrakey->generate();
        $this->assertTrue($success);

        // key file exists and key can be retrieved
        $this->assertTrue(file_exists(vfsStream::url('zentra/config/zentracrypt.key')));
        $this->assertNotEmpty($zentrakey->get());
    }

    public function testGenerateWithExistingPreviousKey()
    {
        $structure = vfsStream::setup('zentra', null, ['config' => []]);
        vfsStream::copyFromFileSystem(ZENTRA_CONFIG_DIR, $structure->getChild('config'));
        $structure->getChild('config/zentracrypt.key')->chmod(0o666);

        $zentrakey = new \ZENTRAKey(vfsStream::url('zentra/config'));

        $success = $zentrakey->generate();
        $this->assertTrue($success);

        // key file exists and key can be retrieved
        $this->assertTrue(file_exists(vfsStream::url('zentra/config/zentracrypt.key')));
        $this->assertNotEmpty($zentrakey->get());

        // check that decrypted value of _local_ldap.rootdn_passwd is correct
        $ldap = getItemByTypeName('AuthLDAP', '_local_ldap');
        $this->assertEquals('insecure', $zentrakey->decrypt($ldap->fields['rootdn_passwd']));
    }

    public function testGenerateFailureWithUnwritableConfigDir()
    {
        // Unwritable dir
        $structure = vfsStream::setup('zentra', null, ['config' => []]);
        $structure->getChild('config')->chmod(0o555);


        $zentrakey = new \ZENTRAKey(vfsStream::url('zentra/config'));

        $this->assertFalse($zentrakey->generate());
        $this->hasPhpLogRecordThatContains(
            'Security key file path (vfs://zentra/config/zentracrypt.key) is not writable.',
            LogLevel::WARNING
        );
    }

    public function testGenerateFailureWithUnwritableConfigFile()
    {
        // Unwritable key file
        $structure = vfsStream::setup('zentra', null, ['config' => ['zentracrypt.key' => 'previouskey']]);
        $structure->getChild('config/zentracrypt.key')->chmod(0o444);

        $zentrakey = new \ZENTRAKey(vfsStream::url('zentra/config'));

        $this->assertFalse($zentrakey->generate());
        $this->hasPhpLogRecordThatContains(
            'Security key file path (vfs://zentra/config/zentracrypt.key) is not writable.',
            LogLevel::WARNING
        );
    }

    public function testGenerateFailureWithUnreadableKey()
    {
        $structure = vfsStream::setup('zentra', null, ['config' => ['zentracrypt.key' => 'unreadable file']]);
        $structure->getChild('config/zentracrypt.key')->chmod(0o222);

        $zentrakey = new \ZENTRAKey(vfsStream::url('zentra/config'));

        $this->assertFalse($zentrakey->generate());
        $this->hasPhpLogRecordThatContains(
            'Unable to get security key file contents.',
            LogLevel::WARNING
        );
    }

    public function testGenerateFailureWithInvalidPreviousKey()
    {
        vfsStream::setup('zentra', null, ['config' => ['zentracrypt.key' => 'not a valid key']]);

        $zentrakey = new \ZENTRAKey(vfsStream::url('zentra/config'));

        $this->assertFalse($zentrakey->generate());
        $this->hasPhpLogRecordThatContains(
            'Invalid security key file contents.',
            LogLevel::WARNING
        );
    }

    public function testEncryptDecryptUsingDefaultKey()
    {
        $structure = vfsStream::setup('zentra', null, ['config' => []]);
        vfsStream::copyFromFileSystem(ZENTRA_CONFIG_DIR, $structure->getChild('config'));

        $zentrakey = new \ZENTRAKey(vfsStream::url('zentra/config'));

        // Short string with no special chars
        $string = 'MyP4ssw0rD';
        $encrypted = $zentrakey->encrypt($string);
        $decrypted = $zentrakey->decrypt($encrypted);
        $this->assertEquals($string, $decrypted);

        // Empty string
        $string = '';
        $encrypted = $zentrakey->encrypt($string);
        $decrypted = $zentrakey->decrypt($encrypted);
        $this->assertEquals($string, $decrypted);

        // Complex string with special chars
        $string = 'This is a string I want to crypt, with some unusual chars like %, \', @, and so on!';
        $encrypted = $zentrakey->encrypt($string);
        $decrypted = $zentrakey->decrypt($encrypted);
        $this->assertEquals($string, $decrypted);
    }

    public static function encryptDecryptProvider()
    {
        $key = hex2bin('a72f621a029175008055f103fb977fe185fecdb248e42c18751afb391278d4b6');

        yield [
            'string'    => 'MyP4ssw0rD',
            'encrypted' => 'LO/9MItyVPEV1a/fn9kMehifov25XPOEqQl69GmnWFlcPG7zWk5v5CrSPRtVHd5Oy1Y=',
            'key'       => $key,
        ];

        yield [
            'string'    => 'This is a string I want to crypt, with some unusual chars like %, \', @, and so on!',
            'encrypted' => 'lBaMoLV3u0DOZS17qDBoO4uVY56WEmYQpUg+F+WfZ8zE3Nt/nQzBajs6VNY5F1CHHKKSaAR5wGdmYfY2MLX4b7KYOBuC/JYeOUnPXvhQTe8uuAdkDxjqmRqRtY2TaNhQBPBz6ul8i+YZRwW3oPe0wssZl2uV0KONNfI=',
            'key'       => $key,
        ];

        yield [
            'string'    => '',
            'encrypted' => 'tBH3MhNfobeT0tdmcYbSNqhll0OTcRSSRajXtSZ980RmzLLgJC3Owg==',
            'key'       => $key,
        ];
    }

    #[DataProvider('encryptDecryptProvider')]
    public function testEncryptUsingSpecificKey(?string $string, ?string $encrypted, ?string $key = null)
    {
        vfsStream::setup('zentra', null, ['config' => []]);

        $zentrakey = new \ZENTRAKey(vfsStream::url('zentra/config'));

        // NONCE produce different result each time
        $this->assertNotEquals($encrypted, $zentrakey->encrypt($string, $key));

        // As encryption produces different result each time, we cannot validate encrypted value.
        // So we validate that encryption alters string, and decryption reproduces the initial string.
        $encrypted = $zentrakey->encrypt($string, $key);
        $this->assertNotEquals($string, $encrypted);
        $decrypted = $zentrakey->decrypt($encrypted, $key);
        $this->assertEquals($string, $decrypted);
    }

    #[DataProvider('encryptDecryptProvider')]
    public function testDecryptUsingSpecificKey(?string $string, ?string $encrypted, ?string $key = null)
    {
        vfsStream::setup('zentra', null, ['config' => []]);

        $zentrakey = new \ZENTRAKey(vfsStream::url('zentra/config'));

        $decrypted = $zentrakey->decrypt($encrypted, $key);
        $this->assertEquals($string, $decrypted);
    }

    #[DataProvider('encryptDecryptProvider')]
    public function testDecryptEmptyValue(?string $string, ?string $encrypted, ?string $key = null)
    {
        $structure = vfsStream::setup('zentra', null, ['config' => []]);
        vfsStream::copyFromFileSystem(ZENTRA_CONFIG_DIR, $structure->getChild('config'));

        $zentrakey = new \ZENTRAKey(vfsStream::url('zentra/config'));

        $this->assertNull($zentrakey->decrypt(null));
        $this->assertEmpty($zentrakey->decrypt(''));
    }

    public function testDecryptInvalidString()
    {
        $structure = vfsStream::setup('zentra', null, ['config' => []]);
        vfsStream::copyFromFileSystem(ZENTRA_CONFIG_DIR, $structure->getChild('config'));

        $zentrakey = new \ZENTRAKey(vfsStream::url('zentra/config'));

        $this->assertEmpty($zentrakey->decrypt('not a valid value'));
        $this->hasPhpLogRecordThatContains(
            'Unable to extract nonce from string. It may not have been crypted with sodium functions.',
            LogLevel::WARNING
        );
    }

    public function testDecryptUsingBadKey()
    {
        $structure = vfsStream::setup('zentra', null, ['config' => []]);
        vfsStream::copyFromFileSystem(ZENTRA_CONFIG_DIR, $structure->getChild('config'));

        $zentrakey = new \ZENTRAKey(vfsStream::url('zentra/config'));

        $this->assertEmpty($zentrakey->decrypt('CUdPSEgzKroDOwM1F8lbC8WDcQUkGCxIZpdTEpp5W/PLSb70WmkaKP0Q7QY='));
        $this->hasPhpLogRecordThatContains(
            'Unable to decrypt string. It may have been crypted with another key.',
            LogLevel::WARNING
        );
    }

    public function testGetFields()
    {
        vfsStream::setup('zentra', null, ['config' => []]);

        $zentrakey = new \ZENTRAKey(vfsStream::url('zentra/config'));

        global $PLUGIN_HOOKS;
        $hooks_backup = $PLUGIN_HOOKS[Hooks::SECURED_FIELDS] ?? null;

        $PLUGIN_HOOKS[Hooks::SECURED_FIELDS] = [
            'myplugin' => [
                'zentra_plugin_myplugin_remote.key',
                'zentra_plugin_myplugin_remote.secret',
            ],
            'anotherplugin' => [
                'zentra_plugin_anotherplugin_link.pass',
            ],
        ];

        $fields = $zentrakey->getFields();

        unset($PLUGIN_HOOKS[Hooks::SECURED_FIELDS]);
        if ($hooks_backup !== null) {
            $PLUGIN_HOOKS[Hooks::SECURED_FIELDS] = $hooks_backup;
        }

        $this->assertEquals(
            [
                'zentra_apiclients.app_token',
                'zentra_authldaps.rootdn_passwd',
                'zentra_mailcollectors.passwd',
                'zentra_oauthclients.secret',
                'zentra_snmpcredentials.auth_passphrase',
                'zentra_snmpcredentials.priv_passphrase',
                'zentra_users.api_token',
                'zentra_users.cookie_token',
                'zentra_users.password_forget_token',
                'zentra_users.personal_token',
                'zentra_plugin_myplugin_remote.key',
                'zentra_plugin_myplugin_remote.secret',
                'zentra_plugin_anotherplugin_link.pass',
            ],
            $fields
        );
    }

    public function testGetConfigs()
    {
        vfsStream::setup('zentra', null, ['config' => []]);

        $zentrakey = new \ZENTRAKey(vfsStream::url('zentra/config'));

        global $PLUGIN_HOOKS;
        $hooks_backup = $PLUGIN_HOOKS[Hooks::SECURED_CONFIGS] ?? null;

        $PLUGIN_HOOKS[Hooks::SECURED_CONFIGS] = [
            'myplugin' => [
                'password',
            ],
            'anotherplugin' => [
                'secret',
            ],
        ];

        $configs = $zentrakey->getConfigs();

        unset($PLUGIN_HOOKS[Hooks::SECURED_CONFIGS]);
        if ($hooks_backup !== null) {
            $PLUGIN_HOOKS[Hooks::SECURED_CONFIGS] = $hooks_backup;
        }

        $this->assertEquals(
            [
                'core' => [
                    'zentranetwork_registration_key',
                    'proxy_passwd',
                    'smtp_passwd',
                    'smtp_oauth_client_secret',
                    'smtp_oauth_refresh_token',
                ],
                'plugin:myplugin' => [
                    'password',
                ],
                'plugin:anotherplugin' => [
                    'secret',
                ],
            ],
            $configs
        );
    }

    public function testIsConfigSecured()
    {
        vfsStream::setup('zentra', null, ['config' => []]);

        $zentrakey = new \ZENTRAKey(vfsStream::url('zentra/config'));
        global $PLUGIN_HOOKS;
        $hooks_backup = $PLUGIN_HOOKS[Hooks::SECURED_CONFIGS] ?? null;

        $PLUGIN_HOOKS[Hooks::SECURED_CONFIGS] = [
            'myplugin' => [
                'password',
            ],
        ];

        $is_url_base_secured = $zentrakey->isConfigSecured('core', 'url_base');
        $is_smtp_passwd_secured = $zentrakey->isConfigSecured('core', 'smtp_passwd');
        $is_myplugin_password_secured = $zentrakey->isConfigSecured('plugin:myplugin', 'password');
        $is_myplugin_href_secured = $zentrakey->isConfigSecured('plugin:myplugin', 'href');
        $is_someplugin_conf_secured = $zentrakey->isConfigSecured('plugin:someplugin', 'conf');

        unset($PLUGIN_HOOKS[Hooks::SECURED_CONFIGS]);
        if ($hooks_backup !== null) {
            $PLUGIN_HOOKS[Hooks::SECURED_CONFIGS] = $hooks_backup;
        }

        $this->assertFalse($is_url_base_secured);
        $this->assertTrue($is_smtp_passwd_secured);
        $this->assertTrue($is_myplugin_password_secured);
        $this->assertFalse($is_myplugin_href_secured);
        $this->assertFalse($is_someplugin_conf_secured);
    }

    public function testGetKeyFileReadErrorsWithMissingFile(): void
    {
        // arrange : create directory structure without key file
        vfsStream::setup('zentra', null, ['config' => []]);
        $zentrakey = new \ZENTRAKey(vfsStream::url('zentra/config'));

        // act
        $errors = $zentrakey->getKeyFileReadErrors();

        // assert
        $this->assertStringContainsString('The security key file does not exist.', implode(" ", $errors));
    }

    public function testGetKeyFileReadErrorsWithUnreadableFile(): void
    {
        // arrange : create unreadable key file
        $structure = vfsStream::setup('zentra', null, ['config' => ['zentracrypt.key' => 'unreadable file']]);
        $structure->getChild('config/zentracrypt.key')->chmod(0o222);

        $zentrakey = new \ZENTRAKey(vfsStream::url('zentra/config'));

        // act
        $errors = $zentrakey->getKeyFileReadErrors();

        // assert
        $this->assertStringContainsString('Unable to get security key file contents.', implode(" ", $errors));
    }

    public function testGetKeyFileReadErrorsWithInvalidKey(): void
    {
        // arrange : key file exists but has invalid contents/length
        vfsStream::setup('zentra', null, ['config' => ['zentracrypt.key' => 'not a valid key']]);
        $zentrakey = new \ZENTRAKey(vfsStream::url('zentra/config'));

        // act
        $errors = $zentrakey->getKeyFileReadErrors();

        // assert
        $this->assertStringContainsString('Invalid security key file contents.', implode(" ", $errors));
    }

    public function testGetKeyFileReadErrorsWithValidKey(): void
    {
        // arrange : key file exists and is valid => no errors
        $valid_key = 'abcdefghijklmnopqrstuvwxyz123456';
        vfsStream::setup('zentra', null, ['config' => ['zentracrypt.key' => $valid_key]]);

        $zentrakey = new \ZENTRAKey(vfsStream::url('zentra/config'));

        // act
        $errors = $zentrakey->getKeyFileReadErrors();

        // assert
        $this->assertEmpty($errors);
    }

    public function testHasKeyFileReadErrorsWithMissingFile(): void
    {
        // arrange : create directory structure without key file
        vfsStream::setup('zentra', null, ['config' => []]);
        $zentrakey = new \ZENTRAKey(vfsStream::url('zentra/config'));

        // act + assert
        $this->assertTrue($zentrakey->hasReadErrors());
    }

    public function testHasKeyFileReadErrorsWithUnreadableFile(): void
    {
        // arrange : create unreadable key file
        $structure = vfsStream::setup('zentra', null, ['config' => ['zentracrypt.key' => 'unreadable file']]);
        $structure->getChild('config/zentracrypt.key')->chmod(0o222);

        $zentrakey = new \ZENTRAKey(vfsStream::url('zentra/config'));

        // act + assert
        $this->assertTrue($zentrakey->hasReadErrors());

    }

    public function testHasKeyFileReadErrorsWithInvalidKey(): void
    {
        // arrange : key file exists but has invalid contents/length
        vfsStream::setup('zentra', null, ['config' => ['zentracrypt.key' => 'not a valid key']]);
        $zentrakey = new \ZENTRAKey(vfsStream::url('zentra/config'));

        // act + assert
        $this->assertTrue($zentrakey->hasReadErrors());
    }

    public function testHasKeyFileReadErrorsWithValidKey(): void
    {
        // arrange : key file exists and is valid => no errors
        $valid_key = 'abcdefghijklmnopqrstuvwxyz123456';
        vfsStream::setup('zentra', null, ['config' => ['zentracrypt.key' => $valid_key]]);

        $zentrakey = new \ZENTRAKey(vfsStream::url('zentra/config'));

        // act + assert
        $this->assertFalse($zentrakey->hasReadErrors());
    }
}

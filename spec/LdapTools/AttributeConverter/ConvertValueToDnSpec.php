<?php
/**
 * This file is part of the LdapTools package.
 *
 * (c) Chad Sikorra <Chad.Sikorra@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spec\LdapTools\AttributeConverter;

use LdapTools\Connection\LdapConnectionInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ConvertValueToDnSpec extends ObjectBehavior
{
    protected $entry = [
        'count' => 1,
        0 => [
            "distinguishedname" => [
                "count" => 1,
                0 => "CN=Foo,DC=bar,DC=foo",
            ],
            0 => "distinguishedName",
            'count' => 2,
            'dn' => "CN=Foo,DC=bar,DC=foo",
        ],
    ];

    function it_is_initializable()
    {
        $this->shouldHaveType('LdapTools\AttributeConverter\ConvertValueToDn');
    }

    function it_should_implement_AttributeConverterInterface()
    {
        $this->shouldImplement('\LdapTools\AttributeConverter\AttributeConverterInterface');
    }

    function it_should_convert_a_dn_to_a_normal_name()
    {
        $this->setOptions(['foo' =>[ 'filter' => ['objectClass' => 'bar'],  'attribute' => 'foo']]);
        $this->setAttribute('foo');
        $this->fromLdap('cn=Foo,dc=bar,dc=foo')->shouldBeEqualTo('Foo');
    }

    function it_should_convert_a_name_back_to_a_dn(LdapConnectionInterface $connection)
    {
        $connection->getLdapType()->willReturn('ad');
        $connection->search('(&(objectClass=\62\61\72)(cn=\46\6f\6f))', ['distinguishedName'], null, 'subtree', null)->willReturn($this->entry);
        $this->setOptions(['foo' => [
            'attribute' => 'cn',
            'filter' => [
                'objectClass' => 'bar'
            ],
        ]]);
        $this->setAttribute('foo');
        $this->setLdapConnection($connection);
        $this->toLdap('Foo')->shouldBeEqualTo($this->entry[0]['distinguishedname'][0]);
    }

    function it_should_convert_a_GUID_back_to_a_dn(LdapConnectionInterface $connection)
    {
        $guid = 'a1131cd3-902b-44c6-b49a-1f6a567cda25';
        $guidHex = '\d3\1c\13\a1\2b\90\c6\44\b4\9a\1f\6a\56\7c\da\25';
        $guidSimpleHex = '\61\31\31\33\31\63\64\33\2d\39\30\32\62\2d\34\34\63\36\2d\62\34\39\61\2d\31\66\36\61\35\36\37\63\64\61\32\35';

        $connection->getLdapType()->willReturn('ad');
        $connection->search('(&(objectClass=\62\61\72)(|(objectGuid='.$guidHex.')(cn='.$guidSimpleHex.')))', ['distinguishedName'], null, 'subtree', null)->willReturn($this->entry);
        $this->setOptions(['foo' => [
            'attribute' => 'cn',
            'filter' => [
                'objectClass' => 'bar'
            ],
        ]]);
        $this->setAttribute('foo');
        $this->setLdapConnection($connection);
        $this->toLdap($guid)->shouldBeEqualTo($this->entry[0]['distinguishedname'][0]);
    }

    function it_should_convert_a_SID_back_to_a_dn(LdapConnectionInterface $connection)
    {
        $sid = 'S-1-5-21-1004336348-1177238915-682003330-512';
        $sidHex = '\01\05\00\00\00\00\00\05\15\00\00\00\dc\f4\dc\3b\83\3d\2b\46\82\8b\a6\28\00\02\00\00';
        $sidSimpleHex = '\53\2d\31\2d\35\2d\32\31\2d\31\30\30\34\33\33\36\33\34\38\2d\31\31\37\37\32\33\38\39\31\35\2d\36\38\32\30\30\33\33\33\30\2d\35\31\32';

        $connection->getLdapType()->willReturn('ad');
        $connection->search('(&(objectClass=\62\61\72)(|(objectSid='.$sidHex.')(cn='.$sidSimpleHex.')))', ['distinguishedName'], null, 'subtree', null)->willReturn($this->entry);
        $this->setOptions(['foo' => [
            'attribute' => 'cn',
            'filter' => [
                'objectClass' => 'bar'
            ],
        ]]);
        $this->setAttribute('foo');
        $this->setLdapConnection($connection);
        $this->toLdap($sid)->shouldBeEqualTo($this->entry[0]['distinguishedname'][0]);
    }

    function it_should_convert_a_dn_back_to_a_dn(LdapConnectionInterface $connection)
    {
        $dn = $this->entry[0]['distinguishedname'][0];
        $dnHex = '\43\4e\3d\46\6f\6f\2c\44\43\3d\62\61\72\2c\44\43\3d\66\6f\6f';

        $connection->getLdapType()->willReturn('ad');
        $connection->search('(&(objectClass=\62\61\72)(|(distinguishedName='.$dnHex.')(cn='.$dnHex.')))', ['distinguishedName'], null, 'subtree', null)->willReturn($this->entry);
        $this->setOptions(['foo' => [
            'attribute' => 'cn',
            'filter' => [
                'objectClass' => 'bar'
            ],
        ]]);
        $this->setAttribute('foo');
        $this->setLdapConnection($connection);
        $this->toLdap($this->entry[0]['distinguishedname'][0])->shouldBeEqualTo($this->entry[0]['distinguishedname'][0]);
    }

    function it_should_convert_a_dn_into_its_common_name()
    {
        $this->setOptions(['foo' =>[ 'filter' => ['objectClass' => 'bar'],  'attribute' => 'foo']]);
        $this->setAttribute('foo');
        $this->fromLdap('cn=Foo\,\=bar,dc=foo,dc=bar')->shouldBeEqualTo('Foo,=bar');
    }

    function it_should_throw_an_error_if_no_options_exist_for_the_current_attribute(LdapConnectionInterface $ldap)
    {
        $this->setLdapConnection($ldap);
        $this->shouldThrow('\RuntimeException')->duringToLdap('foo');
    }
}
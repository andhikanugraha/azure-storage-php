<?php

/**
 * LICENSE: The MIT License (the "License")
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * https://github.com/azure/azure-storage-php/LICENSE
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * PHP version 5
 *
 * @category  Microsoft
 * @package   MicrosoftAzure\Storage\Tests\Unit\Common\Internal
 * @author    Azure Storage PHP SDK <dmsh@microsoft.com>
 * @copyright 2016 Microsoft Corporation
 * @license   https://github.com/azure/azure-storage-php/LICENSE
 * @link      https://github.com/azure/azure-storage-php
 */

namespace MicrosoftAzure\Storage\Tests\unit\Common\Internal;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use MicrosoftAzure\Storage\Common\Internal\ServiceRestProxy;
use MicrosoftAzure\Storage\Common\Internal\Resources;
use MicrosoftAzure\Storage\Tests\Mock\Common\Internal\Filters\SimpleFilterMock;
use MicrosoftAzure\Storage\Blob\Models\AccessCondition;
use MicrosoftAzure\Storage\Common\Internal\Serialization\XmlSerializer;

/**
 * Unit tests for class ServiceRestProxy
 *
 * @category  Microsoft
 * @package   MicrosoftAzure\Storage\Tests\Unit\Common\Internal
 * @author    Azure Storage PHP SDK <dmsh@microsoft.com>
 * @copyright 2016 Microsoft Corporation
 * @license   https://github.com/azure/azure-storage-php/LICENSE
 * @version   Release: 0.12.1
 * @link      https://github.com/azure/azure-storage-php
 */
class ServiceRestProxyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers MicrosoftAzure\Storage\Common\Internal\ServiceRestProxy::generateMetadataHeaders
     */
    public function testConstruct()
    {
        // Setup
        $uri     = 'http://www.microsoft.com';
        $accountName = 'myaccount';
        $dataSerializer = new XmlSerializer();

        // Test
        $proxy = new ServiceRestProxy($uri, $accountName, $dataSerializer);

        // Assert
        $this->assertNotNull($proxy);
        $this->assertEquals($accountName, $proxy->getAccountName());

        // Auto append an '/' at the end of uri.
        $this->assertEquals($uri . '/', $proxy->getUri());

        return $proxy;
    }

    /**
     * @covers  MicrosoftAzure\Storage\Common\Internal\ServiceRestProxy::withFilter
     * @depends testConstruct
     */
    public function testWithFilter($restRestProxy)
    {
        // Setup
        $filter = new SimpleFilterMock('name', 'value');

        // Test
        $actual = $restRestProxy->withFilter($filter);

        // Assert
        $this->assertCount(1, $actual->getFilters());
        $this->assertCount(0, $restRestProxy->getFilters());
    }

    /**
     * @covers  MicrosoftAzure\Storage\Common\Internal\ServiceRestProxy::getFilters
     * @depends testConstruct
     */
    public function testGetFilters($restRestProxy)
    {
        // Setup
        $filter = new SimpleFilterMock('name', 'value');
        $withFilter = $restRestProxy->withFilter($filter);

        // Test
        $actual1 = $withFilter->getFilters();
        $actual2 = $restRestProxy->getFilters();

        // Assert
        $this->assertCount(1, $actual1);
        $this->assertCount(0, $actual2);
    }

    /**
     * @covers  MicrosoftAzure\Storage\Common\Internal\ServiceRestProxy::groupQueryValues
     * @depends testConstruct
     */
    public function testGroupQueryValues()
    {
        // Setup
        $values = array('A', 'B', 'C');
        $expected = 'A,B,C';

        // Test
        $actual = ServiceRestProxy::groupQueryValues($values);

        // Assert
        $this->assertEquals($expected, $actual);
    }

    /**
     * @covers  MicrosoftAzure\Storage\Common\Internal\ServiceRestProxy::groupQueryValues
     * @depends testConstruct
     */
    public function testGroupQueryValuesWithUnorderedValues()
    {
        // Setup
        $values = array('B', 'C', 'A');
        $expected = 'A,B,C';

        // Test
        $actual = ServiceRestProxy::groupQueryValues($values);

        // Assert
        $this->assertEquals($expected, $actual);
    }

    /**
     * @covers  MicrosoftAzure\Storage\Common\Internal\ServiceRestProxy::groupQueryValues
     */
    public function testGroupQueryValuesWithNulls()
    {
        // Setup
        $values = array(null, '', null);

        // Test
        $actual = ServiceRestProxy::groupQueryValues($values);

        // Assert
        $this->assertTrue(empty($actual));
    }

    /**
     * @covers  MicrosoftAzure\Storage\Common\Internal\ServiceRestProxy::groupQueryValues
     * @depends testConstruct
     */
    public function testGroupQueryValuesWithMix()
    {
        // Setup
        $values = array(null, 'B', 'C', '');
        $expected = 'B,C';

        // Test
        $actual = ServiceRestProxy::groupQueryValues($values);

        // Assert
        $this->assertEquals($expected, $actual);
    }

    /**
    * @covers MicrosoftAzure\Storage\Common\Internal\ServiceRestProxy::addPostParameter
    * @depends testConstruct
    */
    public function testPostParameter($restRestProxy)
    {
        // Setup
        $postParameters = array();
        $key = 'a';
        $expected = 'b';

        // Test
        $processedPostParameters = $restRestProxy->addPostParameter($postParameters, $key, $expected);
        $actual = $processedPostParameters[$key];

        // Assert
        $this->assertEquals(
            $expected,
            $actual
        );
    }

    /**
     * @covers MicrosoftAzure\Storage\Common\Internal\ServiceRestProxy::generateMetadataHeaders
     * @depends testConstruct
     */
    public function testGenerateMetadataHeader($proxy)
    {
        // Setup
        $metadata = array('key1' => 'value1', 'MyName' => 'WindowsAzure', 'MyCompany' => 'Microsoft_');
        $expected = array();
        foreach ($metadata as $key => $value) {
            $expected[Resources::X_MS_META_HEADER_PREFIX . $key] = $value;
        }

        // Test
        $actual = $proxy->generateMetadataHeaders($metadata);

        // Assert
        $this->assertEquals($expected, $actual);
    }

    /**
     * @covers MicrosoftAzure\Storage\Common\Internal\ServiceRestProxy::generateMetadataHeaders
     * @depends testConstruct
     */
    public function testGenerateMetadataHeaderInvalidNameFail($proxy)
    {
        // Setup
        $metadata = array('key1' => "value1\n", 'MyName' => "\rAzurr", 'MyCompany' => "Micr\r\nosoft_");
        $this->setExpectedException(get_class(new \InvalidArgumentException(Resources::INVALID_META_MSG)));

        // Test
        $proxy->generateMetadataHeaders($metadata);
    }

    /**
     * @expectedException \GuzzleHttp\Exception\RequestException
     * @expectedExceptionMessage foo
     */
    public function testSetGuzzleOptions()
    {
        $uri = 'http://www.microsoft.com';
        $accountName = 'myaccount';
        $dataSerializer = new XmlSerializer();
        $mockRequestHandler = new MockHandler(
            array(new RequestException('foo', new Request('GET', $uri)))
        );

        $guzzleOptions = array('http' => array('handler' => HandlerStack::create($mockRequestHandler)));
        $proxy = new ServiceRestProxy($uri, $accountName, $dataSerializer, $guzzleOptions);
        $reflection = new \ReflectionClass($proxy);
        $method = $reflection->getMethod('sendAsync');
        $method->setAccessible(true);

        $method->invokeArgs($proxy, array(
            'GET',
            [],
            [],
            [],
            '/',
        ))->wait();
    }
}

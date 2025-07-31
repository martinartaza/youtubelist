<?php
declare(strict_types=1);

namespace Artaza\YoutubeList\Test\Unit\Helper;

use Artaza\YoutubeList\Helper\Data;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Registry;
use Magento\Catalog\Model\Product;
use Psr\Log\LoggerInterface;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for Data Helper
 */
class DataTest extends TestCase
{
    /**
     * @var Data
     */
    private $helper;

    /**
     * @var Context|\PHPUnit\Framework\MockObject\MockObject
     */
    private $contextMock;

    /**
     * @var Curl|\PHPUnit\Framework\MockObject\MockObject
     */
    private $curlMock;

    /**
     * @var Registry|\PHPUnit\Framework\MockObject\MockObject
     */
    private $registryMock;

    /**
     * @var ScopeConfigInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $scopeConfigMock;

    /**
     * @var Product|\PHPUnit\Framework\MockObject\MockObject
     */
    private $productMock;

    /**
     * @var LoggerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $loggerMock;

    /**
     * Set up test environment
     */
    protected function setUp(): void
    {
        $this->contextMock = $this->createMock(Context::class);
        $this->curlMock = $this->createMock(Curl::class);
        $this->registryMock = $this->createMock(Registry::class);
        $this->scopeConfigMock = $this->createMock(ScopeConfigInterface::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        
        // Configure logger mock to handle all logging methods
        $this->loggerMock->method('debug')->willReturnSelf();
        $this->loggerMock->method('warning')->willReturnSelf();
        $this->loggerMock->method('error')->willReturnSelf();
        
        // Create a more specific mock for Product
        $this->productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->addMethods(['getYoutubelist'])
            ->getMock();

        $this->contextMock->method('getScopeConfig')
            ->willReturn($this->scopeConfigMock);

        $this->helper = new Data(
            $this->contextMock,
            $this->curlMock,
            $this->registryMock,
            $this->loggerMock
        );
    }

    /**
     * Test getYouKey method
     */
    public function testGetYouKey()
    {
        $expectedKey = 'test_api_key';
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(Data::PATH_YOUTUBE_KEY)
            ->willReturn($expectedKey);

        $result = $this->helper->getYouKey();
        $this->assertEquals($expectedKey, $result);
    }

    /**
     * Test getUrlProduct method with valid product
     */
    public function testGetUrlProductWithValidProduct()
    {
        $expectedUrl = 'https://www.youtube.com/watch?v=test&list=testlist';
        $this->productMock->method('getYoutubelist')
            ->willReturn($expectedUrl);
        
        $this->registryMock->expects($this->once())
            ->method('registry')
            ->with('product')
            ->willReturn($this->productMock);

        $result = $this->helper->getUrlProduct();
        $this->assertEquals($expectedUrl, $result);
    }

    /**
     * Test getUrlProduct method with no product
     */
    public function testGetUrlProductWithNoProduct()
    {
        $this->registryMock->expects($this->once())
            ->method('registry')
            ->with('product')
            ->willReturn(null);

        $result = $this->helper->getUrlProduct();
        $this->assertEquals('', $result);
    }

    /**
     * Test isList method with list URL
     */
    public function testIsListWithListUrl()
    {
        $listUrl = 'https://www.youtube.com/watch?v=test&list=testlist';
        $this->productMock->method('getYoutubelist')
            ->willReturn($listUrl);
        
        $this->registryMock->method('registry')
            ->with('product')
            ->willReturn($this->productMock);

        $result = $this->helper->isList();
        $this->assertTrue($result);
    }

    /**
     * Test isList method with non-list URL
     */
    public function testIsListWithNonListUrl()
    {
        $nonListUrl = 'https://www.youtube.com/watch?v=test';
        $this->productMock->method('getYoutubelist')
            ->willReturn($nonListUrl);
        
        $this->registryMock->method('registry')
            ->with('product')
            ->willReturn($this->productMock);

        $result = $this->helper->isList();
        $this->assertFalse($result);
    }

    /**
     * Test isOnly method with only video URL
     */
    public function testIsOnlyWithOnlyVideoUrl()
    {
        $onlyUrl = 'https://www.youtube.com/watch?v=test';
        $this->productMock->method('getYoutubelist')
            ->willReturn($onlyUrl);
        
        $this->registryMock->method('registry')
            ->with('product')
            ->willReturn($this->productMock);

        $result = $this->helper->isOnly();
        $this->assertTrue($result);
    }

    /**
     * Test isOnly method with non-only video URL
     */
    public function testIsOnlyWithNonOnlyVideoUrl()
    {
        $nonOnlyUrl = 'https://www.youtube.com/embed/test';
        $this->productMock->method('getYoutubelist')
            ->willReturn($nonOnlyUrl);
        
        $this->registryMock->method('registry')
            ->with('product')
            ->willReturn($this->productMock);

        $result = $this->helper->isOnly();
        $this->assertFalse($result);
    }

    /**
     * Test isEmbed method with embed URL
     */
    public function testIsEmbedWithEmbedUrl()
    {
        $embedUrl = 'https://www.youtube.com/embed/test';
        $this->productMock->method('getYoutubelist')
            ->willReturn($embedUrl);
        
        $this->registryMock->method('registry')
            ->with('product')
            ->willReturn($this->productMock);

        $result = $this->helper->isEmbed();
        $this->assertTrue($result);
    }

    /**
     * Test isEmbed method with non-embed URL
     */
    public function testIsEmbedWithNonEmbedUrl()
    {
        $nonEmbedUrl = 'https://www.youtube.com/watch?v=test';
        $this->productMock->method('getYoutubelist')
            ->willReturn($nonEmbedUrl);
        
        $this->registryMock->method('registry')
            ->with('product')
            ->willReturn($this->productMock);

        $result = $this->helper->isEmbed();
        $this->assertFalse($result);
    }

    /**
     * Test getTypeUrl method with list URL
     */
    public function testGetTypeUrlWithListUrl()
    {
        $listUrl = 'https://www.youtube.com/watch?v=test&list=testlist';
        $this->productMock->method('getYoutubelist')
            ->willReturn($listUrl);
        
        $this->registryMock->method('registry')
            ->with('product')
            ->willReturn($this->productMock);

        $result = $this->helper->getTypeUrl();
        $this->assertEquals('list', $result);
    }

    /**
     * Test getTypeUrl method with only video URL
     */
    public function testGetTypeUrlWithOnlyVideoUrl()
    {
        $onlyUrl = 'https://www.youtube.com/watch?v=test';
        $this->productMock->method('getYoutubelist')
            ->willReturn($onlyUrl);
        
        $this->registryMock->method('registry')
            ->with('product')
            ->willReturn($this->productMock);

        $result = $this->helper->getTypeUrl();
        $this->assertEquals('only', $result);
    }

    /**
     * Test getTypeUrl method with embed URL
     */
    public function testGetTypeUrlWithEmbedUrl()
    {
        $embedUrl = 'https://www.youtube.com/embed/test';
        $this->productMock->method('getYoutubelist')
            ->willReturn($embedUrl);
        
        $this->registryMock->method('registry')
            ->with('product')
            ->willReturn($this->productMock);

        $result = $this->helper->getTypeUrl();
        $this->assertEquals('embed', $result);
    }

    /**
     * Test getTypeUrl method with invalid URL
     */
    public function testGetTypeUrlWithInvalidUrl()
    {
        $invalidUrl = 'https://example.com/invalid';
        $this->productMock->method('getYoutubelist')
            ->willReturn($invalidUrl);
        
        $this->registryMock->method('registry')
            ->with('product')
            ->willReturn($this->productMock);

        $result = $this->helper->getTypeUrl();
        $this->assertEquals('invalid', $result);
    }

    /**
     * Test getArrayVideos method with list type
     */
    public function testGetArrayVideosWithListType()
    {
        $listUrl = 'https://www.youtube.com/watch?v=test&list=testlist';
        $this->productMock->method('getYoutubelist')
            ->willReturn($listUrl);
        
        $this->registryMock->method('registry')
            ->with('product')
            ->willReturn($this->productMock);

        $this->scopeConfigMock->method('getValue')
            ->with(Data::PATH_YOUTUBE_KEY)
            ->willReturn('test_key');

        $this->curlMock->expects($this->once())
            ->method('setTimeout')
            ->with(30)
            ->willReturnSelf();

        $this->curlMock->expects($this->once())
            ->method('get')
            ->willReturnSelf();

        $this->curlMock->expects($this->once())
            ->method('getStatus')
            ->willReturn(200);

        $this->curlMock->expects($this->once())
            ->method('getBody')
            ->willReturn('{"items": []}');

        $result = $this->helper->getArrayVideos();
        $this->assertIsArray($result);
    }

    /**
     * Test getArrayVideos method with only video type
     */
    public function testGetArrayVideosWithOnlyVideoType()
    {
        $onlyUrl = 'https://www.youtube.com/watch?v=test';
        $this->productMock->method('getYoutubelist')
            ->willReturn($onlyUrl);
        
        $this->registryMock->method('registry')
            ->with('product')
            ->willReturn($this->productMock);

        $result = $this->helper->getArrayVideos();
        $this->assertIsArray($result);
        $this->assertCount(1, $result);
    }

    /**
     * Test getArrayVideos method with embed video type
     */
    public function testGetArrayVideosWithEmbedVideoType()
    {
        $embedUrl = 'https://www.youtube.com/embed/test';
        $this->productMock->method('getYoutubelist')
            ->willReturn($embedUrl);
        
        $this->registryMock->method('registry')
            ->with('product')
            ->willReturn($this->productMock);

        $result = $this->helper->getArrayVideos();
        $this->assertIsArray($result);
        $this->assertCount(1, $result);
    }

    /**
     * Test getArrayVideos method with invalid type
     */
    public function testGetArrayVideosWithInvalidType()
    {
        $invalidUrl = 'https://example.com/invalid';
        $this->productMock->method('getYoutubelist')
            ->willReturn($invalidUrl);
        
        $this->registryMock->method('registry')
            ->with('product')
            ->willReturn($this->productMock);

        $result = $this->helper->getArrayVideos();
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /**
     * Test getGraphQLVideos method with valid product and list videos
     */
    public function testGetGraphQLVideosWithListVideos()
    {
        $listUrl = 'https://www.youtube.com/watch?v=test&list=testlist';
        $this->productMock->method('getYoutubelist')
            ->willReturn($listUrl);
        
        $this->registryMock->expects($this->once())
            ->method('register')
            ->with('product', $this->productMock);

        // Mock getArrayVideos to return expected data
        $expectedArrayVideos = [
            [
                'url' => 'https://www.youtube.com/embed/test123',
                'image' => (object)['medium' => (object)['url' => 'test.jpg']]
            ]
        ];

        $this->helper = $this->getMockBuilder(Data::class)
            ->setConstructorArgs([
                $this->contextMock,
                $this->curlMock,
                $this->registryMock,
                $this->loggerMock
            ])
            ->onlyMethods(['getArrayVideos'])
            ->getMock();

        $this->helper->expects($this->once())
            ->method('getArrayVideos')
            ->willReturn($expectedArrayVideos);

        $result = $this->helper->getGraphQLVideos($this->productMock);
        
        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertArrayHasKey('url', $result[0]);
        $this->assertArrayHasKey('image', $result[0]);
        $this->assertEquals('https://www.youtube.com/embed/test123', $result[0]['url']);
        $this->assertEquals('test.jpg', $result[0]['image']);
    }

    /**
     * Test getGraphQLVideos method with single video
     */
    public function testGetGraphQLVideosWithSingleVideo()
    {
        $onlyUrl = 'https://www.youtube.com/watch?v=test123';
        $this->productMock->method('getYoutubelist')
            ->willReturn($onlyUrl);
        
        $this->registryMock->expects($this->once())
            ->method('register')
            ->with('product', $this->productMock);

        // Mock getArrayVideos to return expected data
        $expectedArrayVideos = [
            [
                'url' => 'https://www.youtube.com/embed/test123',
                'image' => ''
            ]
        ];

        $this->helper = $this->getMockBuilder(Data::class)
            ->setConstructorArgs([
                $this->contextMock,
                $this->curlMock,
                $this->registryMock,
                $this->loggerMock
            ])
            ->onlyMethods(['getArrayVideos'])
            ->getMock();

        $this->helper->expects($this->once())
            ->method('getArrayVideos')
            ->willReturn($expectedArrayVideos);

        $result = $this->helper->getGraphQLVideos($this->productMock);
        
        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertArrayHasKey('url', $result[0]);
        $this->assertArrayHasKey('image', $result[0]);
        $this->assertEquals('https://www.youtube.com/embed/test123', $result[0]['url']);
        $this->assertEquals('', $result[0]['image']);
    }

    /**
     * Test getGraphQLVideos method with embed video
     */
    public function testGetGraphQLVideosWithEmbedVideo()
    {
        $embedUrl = 'https://www.youtube.com/embed/test123';
        $this->productMock->method('getYoutubelist')
            ->willReturn($embedUrl);
        
        $this->registryMock->expects($this->once())
            ->method('register')
            ->with('product', $this->productMock);

        // Mock getArrayVideos to return expected data
        $expectedArrayVideos = [
            [
                'url' => 'https://www.youtube.com/embed/test123',
                'image' => ''
            ]
        ];

        $this->helper = $this->getMockBuilder(Data::class)
            ->setConstructorArgs([
                $this->contextMock,
                $this->curlMock,
                $this->registryMock,
                $this->loggerMock
            ])
            ->onlyMethods(['getArrayVideos'])
            ->getMock();

        $this->helper->expects($this->once())
            ->method('getArrayVideos')
            ->willReturn($expectedArrayVideos);

        $result = $this->helper->getGraphQLVideos($this->productMock);
        
        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertArrayHasKey('url', $result[0]);
        $this->assertArrayHasKey('image', $result[0]);
        $this->assertEquals('https://www.youtube.com/embed/test123', $result[0]['url']);
        $this->assertEquals('', $result[0]['image']);
    }

    /**
     * Test getGraphQLVideos method with invalid URL
     */
    public function testGetGraphQLVideosWithInvalidUrl()
    {
        $invalidUrl = 'https://example.com/invalid';
        $this->productMock->method('getYoutubelist')
            ->willReturn($invalidUrl);
        
        $this->registryMock->expects($this->once())
            ->method('register')
            ->with('product', $this->productMock);

        $result = $this->helper->getGraphQLVideos($this->productMock);
        
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /**
     * Test getGraphQLVideos method with empty URL
     */
    public function testGetGraphQLVideosWithEmptyUrl()
    {
        $this->productMock->method('getYoutubelist')
            ->willReturn('');
        
        $this->registryMock->expects($this->once())
            ->method('register')
            ->with('product', $this->productMock);

        $result = $this->helper->getGraphQLVideos($this->productMock);
        
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /**
     * Test getGraphQLVideos method with null URL
     */
    public function testGetGraphQLVideosWithNullUrl()
    {
        $this->productMock->method('getYoutubelist')
            ->willReturn(null);
        
        $this->registryMock->expects($this->once())
            ->method('register')
            ->with('product', $this->productMock);

        $result = $this->helper->getGraphQLVideos($this->productMock);
        
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /**
     * Test getGraphQLVideos method with complex image object
     */
    public function testGetGraphQLVideosWithComplexImageObject()
    {
        $listUrl = 'https://www.youtube.com/watch?v=test&list=testlist';
        $this->productMock->method('getYoutubelist')
            ->willReturn($listUrl);
        
        $this->registryMock->expects($this->once())
            ->method('register')
            ->with('product', $this->productMock);

        // Mock getArrayVideos to return expected data
        $expectedArrayVideos = [
            [
                'url' => 'https://www.youtube.com/embed/complex123',
                'image' => (object)['medium' => (object)['url' => 'complex_image.jpg']]
            ]
        ];

        $this->helper = $this->getMockBuilder(Data::class)
            ->setConstructorArgs([
                $this->contextMock,
                $this->curlMock,
                $this->registryMock,
                $this->loggerMock
            ])
            ->onlyMethods(['getArrayVideos'])
            ->getMock();

        $this->helper->expects($this->once())
            ->method('getArrayVideos')
            ->willReturn($expectedArrayVideos);

        $result = $this->helper->getGraphQLVideos($this->productMock);
        
        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertArrayHasKey('url', $result[0]);
        $this->assertArrayHasKey('image', $result[0]);
        $this->assertEquals('https://www.youtube.com/embed/complex123', $result[0]['url']);
        $this->assertEquals('complex_image.jpg', $result[0]['image']);
    }

    /**
     * Test getGraphQLVideos method with string image
     */
    public function testGetGraphQLVideosWithStringImage()
    {
        $listUrl = 'https://www.youtube.com/watch?v=test&list=testlist';
        $this->productMock->method('getYoutubelist')
            ->willReturn($listUrl);
        
        $this->registryMock->expects($this->once())
            ->method('register')
            ->with('product', $this->productMock);

        // Mock getArrayVideos to return expected data
        $expectedArrayVideos = [
            [
                'url' => 'https://www.youtube.com/embed/string123',
                'image' => 'string_image.jpg'
            ]
        ];

        $this->helper = $this->getMockBuilder(Data::class)
            ->setConstructorArgs([
                $this->contextMock,
                $this->curlMock,
                $this->registryMock,
                $this->loggerMock
            ])
            ->onlyMethods(['getArrayVideos'])
            ->getMock();

        $this->helper->expects($this->once())
            ->method('getArrayVideos')
            ->willReturn($expectedArrayVideos);

        $result = $this->helper->getGraphQLVideos($this->productMock);
        
        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertArrayHasKey('url', $result[0]);
        $this->assertArrayHasKey('image', $result[0]);
        $this->assertEquals('https://www.youtube.com/embed/string123', $result[0]['url']);
        $this->assertEquals('string_image.jpg', $result[0]['image']);
    }
}

<?php
declare(strict_types=1);

namespace Artaza\YoutubeList\Test\Unit\Model\Resolver;

use Artaza\YoutubeList\Helper\Data;
use Artaza\YoutubeList\Model\Resolver\YoutubeVideos;
use Magento\Catalog\Model\Product;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for YoutubeVideos GraphQL resolver
 */
class YoutubeVideosTest extends TestCase
{
    /**
     * @var YoutubeVideos
     */
    private $resolver;

    /**
     * @var Data|\PHPUnit\Framework\MockObject\MockObject
     */
    private $helperMock;

    /**
     * @var Product|\PHPUnit\Framework\MockObject\MockObject
     */
    private $productMock;

    /**
     * @var Field|\PHPUnit\Framework\MockObject\MockObject
     */
    private $fieldMock;

    /**
     * @var ResolveInfo|\PHPUnit\Framework\MockObject\MockObject
     */
    private $resolveInfoMock;

    /**
     * Set up test environment
     */
    protected function setUp(): void
    {
        $this->helperMock = $this->createMock(Data::class);
        $this->productMock = $this->createMock(Product::class);
        $this->fieldMock = $this->createMock(Field::class);
        $this->resolveInfoMock = $this->createMock(ResolveInfo::class);

        $this->resolver = new YoutubeVideos($this->helperMock);
    }

    /**
     * Test resolve method with product that has youtubelist attribute
     */
    public function testResolveWithYoutubelistAttribute()
    {
        $expectedVideos = [
            [
                'url' => 'https://www.youtube.com/embed/test123',
                'image' => 'test_image.jpg'
            ]
        ];

        $this->productMock->expects($this->once())
            ->method('hasData')
            ->with('youtubelist')
            ->willReturn(true);

        $this->helperMock->expects($this->once())
            ->method('getGraphQLVideos')
            ->with($this->productMock)
            ->willReturn($expectedVideos);

        $result = $this->resolver->resolve(
            $this->fieldMock,
            null,
            $this->resolveInfoMock,
            ['model' => $this->productMock]
        );

        $this->assertEquals($expectedVideos, $result);
    }

    /**
     * Test resolve method with product that needs to be loaded
     */
    public function testResolveWithProductNeedingLoad()
    {
        $expectedVideos = [
            [
                'url' => 'https://www.youtube.com/embed/test123',
                'image' => 'test_image.jpg'
            ]
        ];

        $this->productMock->expects($this->once())
            ->method('hasData')
            ->with('youtubelist')
            ->willReturn(false);

        $this->productMock->expects($this->once())
            ->method('getId')
            ->willReturn(123);

        $this->productMock->expects($this->once())
            ->method('load')
            ->with(123);

        $this->helperMock->expects($this->once())
            ->method('getGraphQLVideos')
            ->with($this->productMock)
            ->willReturn($expectedVideos);

        $result = $this->resolver->resolve(
            $this->fieldMock,
            null,
            $this->resolveInfoMock,
            ['model' => $this->productMock]
        );

        $this->assertEquals($expectedVideos, $result);
    }

    /**
     * Test resolve method with empty videos array
     */
    public function testResolveWithEmptyVideos()
    {
        $this->productMock->expects($this->once())
            ->method('hasData')
            ->with('youtubelist')
            ->willReturn(true);

        $this->helperMock->expects($this->once())
            ->method('getGraphQLVideos')
            ->with($this->productMock)
            ->willReturn([]);

        $result = $this->resolver->resolve(
            $this->fieldMock,
            null,
            $this->resolveInfoMock,
            ['model' => $this->productMock]
        );

        $this->assertEquals([], $result);
    }

    /**
     * Test resolve method with multiple videos
     */
    public function testResolveWithMultipleVideos()
    {
        $expectedVideos = [
            [
                'url' => 'https://www.youtube.com/embed/video1',
                'image' => 'image1.jpg'
            ],
            [
                'url' => 'https://www.youtube.com/embed/video2',
                'image' => 'image2.jpg'
            ],
            [
                'url' => 'https://www.youtube.com/embed/video3',
                'image' => 'image3.jpg'
            ]
        ];

        $this->productMock->expects($this->once())
            ->method('hasData')
            ->with('youtubelist')
            ->willReturn(true);

        $this->helperMock->expects($this->once())
            ->method('getGraphQLVideos')
            ->with($this->productMock)
            ->willReturn($expectedVideos);

        $result = $this->resolver->resolve(
            $this->fieldMock,
            null,
            $this->resolveInfoMock,
            ['model' => $this->productMock]
        );

        $this->assertEquals($expectedVideos, $result);
        $this->assertCount(3, $result);
    }

    /**
     * Test resolve method with null value
     */
    public function testResolveWithNullValue()
    {
        $this->helperMock->expects($this->never())
            ->method('getGraphQLVideos');

        $result = $this->resolver->resolve(
            $this->fieldMock,
            null,
            $this->resolveInfoMock,
            null
        );

        $this->assertEquals([], $result);
    }

    /**
     * Test resolve method with value without model
     */
    public function testResolveWithValueWithoutModel()
    {
        $this->helperMock->expects($this->never())
            ->method('getGraphQLVideos');

        $result = $this->resolver->resolve(
            $this->fieldMock,
            null,
            $this->resolveInfoMock,
            ['other_key' => 'value']
        );

        $this->assertEquals([], $result);
    }
}

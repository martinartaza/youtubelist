<?php
declare(strict_types=1);

namespace Artaza\YoutubeList\Test\Unit\ViewModel;

use Artaza\YoutubeList\Helper\Data;
use Artaza\YoutubeList\ViewModel\ProductPage;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for ProductPage ViewModel
 */
class ProductPageTest extends TestCase
{
    /**
     * @var Data|\PHPUnit\Framework\MockObject\MockObject
     */
    private $helperMock;

    /**
     * @var ProductPage
     */
    private $viewModel;

    /**
     * Set up test environment
     */
    protected function setUp(): void
    {
        $this->helperMock = $this->createMock(Data::class);
        $this->viewModel = new ProductPage($this->helperMock);
    }

    /**
     * Test getVideos method
     */
    public function testGetVideos(): void
    {
        $expectedVideos = [
            [
                'url' => 'https://www.youtube.com/watch?v=test1',
                'image' => (object)[
                    'medium' => (object)[
                        'url' => 'https://img.youtube.com/vi/test1/mqdefault.jpg',
                        'width' => 320,
                        'height' => 180
                    ]
                ]
            ]
        ];

        $this->helperMock->expects($this->once())
            ->method('getArrayVideos')
            ->willReturn($expectedVideos);

        $result = $this->viewModel->getVideos();

        $this->assertEquals($expectedVideos, $result);
        $this->assertIsArray($result);
    }

    /**
     * Test getVideos returns empty array when helper returns empty
     */
    public function testGetVideosReturnsEmptyArray(): void
    {
        $this->helperMock->expects($this->once())
            ->method('getArrayVideos')
            ->willReturn([]);

        $result = $this->viewModel->getVideos();

        $this->assertEmpty($result);
        $this->assertIsArray($result);
    }

    /**
     * Test getHelper method
     */
    public function testGetHelper(): void
    {
        $result = $this->viewModel->getHelper();

        $this->assertSame($this->helperMock, $result);
        $this->assertInstanceOf(Data::class, $result);
    }
}

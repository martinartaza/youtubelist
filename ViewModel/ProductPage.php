<?php
declare(strict_types=1);

namespace Artaza\YoutubeList\ViewModel;

use Artaza\YoutubeList\Helper\Data;
use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
 * ViewModel for YouTube videos on product page
 */
class ProductPage implements ArgumentInterface
{
    /**
     * @var Data
     */
    private Data $helper;

    /**
     * @param Data $helper
     */
    public function __construct(Data $helper)
    {
        $this->helper = $helper;
    }

    /**
     * Get videos array
     *
     * @return array
     */
    public function getVideos(): array
    {
        return $this->helper->getArrayVideos();
    }

    /**
     * Get helper instance
     *
     * @return Data
     */
    public function getHelper(): Data
    {
        return $this->helper;
    }
}

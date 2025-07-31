<?php
declare(strict_types=1);

namespace Artaza\YoutubeList\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Artaza\YoutubeList\Helper\Data;

/**
 * YouTube Videos resolver for GraphQL
 */
class YoutubeVideos implements ResolverInterface
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @param Data $helper
     */
    public function __construct(Data $helper)
    {
        $this->helper = $helper;
    }

    /**
     * Resolver for YouTube videos
     *
     * @param Field $field
     * @param mixed $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        // Check if value and model exist
        if (!$value || !isset($value['model'])) {
            return [];
        }

        $product = $value['model'];
        
        // Ensure the product has all attributes loaded
        if (!$product->hasData('youtubelist')) {
            $product->load($product->getId());
        }
        
        // Use the helper method that handles all the logic
        return $this->helper->getGraphQLVideos($product);
    }
}

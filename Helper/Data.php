<?php
declare(strict_types=1);

namespace Artaza\YoutubeList\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Registry;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

/**
 * YouTube List Helper
 */
class Data extends AbstractHelper
{
    /**
     * YouTube API key configuration path
     */
    public const PATH_YOUTUBE_KEY = 'catalog/product_video/youtube_api_key';

    /**
     * YouTube API URL part 1
     */
    public const URL_API_YOUTUBE_1 = 'https://youtube.googleapis.com/youtube/v3/playlistItems?playlistId=';

    /**
     * YouTube API URL part 2
     */
    public const URL_API_YOUTUBE_2 = '&part=id&part=snippet&part=contentDetails&key=';

    /**
     * YouTube watch URL
     */
    public const URL_WATCH_ONLY_VIDEO = 'https://www.youtube.com/watch?v=';

    /**
     * YouTube embed URL
     */
    public const URL_WATCH_ONLY_EMBED_VIDEO = 'https://www.youtube.com/embed/';

    /**
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * @var Curl
     */
    protected $_curl;

    /**
     * @var LoggerInterface|null
     */
    protected $_logger;

    /**
     * @param Context $context
     * @param Curl $curl
     * @param Registry $registry
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        Context $context,
        Curl $curl,
        Registry $registry,
        LoggerInterface $logger = null
    ) {
        $this->_coreRegistry = $registry;
        $this->_curl = $curl;
        $this->_logger = $logger;
        parent::__construct($context);
    }

    /**
     * Get YouTube API key
     *
     * @return string|null
     */
    public function getYouKey()
    {
        try {
            $apiKey = $this->scopeConfig->getValue(self::PATH_YOUTUBE_KEY);
            if (empty($apiKey)) {
                if ($this->_logger) {
                    $this->_logger->warning('YouTube API key is not configured');
                }
                return null;
            }
            return $apiKey;
        } catch (\Exception $e) {
            if ($this->_logger) {
                $this->_logger->error('Error getting YouTube API key: ' . $e->getMessage());
            }
            return null;
        }
    }

    /**
     * Get product URL
     *
     * @return string
     */
    public function getUrlProduct()
    {
        try {
            $product = $this->_coreRegistry->registry('product');
            if (!$product) {
                if ($this->_logger) {
                    $this->_logger->debug('No product found in registry');
                }
                return '';
            }

            $youtubeUrl = $product->getYoutubelist();
            if (empty($youtubeUrl)) {
                if ($this->_logger) {
                    $this->_logger->debug('No YouTube URL configured for product ID: ' . $product->getId());
                }
                return '';
            }

            return $youtubeUrl;
        } catch (\Exception $e) {
            if ($this->_logger) {
                $this->_logger->error('Error getting product URL: ' . $e->getMessage());
            }
            return '';
        }
    }

    /**
     * Check if URL is a list
     *
     * @return bool
     */
    public function isList()
    {
        try {
            $url = $this->getUrlProduct();
            return (!empty($url) && strpos($url, '&list=') !== false);
        } catch (\Exception $e) {
            if ($this->_logger) {
                $this->_logger->error('Error checking if URL is list: ' . $e->getMessage());
            }
            return false;
        }
    }

    /**
     * Check if URL is only video
     *
     * @return bool
     */
    public function isOnly()
    {
        try {
            $url = $this->getUrlProduct();
            return strpos($url, self::URL_WATCH_ONLY_VIDEO) === 0;
        } catch (\Exception $e) {
            if ($this->_logger) {
                $this->_logger->error('Error checking if URL is only video: ' . $e->getMessage());
            }
            return false;
        }
    }

    /**
     * Check if URL is embed
     *
     * @return bool
     */
    public function isEmbed()
    {
        try {
            $url = $this->getUrlProduct();
            return strpos($url, self::URL_WATCH_ONLY_EMBED_VIDEO) === 0;
        } catch (\Exception $e) {
            if ($this->_logger) {
                $this->_logger->error('Error checking if URL is embed: ' . $e->getMessage());
            }
            return false;
        }
    }

    /**
     * Get URL type
     *
     * @return string
     */
    public function getTypeUrl()
    {
        try {
            if ($this->isList()) {
                return 'list';
            }
            if ($this->isOnly()) {
                return 'only';
            }
            if ($this->isEmbed()) {
                return 'embed';
            }
            return 'invalid';
        } catch (\Exception $e) {
            if ($this->_logger) {
                $this->_logger->error('Error getting URL type: ' . $e->getMessage());
            }
            return 'invalid';
        }
    }

    /**
     * Get YouTube API URL for product video
     *
     * @return string|null
     */
    public function getUrlApiYouTubeProductVideo()
    {
        try {
            $url = $this->getUrlProduct();
            if (empty($url)) {
                if ($this->_logger) {
                    $this->_logger->debug('No YouTube URL available for API call');
                }
                return null;
            }

            $listPosition = strpos($url, '&list=');
            if ($listPosition === false) {
                if ($this->_logger) {
                    $this->_logger->debug('No playlist ID found in URL: ' . $url);
                }
                return null;
            }

            $list = substr($url, $listPosition + 6);
            if (empty($list)) {
                if ($this->_logger) {
                    $this->_logger->debug('Empty playlist ID extracted from URL');
                }
                return null;
            }

            $apiKey = $this->getYouKey();
            if (empty($apiKey)) {
                if ($this->_logger) {
                    $this->_logger->warning('Cannot build API URL: YouTube API key is missing');
                }
                return null;
            }

            $apiUrl = self::URL_API_YOUTUBE_1 . $list . self::URL_API_YOUTUBE_2 . $apiKey;
            if ($this->_logger) {
                $this->_logger->debug('Built YouTube API URL: ' . $apiUrl);
            }

            return $apiUrl;
        } catch (\Exception $e) {
            if ($this->_logger) {
                $this->_logger->error('Error building YouTube API URL: ' . $e->getMessage());
            }
            return null;
        }
    }

    /**
     * Get YouTube API response
     *
     * @return string
     */
    public function getResponseYoutube()
    {
        try {
            $apiUrl = $this->getUrlApiYouTubeProductVideo();
            if (empty($apiUrl)) {
                if ($this->_logger) {
                    $this->_logger->warning('Cannot make API request: URL is empty');
                }
                return '';
            }

            $this->_curl->setTimeout(30);
            $this->_curl->get($apiUrl);

            $httpCode = $this->_curl->getStatus();
            if ($httpCode !== 200) {
                if ($this->_logger) {
                    $this->_logger->error('YouTube API request failed with HTTP code: ' . $httpCode);
                }
                return '';
            }

            $response = $this->_curl->getBody();
            if (empty($response)) {
                if ($this->_logger) {
                    $this->_logger->warning('Empty response from YouTube API');
                }
                return '';
            }

            if ($this->_logger) {
                $this->_logger->debug('Successfully received response from YouTube API');
            }
            return $response;
        } catch (\Exception $e) {
            if ($this->_logger) {
                $this->_logger->error('Error making YouTube API request: ' . $e->getMessage());
            }
            return '';
        }
    }

    /**
     * Get array of videos
     *
     * @return array
     */
    public function getArrayVideos()
    {
        try {
            $urlType = $this->getTypeUrl();
            if ($this->_logger) {
                $this->_logger->debug('Processing YouTube URL type: ' . $urlType);
            }

            switch ($urlType) {
                case 'list':
                    return $this->getListVideo();
                case 'embed':
                case 'only':
                    return $this->getOnlyVideo();
                default:
                    if ($this->_logger) {
                        $this->_logger->debug('Invalid URL type, returning empty array');
                    }
                    return [];
            }
        } catch (\Exception $e) {
            if ($this->_logger) {
                $this->_logger->error('Error getting array of videos: ' . $e->getMessage());
            }
            return [];
        }
    }

    /**
     * Get list of videos
     *
     * @return array
     */
    public function getListVideo()
    {
        try {
            $arrayVideos = [];
            $response = $this->getResponseYoutube();

            if (empty($response)) {
                if ($this->_logger) {
                    $this->_logger->warning('Empty response received for list video request');
                }
                return $arrayVideos;
            }

            $responseObject = json_decode($response);
            if (json_last_error() !== JSON_ERROR_NONE) {
                if ($this->_logger) {
                    $this->_logger->error('Failed to decode JSON response: ' . json_last_error_msg());
                }
                return $arrayVideos;
            }

            if (!$responseObject) {
                if ($this->_logger) {
                    $this->_logger->warning('Decoded response is null');
                }
                return $arrayVideos;
            }

            if (!isset($responseObject->items) || !is_array($responseObject->items)) {
                if ($this->_logger) {
                    $this->_logger->warning('No items found in YouTube API response');
                }
                return $arrayVideos;
            }

            foreach ($responseObject->items as $index => $item) {
                try {
                    if (!isset($item->snippet->thumbnails) || !isset($item->contentDetails->videoId)) {
                        if ($this->_logger) {
                            $this->_logger->warning('Missing required fields in item ' . $index);
                        }
                        continue;
                    }

                    $itemVideo = [
                        'image' => $item->snippet->thumbnails,
                        'url' => self::URL_WATCH_ONLY_EMBED_VIDEO . $item->contentDetails->videoId
                    ];
                    $arrayVideos[] = $itemVideo;
                } catch (\Exception $e) {
                    if ($this->_logger) {
                        $this->_logger->error('Error processing video item ' . $index . ': ' . $e->getMessage());
                    }
                    continue;
                }
            }

            if ($this->_logger) {
                $this->_logger->debug('Successfully processed ' . count($arrayVideos) . ' videos from playlist');
            }
            return $arrayVideos;
        } catch (\Exception $e) {
            if ($this->_logger) {
                $this->_logger->error('Error getting list video: ' . $e->getMessage());
            }
            return [];
        }
    }

    /**
     * Get embed video URL
     *
     * @return string|null
     */
    public function getEmbedVideo()
    {
        try {
            if ($this->isEmbed()) {
                $url = $this->getUrlProduct();
                if ($this->_logger) {
                    $this->_logger->debug('URL is already embed format: ' . $url);
                }
                return $url;
            }

            if ($this->isOnly()) {
                $url = $this->getUrlProduct();
                $videoIdPosition = strpos($url, '=');

                if ($videoIdPosition === false) {
                    if ($this->_logger) {
                        $this->_logger->error('Cannot extract video ID from URL: ' . $url);
                    }
                    return null;
                }

                $videoId = substr($url, $videoIdPosition + 1);
                if (empty($videoId)) {
                    if ($this->_logger) {
                        $this->_logger->error('Empty video ID extracted from URL: ' . $url);
                    }
                    return null;
                }

                $embedUrl = self::URL_WATCH_ONLY_EMBED_VIDEO . $videoId;
                if ($this->_logger) {
                    $this->_logger->debug('Converted to embed URL: ' . $embedUrl);
                }
                return $embedUrl;
            }

            if ($this->_logger) {
                $this->_logger->debug('URL is not in embed or only format');
            }
            return null;
        } catch (\Exception $e) {
            if ($this->_logger) {
                $this->_logger->error('Error getting embed video URL: ' . $e->getMessage());
            }
            return null;
        }
    }

    /**
     * Get single video
     *
     * @return array
     */
    public function getOnlyVideo()
    {
        try {
            $embedUrl = $this->getEmbedVideo();
            if (empty($embedUrl)) {
                if ($this->_logger) {
                    $this->_logger->warning('No embed URL available for single video');
                }
                return [];
            }

            $arrayVideos = [];
            $itemVideos = [
                'image' => '',
                'url' => $embedUrl
            ];
            $arrayVideos[] = $itemVideos;

            if ($this->_logger) {
                $this->_logger->debug('Successfully created single video array');
            }
            return $arrayVideos;
        } catch (\Exception $e) {
            if ($this->_logger) {
                $this->_logger->error('Error getting single video: ' . $e->getMessage());
            }
            return [];
        }
    }

    /**
     * Get list of videos from YouTube URLs
     *
     * @param string $youtubeListUrl
     * @return string[]
     */
    public function listVideos(string $youtubeListUrl): array
    {
        $videos = [];
        $urls = explode(',', $youtubeListUrl);

        foreach ($urls as $url) {
            $url = trim($url);
            if (!$url) {
                continue;
            }
            $videoId = $this->extractYoutubeId($url);
            if ($videoId) {
                $videos[] = [
                    'url' => $url,
                    'image' => "https://img.youtube.com/vi/{$videoId}/mqdefault.jpg"
                ];
            }
        }
        return $videos;
    }

    /**
     * Extract YouTube video ID from URL
     *
     * @param string $url
     * @return string|null
     */
    private function extractYoutubeId($url)
    {
        if (preg_match('~(?:youtu\.be/|youtube\.com/(?:embed/|v/|watch\?v=|watch\?.+&v=))([^?&/]+)~', $url, $matches)) {
            return $matches[1];
        }
        return null;
    }

    /**
     * Get videos formatted for GraphQL response
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    public function getGraphQLVideos($product): array
    {
        // Set the product in registry for the helper
        $this->_coreRegistry->register('product', $product);
        
        // Get videos using the same method as ViewModel
        $videos = $this->getArrayVideos();
        
        // Format the response for GraphQL
        $formattedVideos = [];
        foreach ($videos as $video) {
            $formattedVideo = [
                'url' => $video['url']
            ];
            
            // Handle image - it can be an object or a string
            if (isset($video['image'])) {
                if (is_object($video['image']) && isset($video['image']->medium)) {
                    $formattedVideo['image'] = $video['image']->medium->url ?? '';
                } elseif (is_string($video['image'])) {
                    $formattedVideo['image'] = $video['image'];
                } else {
                    $formattedVideo['image'] = '';
                }
            } else {
                $formattedVideo['image'] = '';
            }
            
            $formattedVideos[] = $formattedVideo;
        }
        
        return $formattedVideos;
    }
}

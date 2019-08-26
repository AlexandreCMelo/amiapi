<?php
declare(strict_types=1);

namespace Ams\Domain\Client\Adapter;

use Http\Client\HttpClient;
use Http\Message\RequestFactory;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\SimpleCache\InvalidArgumentException;
use stdClass;

/**
 * Class Xkcd
 *
 * @package Ams\Domain\Client\Adapter
 */
class Xkcd extends BaseClient implements ClientInterface
{
    const PARAM_CLIENT_KEY = 'comics';
    const PARAM_URL = 'https://xkcd.com/%d/info.0.json';
    const CACHE_FIRST_POST_NUMBER_KEY = 'year-fullset-found';
    const CACHE_FOUND_YEAR_KEY_CACHE = 'year-found';
    const SEARCH_OFFSET = 1000;
    const SEARCH_DIVISION_FACTOR = 2;
    const SEARCH_MULTIPLICATION_FACTOR = 1.2;
    const MIN_YEAR = 2006;

    /**
     * @var RequestFactory
     */
    protected $requestFactory;

    /**
     * @var HttpClient
     */
    protected $httpClient;

    /**
     * @param int $year
     * @param int $limit
     * @return array
     * @throws InvalidArgumentException
     */
    public function request(int $year, int $limit)
    {
        if ($year < self::MIN_YEAR) {
            return false;
        }


        $cachedPosts = $this->getCachedPosts($year) ?? [];
        $foundYearDataSet = $this->getCache()->get($this->foundAllPostsFromYearCacheKey($year)) ?? false;

        if ($foundYearDataSet) {
            $posts = $this->slicePosts($cachedPosts, $limit);
        } else {
            $posts = $limit <= count($cachedPosts) ?
                $this->slicePosts($cachedPosts, $limit) :
                $this->requestAsyncPosts(
                    $this->findPostInYear(self::SEARCH_OFFSET, $year),
                    $year,
                    $limit,
                    $cachedPosts
                );
        }

        $data = $this->buildResponse(
            self::PARAM_CLIENT_KEY,
            $year,
            $limit,
            $posts
        );

        return $data;
    }

    /**
     * @param int $year
     * @return mixed|void|null
     * @throws InvalidArgumentException
     */
    public function getCachedPosts(int $year)
    {
        $cacheKey = $this->assembleCacheKey(self::PARAM_CLIENT_KEY, $year);
        $cachedPosts = $this->getCache()->get($cacheKey);
        return $cachedPosts;
    }

    /**
     * @param $postToSearchFrom
     * @param int              $year
     * @param int              $limit
     * @param array            $cachedPosts
     * @return array
     * @throws InvalidArgumentException
     */
    protected function requestAsyncPosts($postToSearchFrom, int $year, int $limit, array $cachedPosts)
    {

        if (empty($postToSearchFrom)) {
            return false;
        }

        $posts = [];
        $onlyKeepSameYearCallback = function ($request, Response $response) use (&$posts, $year) {
            $post = json_decode($response->getBody()->getContents());
            if ((int)$post->year == $year) {
                $posts[] = $this->assembleResponseBody(
                    $post->num,
                    $post->year,
                    $post->title,
                    $post->img,
                    empty($post->transcript) ? $post->alt : $post->transcript
                );
            }
            return false;
        };

        if (!empty($cachedPosts) && $limit > count($cachedPosts)) {
            $lastCachedKey = array_key_last($cachedPosts);
            $currentPost = $cachedPosts[$lastCachedKey]['number'] + 1;
            $postToFinish = $cachedPosts[$lastCachedKey]['number'] + ($limit - count($cachedPosts));
        } else {
            $currentPost = $postToSearchFrom->num;
            $postToFinish = $postToSearchFrom->num + $limit - 1;
        }

        $requestFactory = $this->getRequestFactory();
        $requestPool = $this->getHttpMultiClient($requestFactory);
        $limitNotExceeded = true;
        $turnDirection = false;

        while ($limitNotExceeded) {
            $request = $requestFactory->createRequest(self::HTTP_METHOD, $this->buildUrlFromParameter($currentPost));
            $requestPool->sendAsyncRequest($request, ['callback' => $onlyKeepSameYearCallback]);
            if ($currentPost == $postToFinish && !$turnDirection) {
                $requestPool->flush();
                $totalPosts = !empty($cachedPosts) ? count($cachedPosts) + count($posts) : count($posts);
                if ($totalPosts < $limit) {
                    $postsToSearch = $limit - $totalPosts;
                    $currentPost = $postToSearchFrom->num - $postsToSearch - 1;
                    $postToFinish = $currentPost + $postsToSearch;
                    $turnDirection = true;
                } else {
                    $limitNotExceeded = false;
                }
            } elseif ($currentPost == $postToFinish && $turnDirection) {
                $requestPool->flush();
                $totalPosts = count($posts);
                $limitNotExceeded = false;
            }
            $currentPost++;
        }

        if ($limit > $totalPosts) {
            $totalPostCacheKey = $this->foundAllPostsFromYearCacheKey($year);
            $this->getCache()->set($totalPostCacheKey, count($posts), self::CACHE_TTL_FOUR_HOURS);
        }

        $posts = !empty($cachedPosts) ? array_merge($cachedPosts, $posts) : $posts;
        $posts = $this->orderPostsByNumber($posts);

        $this->getCache()->set($this->assembleCacheKey(self::PARAM_CLIENT_KEY, $year), $posts);

        return $posts;
    }

    /**
     * @param $postNumber
     * @param $year
     * @param $limit
     * @return ResponseInterface|stdClass
     * @throws InvalidArgumentException
     */
    protected function findPostInYear($postNumber, $year)
    {
        $cache = $this->getCache();
        $post = $this->requestParsedJsonFromApiByPostId($postNumber);
        $findYearCacheKey = $this->assembleYearSearchCacheKey($year);
        $postToSearchFrom = $cache->get($findYearCacheKey);

        if ($postToSearchFrom == null) {
            $yearToFind = empty($post) ?
                $postNumber / self::SEARCH_DIVISION_FACTOR :
                $yearToFind = $post->year;
            while ($year != $yearToFind) {
                $postNumber = (int)($yearToFind > $year ?
                    ($postNumber / self::SEARCH_DIVISION_FACTOR) :
                    ($postNumber * self::SEARCH_MULTIPLICATION_FACTOR));

                return $this->findPostInYear($postNumber, $year);
            }
            $cache->set($findYearCacheKey, $post);
            $postToSearchFrom = $post;
        }

        return $postToSearchFrom;
    }

    /**
     * @param int  $postId
     * @param bool $asJson
     * @return ResponseInterface| stdClass
     */
    protected function requestParsedJsonFromApiByPostId(int $postId, $asJson = true)
    {
        $requestFactory = $this->getRequestFactory();
        $request = $requestFactory->createRequest(
            self::HTTP_METHOD,
            $this->buildUrlFromParameter($postId)
        );

        $response = $this->getHttpMultiClient($requestFactory)->sendRequest($request);

        return $asJson ?
            json_decode($response->getBody()->getContents()) :
            $response;
    }

    /**
     * @param int $postNumber
     * @return string
     */
    protected function buildUrlFromParameter(int $postNumber)
    {
        return sprintf(self::PARAM_URL, $postNumber);
    }

    /**
     * @param int $year
     * @return string
     */
    protected function assembleYearSearchCacheKey(int $year): string
    {
        return implode([self::CACHE_FOUND_YEAR_KEY_CACHE, $year], '-');
    }


    /**
     * @param $posts
     * @return mixed
     */
    protected function orderPostsByNumber($posts)
    {
        usort(
            $posts,
            function ($a, $b) {
                return $a['number'] <=> $b['number'];
            }
        );

        return $posts;
    }

    /**
     * @param int $year
     * @return string
     */
    protected function foundAllPostsFromYearCacheKey(int $year): string
    {
        return implode([self::CACHE_FIRST_POST_NUMBER_KEY, $year], '-');
    }

    /**
     * @param $cachedPosts
     * @param $limit
     * @return array
     */
    protected function slicePosts($cachedPosts, $limit): array
    {
        return array_slice($cachedPosts, 0, $limit);
    }
}

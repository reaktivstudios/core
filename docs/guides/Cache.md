# Caching Best Practices

Cache is something that requires careful consideration. When done improperly it can result in bottlenecks, stale data, or unintended collisons. So we want to make sure that when we introduce caching on top of exisitng cache layers we are doing so deliberately and with the right approach.

## Managed Hosts

One factor to consider is that enterprise level hosts have several layers of caching built in. These include, but aren't limited to:

1. **Page Caching**: For guest visitors to the site, and on some hosts even for those logged in, the entire page is cached at the HTML level in short intervals. This is then intelligently invalidated on page or post updates. But there _are times_ when the cache won't be purged correctly for pages, if for instance data is being edited outside of the context of the editor.
2. **Object Level Caching**: Many hosts will provide a layer of object cache, using tools like Redis and Memcached that sits just beneath the page caching tools. When done properly, this will include automatic hooks into standard WordPress queries and requests. However this can also be accessed at stored to using standard WordPress functions, such as `wp_cache_get`
3. **Reverse Proxy Caching**: When working with a reverse proxy, there will be typically be one additional level of caching happening at that level as well. Depending on how aggressive it is, this can cause unintended side effects. Using a Cache Control header and other NGINX features can protect against this.

## Cache Invalidation

One of the most important considerations when adding caching to your project is cache invalidation. It is so tricky that it once led to this quote by programmer Phil Karlton:

> There are only two hard things in Computer Science: cache invalidation and naming things.

Cache invalidation refers to clearing a previously cached value whenever that value is no longer valid. For instance, you may want to cache a third party request to prevent overburdening an API url, or causing slowdowns. But if that value is not refreshed on a regular basis it will soon become stale. As another example, you may cache a complex meta query to save it from being called multiple times, but that needs to be invalidated whenever a new value is saved to that particular meta key.

Cache invalidation typically happens using the `wp_cache_delete` key, hooking into a common action or process where it makes the most sense. However, you can also add optional time parameters to caching function to clear them automatically.

If you automatically are clearing values based on a certain duration, be sure to add a fallback to clear cache manually via the admin or a CLI command, or use the long cache pattern (noted below).

## Third Party Requests

One of the most common reasons to cache data is because it is coming from a third party API or query. These are cases when we cannot rely on built in cache services of the platfroms we build on, and have to manage cached requests ourselves.

## Transients vs WP Cache

WordPress supports two different ways to store and retrieve data, `wp_cache_` and `wp_transient_`. Both follow similar programmatic patterns but are intended to serve different purposes.

In theory, **`wp_cache_`** functions store and retrieve data from an object cache store, while **`wp_transient`** functions store and retrieve data from the `wp_options` table.

However, in practice many hosts simply store both in object cache, making transients slightly less predictable.

*As a general rule, `wp_cache_` functions should be favored, unless there is a specific reason to use transients.*

## Long Cache
Long cache is a technique we will occasionally use when the data being stored can be unstable, for instance via an API that's prone to downtime or unreliable results.

This works by setting two cache keys whenever values are retrieved. One is set to a relatively small duration (i.e. 30 minutes) and one with a much larger expiration (i.e. 14 days).

When the value is called, the key with the shorter expiration is first attempted, but as a fallback the longer expiration is called next. That way if 30 minutes, or whatever the initial expiration is, has already passed and we are unable to get a reliable result we don't need to continuously make requests, we can simply pull the "long cache" key that's been stored.

Each time a successful request is made, both keys are set at the same time to make them both as fresh as possible.


## Links to Host specific info

- [WordPress VIP Caching Details](https://docs.wpvip.com/caching/)
- [Pantheon Caching](https://docs.pantheon.io/guides/wordpress-configurations/wordpress-cache-plugin) and [Object Cache Pro](https://docs.pantheon.io/object-cache/wordpress)
- WP Engine [Page Level Cache](https://wpengine.com/support/cache/) and [Object Caching](https://wpengine.com/support/wp-engines-object-caching/)

## The RKV Cache Class

[`/inc/classes/Cache.php`]('../inc/classes/Cache.php)

The RKV Cache class makes managing cache simpler by storing the common parameters for cache in one place and providing a set of helpers for retrieving and setting values.

Please refer to the Cache class for docs on how to use it.